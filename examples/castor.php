<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\guard_min_version;
use function Castor\import;
use function Castor\io;
use function Castor\notify;
use function Castor\variable;
use function docker\about;
use function docker\build;
use function docker\docker_compose_run;
use function docker\up;

defined('CASTOR_USE_CHDIR') || define('CASTOR_USE_CHDIR', true);

guard_min_version('1.8.0');

import(__DIR__ . '/.castor');

/**
 * @return array{project_name: string, root_domain: string}
 */
function create_default_variables(): array
{
    $projectName = 'symfony-metrics';
    $tld = 'test';

    return [
        'project_name' => $projectName,
        'root_domain' => "{$projectName}.{$tld}",
    ];
}

#[AsTask(description: 'Builds and starts the infrastructure, then install the application (composer, ...)')]
function start(): void
{
    io()->title('Starting the stack');

    build();
    install();
    up(profiles: ['default']);
    migrate();

    notify('The stack is now up and running.');
    io()->success('The stack is now up and running.');

    about();
}

#[AsTask(description: 'Installs the application (composer, ...)', namespace: 'app', aliases: ['install'])]
function install(): void
{
    io()->title('Installing the application');

    $basePath = variable('root_dir');

    if (is_file("{$basePath}/composer.json")) {
        io()->section('Installing PHP dependencies');
        docker_compose_run(['composer', 'install', '-n', '--prefer-dist', '--optimize-autoloader']);
    }
    if (is_file("{$basePath}/yarn.lock")) {
        io()->section('Installing Node.js dependencies');
        docker_compose_run(['yarn', 'install', '--immutable']);
    } elseif (is_file("{$basePath}/package.json")) {
        io()->section('Installing Node.js dependencies');

        if (is_file("{$basePath}/package-lock.json")) {
            docker_compose_run(['npm', 'ci']);
        } else {
            docker_compose_run(['npm', 'install']);
        }
    }
    if (is_file("{$basePath}/importmap.php")) {
        io()->section('Installing importmap');
        docker_compose_run(['bin/console', 'importmap:install']);
    }

    qa\install();
}

#[AsTask(description: 'Update dependencies')]
function update(bool $withTools = false): void
{
    io()->title('Updating dependencies...');

    docker_compose_run(['composer', 'update', '-o']);

    if ($withTools) {
        qa\update();
    }
}

#[AsTask(description: 'Clears the application cache', namespace: 'app', aliases: ['cache-clear'])]
function cache_clear(bool $warm = true): void
{
    io()->title('Clearing the application cache');

    docker_compose_run(['rm', '-rf', 'var/cache/']);

    if ($warm) {
        cache_warmup();
    }
}

#[AsTask(description: 'Warms the application cache', namespace: 'app', aliases: ['cache-warmup'])]
function cache_warmup(): void
{
    io()->title('Warming the application cache');

    docker_compose_run(['bin/console', 'cache:warmup'], c: context()->withAllowFailure());
}

#[AsTask(description: 'Migrates database schema', namespace: 'app:db', aliases: ['migrate'])]
function migrate(): void
{
    io()->title('Migrating the database schema');

    docker_compose_run(['bin/console', 'doctrine:database:create', '--if-not-exists']);
    docker_compose_run(['bin/console', 'doctrine:migration:migrate', '-n', '--allow-no-migration', '--all-or-nothing']);
}
