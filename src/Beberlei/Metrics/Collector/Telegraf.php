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

/**
 * Sends statistics to the StatsD daemon over UDP,
 * ad hoc implementation for the StatsD - Telegraf integration,
 * support tagging.
 */
class Telegraf implements Collector, TaggableGaugeableCollector
{
    /** @var string */
    private $host;

    /** @var string */
    private $port;

    /** @var string */
    private $prefix;

    /** @var array */
    private $data;

    /** @var array */
    private $tags;

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
    public function setTags($tags)
    {
        $this->tags = $tags;
	}

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time, $tags = array())
    {
        $this->data[] = sprintf('%s%s:%s|ms', $variable, $this->buildTagString(array_merge($this->tags, $tags)), $time);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable, $tags = array())
    {
        $this->data[] = $variable.$this->buildTagString(array_merge($this->tags, $tags)).':1|c';
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($variable, $tags = array())
    {
        $this->data[] = $variable.$this->buildTagString(array_merge($this->tags, $tags)).':-1|c';
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value, $tags = array())
    {
        $this->data[] = sprintf('%s%s:%s|c', $variable, $this->buildTagString(array_merge($this->tags, $tags)), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function gauge($variable, $value, $tags = array())
    {
        $this->data[] = sprintf('%s%s:%s|g', $variable, $this->buildTagString(array_merge($this->tags, $tags)), $value);
    }

    /**
     * @param $variable
     * @param $value
     */
    public function set($variable, $value)
    {
        $this->data[] = sprintf('%s%s:%s|s', $variable, $this->buildTagString(array_merge($this->tags, $tags)), $value);
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
     * telegraf statsd tag string and returns the string.
     *
     * @param $tags array
     *
     * @return string
     */
    private function buildTagString($tags)
    {
		$tagString = http_build_query($tags, '', ',');
        $tagString = (strlen($this->tags) > 0 ? ','.$this->tags : $this->tags);
		return $tagString;
    }
}
