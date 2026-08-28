<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

use Aws\CloudWatch\CloudWatchClient;
use Beberlei\Metrics\Utils\Box;

final class CloudWatch implements CollectorInterface
{
    /** @var list<array{string, int|float, string, array<string, mixed>}> */
    private array $data = [];

    /**
     * @param array<string, mixed> $tags
     */
    public function __construct(
        private readonly CloudWatchClient $client,
        private readonly string $namespace = 'beberlei/metrics',
        private readonly array $tags = [],
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->data[] = [$variable, $value, 'Count', $tags];
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->data[] = [$variable, 1, 'Count', $tags];
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->data[] = [$variable, -1, 'Count', $tags];
    }

    public function timing(string $variable, int|float $time, array $tags = []): void
    {
        $this->data[] = [$variable, $time, 'Milliseconds', $tags];
    }

    public function flush(): void
    {
        if (!$this->data) {
            return;
        }

        Box::box($this->doFlush(...));
    }

    private function doFlush(): void
    {
        $metricData = [];
        foreach ($this->data as [$variable, $value, $unit, $tags]) {
            $dimensions = [];
            foreach (array_merge($this->tags, $tags) as $key => $tagValue) {
                $dimensions[] = ['Name' => (string) $key, 'Value' => self::tagValue($tagValue)];
            }

            $metricData[] = [
                'MetricName' => $variable,
                'Value' => $value,
                'Unit' => $unit,
                'Dimensions' => $dimensions,
            ];
        }

        try {
            // PutMetricData accepts at most 1000 MetricData entries per call.
            foreach (array_chunk($metricData, 1000) as $chunk) {
                $this->client->putMetricData([
                    'Namespace' => $this->namespace,
                    'MetricData' => $chunk,
                ]);
            }
        } finally {
            $this->data = [];
        }
    }

    private static function tagValue(mixed $value): string
    {
        return match (true) {
            $value instanceof \BackedEnum => (string) $value->value,
            $value instanceof \UnitEnum => $value->name,
            default => (string) $value,
        };
    }
}
