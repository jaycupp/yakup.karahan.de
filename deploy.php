<?php
namespace Deployer;

require 'recipe/common.php';

// Configuration
// You have to set live server credentials. They are not set because of security reasons.
set('live_server', "");
set('live_server_path', "");
set('live_server_ssh_user', "");

set('live_db_name', "");
set('live_db_user', "");
set('live_db_pw', "");

set('repository', '{{live_server_ssh_user}}@{{live_server}}:{{live_server_path}}/git/yakup.karahan.de.git');
set('git_tty', true); // [Optional] Allocate tty for git on first deployment
set('ssh_multiplexing', false);
set('shared_files', []);
set('shared_dirs', []);
set('writable_dirs', []);

// Hosts

host(get('live_server_path'))
    ->user(get('live_server_ssh_user'))
    ->stage('production')
    ->set('deploy_path', get('live_server_path'));


localhost()
    ->stage('local')
    ->set('deploy_path', '/var/www/html/contao');


// Tasks
desc('copy release files to html Directory');
task('deploy:movefiles', function () {
    run('rsync --delete --exclude=".git" --exclude=".dep" -avcze {{release_path}}/* {{deploy_path}}');
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

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
