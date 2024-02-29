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

class InlineTaggableGaugeableNullCollector implements Collector, InlineTaggableGaugeableCollector
{
    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value, $tags = array())
    {
        // Not logging, this is a null collector
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable, $tags = array())
    {
        // Not logging, this is a null collector
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($variable, $tags = array())
    {
        // Not logging, this is a null collector
    }

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time, $tags = array())
    {
        // Not logging, this is a null collector
    }

    /**
     * {@inheritdoc}
     */
    public function gauge($variable, $value, $tags = array())
    {
        // Not logging, this is a null collector
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        // Not logging, this is a null collector
    }
}
