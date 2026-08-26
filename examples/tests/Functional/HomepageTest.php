<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomepageTest extends WebTestCase
{
    public function testHomepageDisplaysMetrics(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Metrics demo application');
        self::assertSelectorTextContains('ul li', 'Beberlei\Metrics\Collector');
    }

    public function testHomepageIncrementsTheVisitsCounter(): void
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/');

        $row = $crawler->filter('table tr:contains("visits")')->first();

        self::assertGreaterThanOrEqual(1, (int) $row->filter('td')->last()->text());
    }
}
