<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'Deploy');

// Project repository
set('repository', 'git@github.com:AvengersTraining/DucLS_Batch02_DeployPHP_Application_Training.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', ['.env']);
add('shared_dirs', ['.storage']);

// Writable dirs by web server 
add('writable_dirs', [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storate/app/public',
    'storage/framework',
    'storage/logs'
]);

set('bin/npm', function () {
    return run('which npm');
});

// Hosts

host('167.71.216.4')
    ->user('deploy')
    ->stage('development')
    ->set('deploy_path', '~/{{application}}')
    ->forwardAgent(false);    
    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

task('reload:php-fpm', function () {
    run('sudo /usr/sbin/service php7.3-fpm reload');
});

task('npm:install', function () {
    if (has('previous_release')) {
        if (test('[ -d {{previous_release}}/node_modules ]')) {
            run('cp -R {{previous_release}}/node_modules {{release_path}}');
        }
    }

    run('cd {{release_path}} && {{bin/npm}} install');
});

task('npm:run_dev', function () {
    run('cd {{release_path}} && {{bin/npm}} run dev');
});

task('deployer', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'npm:install',
    'npm:run_dev',
    'deploy:writable',
    'artisan:storage:link',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'reload:php-fpm',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
after('reload:php-fpm', 'artisan:migrate');
after('deployer', 'success');
// Migrate database before symlink new release.

//before('deploy:symlink', 'artisan:migrate');

