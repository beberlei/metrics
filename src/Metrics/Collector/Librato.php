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

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Librato implements Collector
{
    private HttpClientInterface $httpClient;

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
     * @param string        $source
     * @param string        $username
     * @param string        $password
     */
    public function __construct(HttpClientInterface $httpClient, $source, $username, $password)
    {
        $this->httpClient = $httpClient;
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
            $this->httpClient->request('POST', 'https://metrics-api.librato.com/v1/metrics', [
                'auth_basic' => [$this->username, $this->password],
                'json' => $this->data,
            ]);
            $this->data = array('gauges' => array(), 'counters' => array());
        } catch (ExceptionInterface) {
        }
    }
}
