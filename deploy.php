<?php
namespace Deployer;

require 'recipe/common.php';

// Configuration
// You have to set live server credentials. They are not set because of security reasons.
// Store the credentials in credentials.json.
$json_credentials = file_get_contents('./credentials.json', true);
$credentials = json_decode($json_credentials, true);

set('live_server', $credentials["live_server"]);
set('live_server_path', $credentials["live_server_path"]);
set('live_server_ssh_user', $credentials["live_server_ssh_user"]);

set('live_db_name', $credentials["live_db_name"]);
set('live_db_user', $credentials["live_db_user"]);
set('live_db_pw', $credentials["live_db_pw"]);

set('local_server_path', $credentials["local_server_path"]);

set('local_db_name', $credentials["local_db_name"]);
set('local_db_user', $credentials["local_db_user"]);
set('local_db_pw', $credentials["local_db_pw"]);

set('repository', '{{live_server_ssh_user}}@{{live_server}}:{{live_server_path}}/git/yakup.karahan.de.git');
set('git_tty', true); // [Optional] Allocate tty for git on first deployment
set('ssh_multiplexing', false);
set('shared_files', []);
set('shared_dirs', []);
set('writable_dirs', []);
set('default_stage', 'local');
// Hosts

host(get('live_server'))
    ->user(get('live_server_ssh_user'))
    ->stage('production')
    ->set('deploy_path', '{{live_server_path}}/contao');


localhost()
    ->stage('local')
    ->set('deploy_path', '{{local_server_path}}/contao');


// Tasks
desc('Migrate live database and Configuration to local project');
task('deploy:preview', function () {
    run('rsync --delete --exclude=".git" --exclude=".dep" -avczer ./files/* {{deploy_path}}/files');
    run('rsync --delete --exclude=".git" --exclude=".dep" -avczer ./templates/* {{deploy_path}}/templates');
    run('rsync -avczer ./bower_components/pushy/* {{deploy_path}}/files');
    run('rsync -avczer ./bower_components/font-awesome/* {{deploy_path}}/files');
    run('rsync -avczer ./bower_components/animatewithsass/* {{deploy_path}}/files/scss');
    run('rsync -avczer ./bower_components/wow/dist/wow.js {{deploy_path}}/files/js');
    //run('rsync --exclude=".git" --exclude=".dep" -avczer ./node_modules/jquery-aniview/dist/* {{deploy_path}}/files/js');
})->onStage('local');

desc('Migrate live database and Configuration to local project');
task('deploy:migrate', function () {
    run('ssh {{live_server_ssh_user}}@{{live_server}} "mysqldump --default-character-set="UTF8" --add-drop-table -h localhost -u {{live_db_user}} -p\'{{live_db_pw}}\' {{live_db_name}}" >> {{deploy_path}}/contaodb_{{release_name}}.sql');
    run('mysql --default-character-set=UTF8 --host=localhost -u contao -p\'contaopw\' contao1 < {{deploy_path}}/contaodb_{{release_name}}.sql');
})->onStage('local');

desc('copy release files to html Directory');
task('deploy:update_libs', function () {
  //run('scp {{live_server_ssh_user}}@{{live_server}}  {{live_server_ssh_user}}@{{live_server}}:{{live_server_path}}/contao');
  //run('rsync -avczer ./bower_components/font-awesome/* {{live_server_ssh_user}}@{{live_server}}:{{deploy_path}}/files');
  run('scp -r ./bower_components/font-awesome/* {{live_server_ssh_user}}@{{live_server}}:{{deploy_path}}/files');
  //run('scp -r bower_components/pushy/* {{live_server_ssh_user}}@{{live_server}}:{{deploy_path}}/files');
  //run('scp -r bower_components/wow/dist/wow.js {{live_server_ssh_user}}@{{live_server}}:{{deploy_path}}/files');
  //run('bower install');
  //run('rm -rf {{deploy_path}}/releases');
});

desc('copy release files to html Directory');
task('deploy:movefiles', function () {
  run('rsync --delete --exclude=".git" --exclude=".dep" -avczer {{release_path}}/* {{deploy_path}}');
  //run('rsync --exclude=".git" --exclude=".dep" -avczer ./bower_components/pushy/* {{deploy_path}}/files');
  //run('rsync --exclude=".git" --exclude=".dep" -avczer ./bower_components/font-awesome/* {{deploy_path}}/files');
  run('rm -rf {{deploy_path}}/releases');
});

desc('Restart PHP-FPM service');
desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    //'deploy:shared',
    'deploy:writable',
    //'deploy:update_libs',
    'deploy:movefiles',
    'deploy:migrate',
    //'deploy:vendors',
    'deploy:clear_paths',
    //'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);
desc('Development copying');
task('deploy:dev', [
    'deploy:preview',
    'deploy:migrate'
]);
// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
