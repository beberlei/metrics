<?php

namespace Beberlei\Metrics\Collector;

class TaggableNullCollector implements Collector, InlineTaggableGaugeableCollector
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
