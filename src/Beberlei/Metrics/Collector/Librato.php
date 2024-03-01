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

use Buzz\Browser;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Librato implements Collector
{
    const ENDPOINT = 'https://metrics-api.librato.com/v1/metrics';

    /** @var HttpClientInterface|Browser */
    private $client;

    /** @var string */
    private $source;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var array */
    private $data = array(
        'counters' => array(),
        'gauges' => array(),
    );

    /**
     * @param HttpClientInterface|Browser   $client
     * @param string                        $source
     * @param string                        $username
     * @param string                        $password
     */
    public function __construct($client, $source, $username, $password)
    {
        if (!$client instanceof Browser && !$client instanceof HttpClientInterface) {
            throw new \TypeError(sprintf('Argument 1 passed to %s::%s() must be an instance of %s or %s, %s given', __CLASS__, __METHOD__, HttpClientInterface::class, Browser::class, \is_object($client) ? \get_class($client) : \gettype($client)));
        }

        $this->client = $client;
        $this->source = $source;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable)
    {
        $this->data['counters'][] = array(
            'source' => $this->source,
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
            'source' => $this->source,
            'name' => $variable,
            'value' => -1,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time)
    {
        $this->data['gauges'][] = array(
            'source' => $this->source,
            'name' => $variable,
            'value' => $time,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value)
    {
        $this->data['gauges'][] = array(
            'source' => $this->source,
            'name' => $variable,
            'value' => $value,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (!$this->data['gauges'] && !$this->data['counters']) {
            return;
        }

        try {
            if ($this->client instanceof Browser) {
                $this->client->post(self::ENDPOINT, array(
                    'Authorization: Basic '.base64_encode($this->username.':'.$this->password),
                    'Content-Type: application/json',
                ), json_encode($this->data));
            } else {
                $this->client->request('POST', self::ENDPOINT, array(
                    'json' => $this->data,
                    'auth_basic' => array($this->username, $this->password)
                ));
            }

            $this->data = array('gauges' => array(), 'counters' => array());
        } catch (\Exception $e) {
        }
    }
}
