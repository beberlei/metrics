# AGENTS.md

Instructions for AI agents working on the example application of
`beberlei/metrics` (this `examples/` directory).

## Fundamental rule: never run PHP/Node/Composer on the host machine

Everything goes through the `builder` container, via Castor, run from this
`examples/` directory:

```bash
castor builder -- bin/console cache:clear    # one-off command
castor builder -- bin/console make:migration # one-off command
```

Host prerequisites only: Docker, Bash, Castor.

## Essential commands

```bash
castor start                                 # build + install + up + migrate
castor stop                                  # stop the stack
castor logs [--service=service]              # logs (frontend, postgres, grafana, ...)
castor app:install                           # composer install + importmap + qa:install
castor app:db:migrate                        # Doctrine migrations (alias: castor migrate)
castor postgres -- select 1 from foobar      # one-off command database query
castor grafana                               # restart Grafana to re-provision datasources/dashboards
```

Docker:

```bash
castor docker:build [--service=service]
castor docker:up [--service=service]
```

## Castor contexts

The context changes how tasks are executed (`APP_ENV`, compose files, etc.):

```bash
castor --context=test qa:phpunit             # APP_ENV=test, for tests
castor --context=ci ...                      # like test, tuned for CI
```

Always run tests and anything touching the database with `--context=test`.
Without option, the `default` context applies.

## Stack

- Symfony application at the root of `examples/` (docroot = `examples/public`), mounted
  in `/var/www`
- The library (repository root) is mounted in `/metrics` and installed through a
  Composer `path` repository
- PostgreSQL 16: user/pass/db = `app`/`app`, DATABASE_URL already configured
- One backend per collector: Grafana, Graphite/StatsD, InfluxDB v1 and v2, LocalStack
  (CloudWatch), Prometheus (see `infrastructure/docker/docker-compose.yml`)
- nginx + php-fpm (service `frontend`, listening on port 8080), Traefik router, HTTPS on
  `<root_domain>` (see `castor.php`)
- No production images: this is a demo application

## QA — before considering a task done

Tools run inside the builder.

```bash
castor qa                                    # everything: cs + phpstan + twig-cs + phpunit
castor qa:cs [--dry-run]                     # PHP-CS-Fixer (.php-cs-fixer.php)
castor qa:phpstan [-b]                       # PHPStan level 8 (phpstan.neon)
castor qa:twig-cs                            # Twig-CS-Fixer
castor qa:phpunit                            # PHPUnit
```

After any PHP/Twig code change: `castor qa:cs --dry-run`,
`castor qa:phpstan`, then `castor qa:phpunit`.

The library itself (`../src`, `../tests`) has its own QA tooling, run by the
GitHub Actions workflow of the repository.

## Conventions

1. **Never invoke `docker compose` by hand**: use the `docker_compose()` /
   `docker_compose_run()` functions from `.castor/docker.php` to write new tasks.
2. **Never hardcode ports or project names**: use `variable('project_name')` etc.
3. New recurring task? Make it a Castor task (`castor.php` or `.castor/*.php`),
   not a shell script.
4. QA tool dependencies live in `tools/<tool>/composer.json`
   (not in `composer.json`).
