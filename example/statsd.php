<?php

require_once __DIR__.'/../vendor/autoload.php';

$metrics = \Beberlei\Metrics\Factory::create('statsd');

while (true) {
    $metrics->increment('foo.bar');
    $metrics->decrement('foo.baz');
    $metrics->measure('foo', rand(1, 10));
    $metrics->flush();
    usleep(500);
}
