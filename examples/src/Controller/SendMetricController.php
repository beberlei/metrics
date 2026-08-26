<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Metrics\OperationCatalog;
use App\Metrics\ProcessorCatalog;
use Beberlei\Metrics\Collector\CollectorInterface;
use Beberlei\Metrics\Collector\GaugeableCollectorInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the "Send a custom metric" form on the homepage: picks a
 * collector ("processor") and an operation at request time, so it can't
 * simply autowire one fixed CollectorInterface like the other controllers.
 *
 * The locator is an explicit name => service id map (mirroring
 * config/packages/metrics.yaml) rather than a CollectorInterface-tagged
 * locator, because the "chain" collector is intentionally not tagged with
 * CollectorInterface (see BeberleiMetricsExtension) so it isn't
 * double-flushed, yet it must still be reachable here by name.
 */
class SendMetricController extends AbstractController
{
    public function __construct(
        #[AutowireLocator([
            'dbal' => new Autowire(service: 'beberlei_metrics.collector.dbal'),
            'prometheus' => new Autowire(service: 'beberlei_metrics.collector.prometheus'),
            'otel' => new Autowire(service: 'beberlei_metrics.collector.otel'),
            'graphite' => new Autowire(service: 'beberlei_metrics.collector.graphite'),
            'statsd' => new Autowire(service: 'beberlei_metrics.collector.statsd'),
            'dogstatsd' => new Autowire(service: 'beberlei_metrics.collector.dogstatsd'),
            'chain' => new Autowire(service: 'beberlei_metrics.collector.chain'),
            'influxdb_v1' => new Autowire(service: 'beberlei_metrics.collector.influxdb_v1'),
            'logger' => new Autowire(service: 'beberlei_metrics.collector.logger'),
            'memory' => new Autowire(service: 'beberlei_metrics.collector.memory'),
            'null' => new Autowire(service: 'beberlei_metrics.collector.null'),
        ])]
        private readonly ContainerInterface $collectors,
    ) {
    }

    #[Route('/send', name: 'send_metric', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $processorName = (string) $request->request->get('processor', '');
        $operationName = (string) $request->request->get('operation', '');
        $metric = trim((string) $request->request->get('metric', ''));
        $rawValue = (string) $request->request->get('value', '0');

        try {
            $processor = ProcessorCatalog::get($processorName);
            $operation = OperationCatalog::get($operationName);
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());

            return $this->redirectToRoute('homepage');
        }

        if ('' === $metric) {
            $this->addFlash('danger', 'Please provide a metric name.');

            return $this->redirectToRoute('homepage');
        }

        if ($operation['requiresGaugeable'] && !$processor['gaugeable']) {
            $this->addFlash('danger', \sprintf('The "%s" processor does not support gauges.', $processor['label']));

            return $this->redirectToRoute('homepage');
        }

        if ($operation['requiresValue'] && !is_numeric($rawValue)) {
            $this->addFlash('danger', \sprintf('"%s" needs a numeric value.', $operation['label']));

            return $this->redirectToRoute('homepage');
        }

        $value = (int) $rawValue;
        $collector = $this->collectors->get($processorName);

        if (!$collector instanceof CollectorInterface) {
            throw new \LogicException(\sprintf('Service "%s" is not a CollectorInterface.', $processorName));
        }

        match ($operationName) {
            'measure' => $collector->measure($metric, $value),
            'increment' => $collector->increment($metric),
            'decrement' => $collector->decrement($metric),
            'timing' => $collector->timing($metric, $value),
            'gauge' => $collector instanceof GaugeableCollectorInterface
                ? $collector->gauge($metric, $value)
                : throw new \LogicException('This processor does not implement GaugeableCollectorInterface.'),
            default => throw new \LogicException(\sprintf('Unhandled operation "%s".', $operationName)),
        };

        // Flush immediately instead of waiting for kernel.terminate, so the
        // metric is visible in Grafana as soon as this redirect completes.
        $collector->flush();

        $this->addFlash('success', \sprintf(
            'Sent %s(%s%s) to the "%s" processor.',
            $operation['label'],
            $metric,
            $operation['requiresValue'] ? \sprintf('=%d', $value) : '',
            $processor['label'],
        ));

        return $this->redirectToRoute('homepage');
    }
}
