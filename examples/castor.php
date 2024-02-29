<?php

use Castor\Attribute\AsTask;

use function Castor\import;
use function Castor\io;
use function Castor\notify;
use function docker\about;
use function docker\build;
use function docker\docker_compose_run;
use function docker\generate_certificates;
use function docker\up;
use function docker\workers_start;
use function docker\workers_stop;

import(__DIR__ . '/.castor');

/**
 * @return array<string, mixed>
 */
function create_default_variables(): array
{
    return [
        'project_name' => 'symfony-metrics',
        'root_domain' => 'symfony-metrics.test',
        'extra_domains' => [
            'grafana.symfony-metrics.test',
        ],
    ];
}

#[AsTask(description: 'Builds and starts the infrastructure, then install the application (composer, yarn, ...)')]
function start(): void
{
    io()->title('Starting the stack');

    workers_stop();
    generate_certificates(force: false);
    build();
    up();
    cache_clear();
    install();
    migrate();
    workers_start();

    notify('The stack is now up and running.');
    io()->success('The stack is now up and running.');

    about();
}

#[AsTask(description: 'Installs the application (composer, yarn, ...)', namespace: 'app', aliases: ['install'])]
function install(): void
{
    io()->title('Installing the application');

    io()->section('Installing PHP dependencies');
    docker_compose_run('composer install -n --prefer-dist --optimize-autoloader');

    io()->section('Installing importmap');
    docker_compose_run('bin/console importmap:install');

    qa\install();
}

#[AsTask(description: 'Clear the application cache', namespace: 'app', aliases: ['cache-clear'])]
function cache_clear(): void
{
    io()->title('Clearing the application cache');

    docker_compose_run('rm -rf var/cache/ && bin/console cache:warmup');
}

#[AsTask(description: 'Migrates database schema', namespace: 'app:db', aliases: ['migrate'])]
function migrate(): void
{
    io()->title('Migrating the database schema');

    docker_compose_run('bin/console doctrine:database:create --if-not-exists');
    docker_compose_run('bin/console doctrine:migration:migrate -n --allow-no-migration');
}
