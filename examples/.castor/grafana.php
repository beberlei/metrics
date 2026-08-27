<?php

namespace grafana;

use Castor\Attribute\AsTask;

use function Castor\io;
use function docker\docker_compose;

#[AsTask(description: 'Forces Grafana to re-provision its datasources and dashboards', aliases: ['grafana'])]
function provision(): void
{
    io()->title('Re-provisioning Grafana');

    io()->comment('Grafana only reliably picks up changes under infrastructure/docker/services/grafana/ on (re)start, so this restarts the container.');
    docker_compose(['restart', 'grafana']);

    io()->success('Grafana has been restarted: datasources and dashboards are freshly re-provisioned.');
}
