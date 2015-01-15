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

use Buzz\Browser;

class Librato implements Collector
{
    private $browser;
    private $hostname;
    private $username;
    private $password;

    private $data = array(
        'counters' => array(),
        'gauges' => array(),
    );

    public function __construct(Browser $browser, $hostname, $username, $password)
    {
        $this->browser  = $browser;
        $this->hostname = $hostname;
        $this->username = $username;
        $this->password = $password;
    }

    public function increment($variable)
    {
        $this->data['counters'][] = array(
            'source' => $this->hostname,
            'name'   => $variable,
            'value'  => 1,
        );
    }

    public function decrement($variable)
    {
        $this->data['counters'][] = array(
            'source' => $this->hostname,
            'name'   => $variable,
            'value'  => -1,
        );
    }

    public function timing($variable, $time)
    {
        $this->data['gauges'][] = array(
            'source' => $this->hostname,
            'name'   => $variable,
            'value'  => $time,
        );
    }

    public function measure($variable, $value)
    {
        $this->data['gauges'][] = array(
            'source' => $this->hostname,
            'name'   => $variable,
            'value'  => $value,
        );
    }

    public function flush()
    {
        if (!$this->data['gauges'] && !$this->data['counters']) {
            return;
        }

        try {
            $this->browser->post('https://metrics-api.librato.com/v1/metrics', array(
                'Authorization: Basic '.base64_encode($this->username.":".$this->password),
                'Content-Type: application/json',
            ), json_encode($this->data));
            $this->data = array('gauges' => array(), 'counters' => array());
        } catch (\Exception $e) {
        }
    }
}
