<?php

require_once __DIR__.'/../vendor/autoload.php';

$credis = new Credis_Client();

$metrics = \Beberlei\Metrics\Factory::create('credis', array(
    'credis_client' => $credis,
));

while (true) {
    $metrics->increment('foo.bar');
    $metrics->decrement('foo.baz');
    $metrics->measure('foo', rand(1, 10));
    usleep(10000);
}
