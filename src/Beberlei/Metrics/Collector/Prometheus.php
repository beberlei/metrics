<?php
/**
 * Beberlei Metrics.
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

    /** @var array */
    private $data = array(
        'counters' => array(),
        'gauges' => array(),
    );

    /**
     * @var array
     */
    private $tags = array();

    /**
     * @param CollectorRegistry $collectorRegistry
     * @param string            $namespace
     */
    public function __construct(CollectorRegistry $collectorRegistry, $namespace = '')
    {
        $this->collectorRegistry = $collectorRegistry;
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value)
    {
        $this->data['gauges'][] = array(
            'name' => $variable,
            'value' => $value,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable)
    {
        $this->data['counters'][] = array(
            'name' => $variable,
            'value' => 1,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($variable)
    {
        $this->data['counters'][] = array(
            'name' => $variable,
            'value' => -1,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time)
    {
        $this->measure($variable, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (!$this->data['gauges'] && !$this->data['counters']) {
            return;
        }

        $tagsValues = array_values($this->tags);

        foreach ($this->data['counters'] as $counterData) {
            $gauge = $this->getOrRegisterGaugeForVariable($counterData['name']);

            if ($counterData['value'] > 0) {
                $gauge->inc($tagsValues);
            } elseif ($counterData['value'] < 0) {
                $gauge->dec($tagsValues);
            }
        }

        foreach ($this->data['gauges'] as $gaugeData) {
            $gauge = $this->getOrRegisterGaugeForVariable($gaugeData['name']);

            $gauge->set($gaugeData['value'], $tagsValues);
        }

        $this->data = array('counters' => array(), 'gauges' => array());
    }

    /**
     * {@inheritdoc}
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param string $variable
     *
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
