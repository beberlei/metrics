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

use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricNotFoundException;

class Prometheus implements Collector, TaggableCollector
{
    /**
     * @var CollectorRegistry
     */
    private $collectorRegistry;

    /**
     * @var string
     */
    private $namespace = '';

    /**
     * @var array
     */
    private $tags = array();

    /**
     * @param CollectorRegistry $collectorRegistry
     * @param string $namespace
     */
    public function __construct(CollectorRegistry $collectorRegistry, $namespace = '')
    {
        $this->collectorRegistry = $collectorRegistry;
        $this->namespace = $namespace;
    }

    /**
     * @inheritdoc
     */
    public function measure($variable, $value)
    {
        $gauge = $this->getOrRegisterGaugeForVariable($variable);

        $gauge->set($value, array_values($this->tags));
    }

    /**
     * @inheritdoc
     */
    public function increment($variable)
    {
        $gauge = $this->getOrRegisterGaugeForVariable($variable);

        $gauge->inc(array_values($this->tags));
    }

    /**
     * @inheritdoc
     */
    public function decrement($variable)
    {
        $gauge = $this->getOrRegisterGaugeForVariable($variable);

        $gauge->dec(array_values($this->tags));
    }

    /**
     * @inheritdoc
     */
    public function timing($variable, $time)
    {
        $this->measure($variable, $time);
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
    }

    /**
     * @inheritdoc
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param string $variable
     * @return \Prometheus\Gauge
     */
    private function getOrRegisterGaugeForVariable($variable)
    {
        try {
            $gauge = $this->collectorRegistry->getGauge($this->namespace, $variable);
        } catch (MetricNotFoundException $e) {
            $gauge = $this->collectorRegistry->registerGauge(
                $this->namespace,
                $variable,
                '',
                array_keys($this->tags)
            );
        }

        return $gauge;
    }
}
