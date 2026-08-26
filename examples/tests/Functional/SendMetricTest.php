<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SendMetricTest extends WebTestCase
{
    public function testSendingAValidMetricRedirectsWithASuccessFlash(): void
    {
        $client = self::createClient();
        $client->request('POST', '/send', [
            'processor' => 'memory',
            'operation' => 'gauge',
            'metric' => 'test.custom',
            'value' => '42',
        ]);

        self::assertResponseRedirects('/');
        $client->followRedirect();

        self::assertSelectorTextContains('.alert-success', 'Sent Gauge(test.custom=42) to the "In-memory" processor.');
    }

    public function testSendingAGaugeToANonGaugeableProcessorFailsWithADangerFlash(): void
    {
        $client = self::createClient();
        $client->request('POST', '/send', [
            'processor' => 'dbal',
            'operation' => 'gauge',
            'metric' => 'test.custom',
            'value' => '42',
        ]);

        self::assertResponseRedirects('/');
        $client->followRedirect();

        self::assertSelectorTextContains('.alert-danger', 'The "Doctrine DBAL" processor does not support gauges.');
    }

    public function testSendingToTheChainProcessorRedirectsWithASuccessFlash(): void
    {
        $client = self::createClient();
        $client->request('POST', '/send', [
            'processor' => 'chain',
            'operation' => 'increment',
            'metric' => 'test.custom',
        ]);

        self::assertResponseRedirects('/');
        $client->followRedirect();

        self::assertSelectorTextContains('.alert-success', 'Sent Increment(test.custom) to the "Chain" processor.');
    }

    public function testSendingToTheOpenTelemetryProcessorRedirectsWithASuccessFlash(): void
    {
        $client = self::createClient();
        $client->request('POST', '/send', [
            'processor' => 'otel',
            'operation' => 'gauge',
            'metric' => 'test.custom',
            'value' => '42',
        ]);

        self::assertResponseRedirects('/');
        $client->followRedirect();

        self::assertSelectorTextContains('.alert-success', 'Sent Gauge(test.custom=42) to the "OpenTelemetry" processor.');
    }
}
