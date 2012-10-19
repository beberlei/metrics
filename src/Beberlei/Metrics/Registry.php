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

namespace Beberlei\Metrics;

/**
 * Registry for metrics collectors.
 */
class Registry
{
    static private $defaultCollector = 'default';

    static private $collectors = array();

    static public function clear()
    {
        self::$collectors = array();
    }

    static public function setDefaultName($defaultCollector)
    {
        self::$defaultCollector = $defaultCollector;
    }

    static public function set($name, Collector\Collector $collector)
    {
        self::$collectors[$name] = $collector;
    }

    /**
     * @param null|string $name
     * @return \Beberlei\Metrics\Collector\Collector
     */
    static public function get($name = null)
    {
        $name = $name ?: self::$defaultCollector;

        if ( ! isset(self::$collectors[$name])) {
            self::$collectors[$name] = new Collector\Null();
        }

        return self::$collectors[$name];
    }

    static public function all()
    {
        return self::$collectors;
    }
}

