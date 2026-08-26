<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Metrics;

/**
 * Explains, for the demo homepage, what each CollectorInterface operation
 * does, so the "Send a custom metric" form can describe its own inputs.
 *
 * @phpstan-type Operation array{label: string, description: string, requiresValue: bool, requiresGaugeable: bool}
 */
final class OperationCatalog
{
    /** @var array<string, Operation> */
    private const OPERATIONS = [
        'measure' => [
            'label' => 'Measure',
            'description' => 'Adds an arbitrary integer value to a counter.',
            'requiresValue' => true,
            'requiresGaugeable' => false,
        ],
        'increment' => [
            'label' => 'Increment',
            'description' => 'Increments a counter by 1. The value field is ignored.',
            'requiresValue' => false,
            'requiresGaugeable' => false,
        ],
        'decrement' => [
            'label' => 'Decrement',
            'description' => 'Decrements a counter by 1. The value field is ignored.',
            'requiresValue' => false,
            'requiresGaugeable' => false,
        ],
        'timing' => [
            'label' => 'Timing',
            'description' => 'Records a duration, in milliseconds.',
            'requiresValue' => true,
            'requiresGaugeable' => false,
        ],
        'gauge' => [
            'label' => 'Gauge',
            'description' => 'Sets a gauge to an exact value. Only supported by "gaugeable" processors.',
            'requiresValue' => true,
            'requiresGaugeable' => true,
        ],
    ];

    private function __construct()
    {
    }

    /**
     * @return array<string, Operation>
     */
    public static function all(): array
    {
        return self::OPERATIONS;
    }

    /**
     * @return Operation
     */
    public static function get(string $name): array
    {
        return self::OPERATIONS[$name] ?? throw new \InvalidArgumentException(\sprintf('Unknown operation "%s".', $name));
    }
}
