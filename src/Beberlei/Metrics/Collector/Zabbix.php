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

use Net\Zabbix\Sender;

/**
 * Zabbix Collector
 */
class Zabbix implements Collector
{
    /**
     * @var Net\Zabbix\Sender
     */
    private $sender;

    /**
     * @var string
     */
    private $prefix;

    public function __construct(Sender $sender, $prefix = null)
    {
        $this->sender = $sender;
        $this->prefix = $prefix ?: gethostname();
    }

    public function increment($variable)
    {
        $this->sender->addData($this->prefix, $variable, '1');
    }

    public function decrement($variable)
    {
        $this->sender->addData($this->prefix, $variable, '-1');
    }

    public function timing($variable, $time)
    {
        $this->sender->addData($this->prefix, $variable, $time);
    }

    public function measure($variable, $value)
    {
        $this->sender->addData($this->prefix, $variable, $value);
    }

    public function flush()
    {
        $this->sender->send();
    }
}
