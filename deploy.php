<?php
namespace Deployer;

require 'recipe/laravel.php';

set('application', 'Locomotion');

set('repository', 'git@git.molotov.ca:molotov/locomotion.app.git');

set('git_tty', true);

add('shared_files', []);
add('shared_dirs', []);

add('writable_dirs', []);

host('locomotion.app')
    ->stage('production')
    ->user('locomotion')
    ->set('deploy_path', '/var/www/locomotion.app');

task('build', function () {
    run('cd {{release_path}} && build');
});

after('deploy:failed', 'deploy:unlock');

before('deploy:symlink', 'artisan:migrate');

desc('Reload services');
task('deploy:reload', function () {});
after('deploy:symlink', 'deploy:reload');

desc('Reload nginx');
task('deploy:reload:nginx', function () {
    run('sudo /usr/sbin/service nginx reload');
});
after('deploy:reload', 'deploy:reload:nginx');

desc('Reload php-fpm');
task('deploy:reload:php-fpm', function () {
    run('sudo /usr/sbin/service php7.3-fpm reload');
});
after('deploy:reload', 'deploy:reload:php-fpm');
