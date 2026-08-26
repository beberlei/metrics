<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ServicesResetter;

final class PrometheusTest extends WebTestCase
{
    public function testPrometheusEndpointExposesMetrics(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');

        // Collectors are flushed when their services are reset
        $resetter = self::getContainer()->get('services_resetter');

        if (!$resetter instanceof ServicesResetter) {
            throw new \RuntimeException(\sprintf('The "%s" service is not a ServicesResetter.', ServicesResetter::class));
        }

        $resetter->reset();

        $client->request('GET', '/prometheus');

        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('text/plain; version=0.0.4', (string) $client->getResponse()->headers->get('Content-Type'));
        self::assertStringContainsString('app_homepage_visits', (string) $client->getResponse()->getContent());
    }
}
