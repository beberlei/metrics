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

use Beberlei\Metrics\Registry;

function bmetrics_increment($variable, $registryKey = null)
{
    Registry::get($registryKey)->increment($variable);
}

function bmetrics_decrement($variable, $registryKey = null)
{
    Registry::get($registryKey)->decrement($variable);
}

function bmetrics_measure($variable, $value, $registryKey = null)
{
    Registry::get($registryKey)->measure($variable, $value);
}

function bmetrics_timing($variable, $time, $registryKey = null)
{
    Registry::get($registryKey)->timing($variable, $time);
}

