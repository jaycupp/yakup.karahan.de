<?php
namespace Deployer;

require 'recipe/common.php';

// Configuration

set('repository', 'ssh-w00bb878@w00bb878.kasserver.com:/www/htdocs/w00bb878/yakup/git/yakup.karahan.de.git');
set('git_tty', true); // [Optional] Allocate tty for git on first deployment
set('ssh_multiplexing', false);
set('shared_files', []);
set('shared_dirs', []);
set('writable_dirs', []);


// Hosts

host('w00bb878.kasserver.com')
    ->user('ssh-w00bb878')
    ->stage('production')
    ->set('deploy_path', '/www/htdocs/w00bb878/yakup/contao');


localhost()
    ->stage('local')
    ->set('deploy_path', '/var/www/html');


// Tasks

desc('Restart PHP-FPM service');
// task('php-fpm:restart', function () {
//     // The user must have rights for restart service
//     // /etc/sudoers: username ALL=NOPASSWD:/bin/systemctl restart php-fpm.service
//     run('sudo systemctl restart php-fpm.service');
// });
// after('deploy:symlink', 'php-fpm:restart');

desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    //'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
