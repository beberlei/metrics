<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Utils;

final class Box
{
    public static function box(callable $callable): mixed
    {
        try {
            set_error_handler(static fn () => null);

            return $callable();
        } catch (\Throwable) {
            // ignore
        } finally {
            restore_error_handler();
        }

        return null;
    }
}
