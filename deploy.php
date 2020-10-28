<?php
namespace Deployer;

require 'recipe/laravel.php';

function sendMattermostNotification($message) {
    $dotenv = \Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();

    $url = $_ENV['MATTERMOST_WEBHOOK'];

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode([
                'text' => $message,
            ]),
        ),
    );

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    if ($result === false) {
        echo "Erreur d'envoi de notification à Mattermost...";
        sleep(1);
    } else {
        // Rate-limiting
        usleep(500 * 1000);
    }
}

set('application', 'LocoMotion');

set('repository', 'git@gitlab.com:Solon-collectif/locomotion.app.git');

set('git_tty', true);
set('default_timeout', 600);

add('shared_files', ['resources/app/.env']);
add('shared_dirs', ['resources/app/node_modules']);

add('writable_dirs', []);

host('production')
    ->hostname('vps.locomotion.app')
    ->set('branch', 'production')
    ->stage('production')
    ->user('locomotion')
    ->set('deploy_path', '/var/www/locomotion.app');

foreach (['staging', 'demo'] as $env) {
    host($env)
        ->hostname('vps.locomotion.app')
        ->stage('staging')
        ->user('locomotion')
        ->set('deploy_path', "/var/www/$env.locomotion.app");
}

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
    run('sed -i -e /^VUE_APP_RELEASE/d {{release_path}}/../../shared/resources/app/.env');
    run('cat {{release_path}}/resources/app/.release >> '
        . '{{release_path}}/../../shared/resources/app/.env');
});
before('deploy:build', 'deploy:set_release');

desc('Copy assets');
task('deploy:copy', function () {
    run('rsync -rv {{release_path}}/resources/app/dist/* {{release_path}}/public/ --exclude=index.html');
});
after('deploy:build', 'deploy:copy');

desc('Get currently deployed commit');
task('status:revision', function () {
    writeLn(run('cd {{release_path}} && git log -n1'));
});

desc('Notify on Mattermost webhook on upcoming deploy');
task('deploy:notify_before', function () {
    $username = system('whoami');
    $branch = get('branch');
    $commit = system("git log --oneline -n1 $branch | head -n1");

    $message = "Un déploiement de $branch ('$commit') a été lancé par $username.";

    sendMattermostNotification($message);
});
before('deploy:prepare', 'deploy:notify_before');

desc('Notify on Mattermost webhook on completed deploy');
task('deploy:notify_after', function () {
    $username = system('whoami');
    $branch = get('branch');
    $commit = system("git log --oneline -n1 $branch | head -n1");

    $message = "Un déploiement de $branch ('$commit') est terminé!";

    sendMattermostNotification($message);
});
after('deploy:symlink', 'deploy:notify_after');

desc('Notify on Mattermost webhook on failed deploy');
task('deploy:notify_failed', function () {
    $username = system('whoami');
    $branch = get('branch');
    $commit = system("git log --oneline -n1 $branch | head -n1");

    $message = "Un déploiement de $branch ('$commit') a échoué.";

    sendMattermostNotification($message);
});
after('deploy:failed', 'deploy:notify_failed');
