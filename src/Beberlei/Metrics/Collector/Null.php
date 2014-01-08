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

namespace Beberlei\Metrics\Collector;

class Null implements Collector
{
    public function increment($variable)
    {
    }
    public function decrement($variable)
    {
    }
    public function timing($variable, $time)
    {
    }
    public function measure($variable, $value)
    {
    }
    public function gauge($variable, $value)
    {
    }
    public function flush()
    {
    }
}

