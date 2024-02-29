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

class Librato implements CollectorInterface
{
    private array $data = ['counters' => [], 'gauges' => []];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $source,
        private readonly string $username,
        private readonly string $password
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->data['gauges'][] = ['source' => $this->source, 'name' => $variable, 'value' => $value];
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->data['counters'][] = ['source' => $this->source, 'name' => $variable, 'value' => 1];
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->data['counters'][] = ['source' => $this->source, 'name' => $variable, 'value' => -1];
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->data['gauges'][] = ['source' => $this->source, 'name' => $variable, 'value' => $time];
    }

    public function flush(): void
    {
        if (!$this->data['gauges'] && !$this->data['counters']) {
            return;
        }

        try {
            $this->httpClient->request('POST', 'https://metrics-api.librato.com/v1/metrics', [
                'auth_basic' => [$this->username, $this->password],
                'json' => $this->data,
            ]);
            $this->data = ['gauges' => [], 'counters' => []];
        } catch (ExceptionInterface) {
        }
    }
}
