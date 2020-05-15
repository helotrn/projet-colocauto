<?php
namespace Deployer;

require 'recipe/laravel.php';

set('application', 'LocoMotion');

set('repository', 'git@gitlab.com:Solon-collectif/locomotion.app.git');

set('git_tty', true);

add('shared_files', ['resources/app/.env']);
add('shared_dirs', ['resources/app/node_modules']);

add('writable_dirs', []);

host('vps.locomotion.app')
    ->stage('production')
    ->user('locomotion')
    ->set('deploy_path', '/var/www/locomotion.app');

host('vps.locomotion.app')
    ->stage('staging')
    ->user('locomotion')
    ->set('deploy_path', '/var/www/staging.locomotion.app');

host('demo.locomotion.app')
    ->stage('demo')
    ->user('locomotion')
    ->set('deploy_path', '/var/www/staging.locomotion.app');

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

desc('Restart queue');
task('deploy:reload:queue', function () {
    $stage = input()->getArgument('stage');
    switch ($stage) {
        case 'production':
            run('sudo /usr/sbin/service locomotion-queue restart');
            break;
        case 'staging':
        case 'demo':
            run('sudo /usr/sbin/service locomotion-staging-queue restart');
            break;
    }
});
after('deploy:reload', 'deploy:reload:queue');

desc('Build frontend application');
task('deploy:build', function () {
    run('cd {{release_path}}/resources/app && yarn && yarn build');
});
before('deploy:symlink', 'deploy:build');

desc('Set release version in .env file');
task('deploy:set_release', function () {
    run('sed -i -e 4,1000000d {{release_path}}/../../shared/resources/app/.env');
    run('cat {{release_path}}/resources/app/.release >> '
        . '{{release_path}}/../../shared/resources/app/.env');
});
before('deploy:build', 'deploy:set_release');

desc('Copy assets');
task('deploy:copy', function () {
    run('rsync -rv {{release_path}}/resources/app/dist/* {{release_path}}/public/ --exclude=index.html');
});
after('deploy:build', 'deploy:copy');
