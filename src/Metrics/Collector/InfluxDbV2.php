<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

use Beberlei\Metrics\Utils\Box;
use InfluxDB2\Model\WritePrecision;
use InfluxDB2\Point;
use InfluxDB2\WriteApi;

final class InfluxDbV2 implements CollectorInterface
{
    /** @var list<array{string, int, array<string, mixed>}> */
    private array $data = [];

    /**
     * @param array<string, mixed> $tags
     */
    public function __construct(
        private readonly WriteApi $writeApi,
        private readonly array $tags = [],
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->data[] = [$variable, $value, $tags];
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->data[] = [$variable, 1, $tags];
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->data[] = [$variable, -1, $tags];
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->data[] = [$variable, $time, $tags];
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
        $points = [];
        foreach ($this->data as $data) {
            $point = Point::measurement($data[0])->addField('value', $data[1]);
            foreach (array_merge($this->tags, $data[2]) as $key => $value) {
                $point->addTag((string) $key, self::tagValue($value));
            }
            $points[] = $point;
        }

        try {
            // WriteApi::write() requires a precision, either from the Client's
            // own options or passed explicitly here: it has no built-in default
            // and raises a TypeError otherwise. Points never set an explicit
            // timestamp, so this only picks the unit for the server-assigned one.
            $this->writeApi->write($points, WritePrecision::NS);
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
