<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests\Collector;

use Beberlei\Metrics\Collector\DogStatsD;
use Beberlei\Metrics\Collector\StatsD;
use Beberlei\Metrics\Collector\Telegraf;
use PHPUnit\Framework\TestCase;

final class UdpCollectorsTest extends TestCase
{
    public function testTelegrafEmitsDynamicTagsInTelegrafFormat(): void
    {
        $tags = [
            'path' => 'player/home fr',
            'status' => '200',
            'method' => 'GET',
        ];

        $payloads = $this->capture(static function (int $port) use ($tags): void {
            $collector = new Telegraf('127.0.0.1', $port);
            $collector->increment('front.requests', $tags);
            $collector->timing('front.request_duration', 12.345, $tags);
            $collector->flush();
        }, 2);

        self::assertSame([
            'front.requests,path=player%2Fhome%20fr,status=200,method=GET:1|c',
            'front.request_duration,path=player%2Fhome%20fr,status=200,method=GET:12.345|ms',
        ], $payloads);
    }

    public function testTelegrafTagsEveryMetricTypeAndPerCallTagsOverrideDefaults(): void
    {
        $payloads = $this->capture(static function (int $port): void {
            $collector = new Telegraf('127.0.0.1', $port, 'app.', [
                'environment' => 'production',
                'method' => 'OTHER',
            ]);
            $tags = ['method' => 'GET', 'path' => 'player/home fr'];

            $collector->measure('measure', 7, $tags);
            $collector->increment('increment', $tags);
            $collector->decrement('decrement', $tags);
            $collector->timing('timing', 12.345, $tags);
            $collector->gauge('gauge', 4, $tags);
            $collector->set('set', 'member', $tags);
            $collector->flush();
        }, 6);

        $suffix = ',environment=production,method=GET,path=player%2Fhome%20fr';
        self::assertSame([
            'app.measure' . $suffix . ':7|c',
            'app.increment' . $suffix . ':1|c',
            'app.decrement' . $suffix . ':-1|c',
            'app.timing' . $suffix . ':12.345|ms',
            'app.gauge' . $suffix . ':4|g',
            'app.set' . $suffix . ':member|s',
        ], $payloads);
    }

    public function testTelegrafDropsBufferedMetricsWhenOpeningTheSocketFails(): void
    {
        $collector = new Telegraf("invalid\0host");
        $collector->increment('request.count');

        $collector->flush();

        self::assertSame([], new \ReflectionProperty($collector, 'data')->getValue($collector));
    }

    public function testStatsDEmitsPreciseTimings(): void
    {
        $payloads = $this->capture(static function (int $port): void {
            $collector = new StatsD('127.0.0.1', $port);
            $collector->timing('request.duration', 12.345);
            $collector->flush();
        }, 1);

        self::assertSame(['request.duration:12.345|ms'], $payloads);
    }

    public function testDogStatsDEmitsPreciseTimings(): void
    {
        $payloads = $this->capture(static function (int $port): void {
            $collector = new DogStatsD('127.0.0.1', $port);
            $collector->timing('request.duration', 12.345, ['method' => 'GET']);
            $collector->flush();
        }, 1);

        self::assertSame(['request.duration:12.345|ms|#method:GET'], $payloads);
    }

    /**
     * @param callable(int): void $send
     *
     * @return list<string>
     */
    private function capture(callable $send, int $expectedPayloads): array
    {
        $errorCode = 0;
        $errorMessage = '';
        $server = stream_socket_server('udp://127.0.0.1:0', $errorCode, $errorMessage, flags: \STREAM_SERVER_BIND);
        self::assertIsResource($server, $errorMessage);

        $address = stream_socket_get_name($server, false);
        self::assertIsString($address);
        $port = (int) substr(strrchr($address, ':'), 1);
        stream_set_timeout($server, 1);

        try {
            $send($port);

            $payloads = [];
            for ($index = 0; $index < $expectedPayloads; ++$index) {
                $payload = stream_socket_recvfrom($server, 65_535);
                self::assertNotFalse($payload, \sprintf('Missing UDP payload %d of %d.', $index + 1, $expectedPayloads));
                $payloads[] = $payload;
            }

            return $payloads;
        } finally {
            fclose($server);
        }
    }
}
