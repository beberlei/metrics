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

class DogStatsD implements Collector, InlineTaggableGaugeableCollector
{
    /** @var string */
    private $host;

    /** @var string */
    private $port;

    /** @var string */
    private $prefix;

    /** @var array */
    private $data;

    /**
     * @param string $host
     * @param string $port
     * @param string $prefix
     */
    public function __construct($host = 'localhost', $port = '8125', $prefix = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->prefix = $prefix;
        $this->data = array();
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value, $tags = array())
    {
        $this->data[] = sprintf('%s:%s|c%s', $variable, $value, $this->buildTagString($tags));
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable, $tags = array())
    {
        $this->data[] = $variable.':1|c'.$this->buildTagString($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($variable, $tags = array())
    {
        $this->data[] = $variable.':-1|c'.$this->buildTagString($tags);
    }

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time, $tags = array())
    {
        $this->data[] = sprintf('%s:%s|ms%s', $variable, $time, $this->buildTagString($tags));
    }

    /**
     * {@inheritdoc}
     */
    public function gauge($variable, $value, $tags = array())
    {
        $this->data[] = sprintf('%s:%s|g%s', $variable, $value, $this->buildTagString($tags));
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (!$this->data) {
            return;
        }

        $fp = fsockopen('udp://'.$this->host, $this->port, $errno, $errstr, 1.0);

        if (!$fp) {
            return;
        }

        $level = error_reporting(0);
        foreach ($this->data as $line) {
            fwrite($fp, $this->prefix.$line);
        }
        error_reporting($level);

        fclose($fp);

        $this->data = array();
    }

    /**
     * Given a key/value map of metric tags, builds them into a
     * DogStatsD tag string and returns the string.
     *
     * @param $tags array
     *
     * @return string
     */
    private function buildTagString($tags)
    {
        $results = array();

        foreach ($tags as $key => $value) {
            $results[] = sprintf('%s:%s', $key, $value);
        }

        $tagString = implode(',', $results);

        if (strlen($tagString)) {
            $tagString = sprintf('|#%s', $tagString);
        }

        return $tagString;
    }
}
