<?php

require_once __DIR__.'/../vendor/autoload.php';

$metrics = \Beberlei\Metrics\Factory::create('credis');

while (true) {
    $metrics->increment('foo.bar');
    $metrics->decrement('foo.baz');
    $metrics->measure('foo', rand(1, 10));
    usleep(10000);
}