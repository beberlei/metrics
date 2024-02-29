<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrometheusController extends AbstractController
{
    public function __construct(
        private readonly CollectorRegistry $registry,
        private readonly RenderTextFormat $renderer,
    ) {
    }

    #[Route('/prometheus')]
    public function index(): Response
    {
        return new Response(
            $this->renderer->render($this->registry->getMetricFamilySamples()),
            headers: ['Content-Type' => RenderTextFormat::MIME_TYPE],
        );
    }
}
