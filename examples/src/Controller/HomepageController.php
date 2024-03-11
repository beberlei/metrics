<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Beberlei\Metrics\Collector\CollectorInterface;
use Beberlei\Metrics\Collector\GaugeableCollectorInterface;
use Beberlei\Metrics\Collector\InMemory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    private readonly InMemory $memoryCollector;

    /**
     * @param iterable<CollectorInterface> $collectors
     */
    public function __construct(
        #[TaggedIterator(CollectorInterface::class)]
        private readonly iterable $collectors,
        #[Target('memory')]
        CollectorInterface $memoryCollector,
    ) {
        if (!$memoryCollector instanceof InMemory) {
            throw new \InvalidArgumentException('The memory collector must be an instance of InMemory.');
        }
        $this->memoryCollector = $memoryCollector;
    }

    #[Route('/')]
    public function index(): Response
    {
        $random = date('s');
        $timing = (int) ((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000);
        $gauges = random_int(0, 100);

        foreach ($this->collectors as $collector) {
            $collector->measure('homepage.random', $random);
            $collector->increment('homepage.visits');
            $collector->timing('homepage.duration', $timing);
            if ($collector instanceof GaugeableCollectorInterface) {
                $collector->gauge('homepage.gauge', $gauges);
            }
        }

        return $this->render('homepage/index.html.twig', [
            'collectors' => $this->collectors,
            'random' => $this->memoryCollector->getMeasure('homepage.random'),
            'visits' => $this->memoryCollector->getMeasure('homepage.visits'),
            'timing' => $this->memoryCollector->getTiming('homepage.duration'),
            'gauge' => $this->memoryCollector->getGauge('homepage.gauge'),
        ]);
    }
}
