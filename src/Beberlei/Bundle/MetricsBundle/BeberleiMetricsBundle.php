<?php
/**
 * Beberlei Metrics
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace Beberlei\Bundle\MetricsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BeberleiMetricsBundle extends Bundle
{
    public function boot()
    {
        if (!function_exists('bmetrics_increment')) {
            require_once __DIR__."/../../Metrics/functions.php";
        }

        parent::boot();

        // initialize all collectors
        $this->container->get('beberlei_metrics.registry');
    }

    public function shutdown()
    {
        $this->container->get('beberlei_metrics.flush_service')->flush();
    }
}
