<?php
namespace Deployer;

require 'recipe/common.php';

// Configuration
// You have to set live server credentials. They are not set because of security reasons.
set('live_server', "w00bb878.kasserver.com");
set('live_server_path', "/www/htdocs/w00bb878/yakup");
set('live_server_ssh_user', "ssh-w00bb878");

set('live_db_name', "d0265a9c");
set('live_db_user', "d0265a9c");
set('live_db_pw', "cyzAzT8gbt2ahXW35roJ");

set('local_db_name', "d0265a9c");
set('local_db_user', "d0265a9c");
set('local_db_pw', "cyzAzT8gbt2ahXW35roJ");

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
    ->set('deploy_path', '/var/www/html/contao');


// Tasks
desc('Migrate live database and Configuration to local project');
task('deploy:preview', function () {
    run('rsync --delete --exclude=".git" --exclude=".dep" -avczer ./files/* {{deploy_path}}/files');
    run('rsync --delete --exclude=".git" --exclude=".dep" -avczer ./templates/* {{deploy_path}}/templates');
    run('rsync --exclude=".git" --exclude=".dep" -avczer ./bower_components/pushy/* {{deploy_path}}/files');
    run('rsync --exclude=".git" --exclude=".dep" -avczer ./bower_components/font-awesome/* {{deploy_path}}/files');
})->onStage('local');

desc('copy release files to html Directory');
task('deploy:movefiles', function () {
    run('rsync --delete --exclude=".git" --exclude=".dep" -avczer {{release_path}}/* {{deploy_path}}');
    run('rm -rf {{deploy_path}}/releases');
});

desc('Migrate live database and Configuration to local project');
task('deploy:migrate', function () {
    run('ssh {{live_server_ssh_user}}@{{live_server}} "mysqldump --default-character-set="UTF8" --add-drop-table -h localhost -u {{live_db_user}} -p\'{{live_db_pw}}\' {{live_db_name}}" >> {{deploy_path}}/contaodb_{{release_name}}.sql');
    run('mysql --default-character-set=UTF8 --host=localhost -u contao -p\'contaopw\' contao1 < {{deploy_path}}/contaodb_{{release_name}}.sql');
})->onStage('local');

desc('Restart PHP-FPM service');
desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    //'deploy:shared',
    'deploy:writable',
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
