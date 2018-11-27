#!/usr/bin/env php
<?php

namespace ImagickContained;

use ImagickContained\CLIFunction;

error_reporting(E_ALL);
//require __DIR__ . '/lib/ImagickContained\CLIFunction.php';

// The vendor directory comes from the host machine/project
$autoloader = require __DIR__ . '/vendor/autoload.php';

/**
 * @return \Redis
 * @throws \Exception
 */
function createRedis(): \Redis
{
    $host = '127.0.0.1';
    $port = 6379;

    $redis = new \Redis();
    $redis->connect($host, $port);
    //$redis->auth($password);
    $redis->ping();

    return $redis;
}

/**
 * Require all of the PHP files in a directory
 * @param string $directory
 */
function requireDirectory(string $directory)
{
    $files = glob($directory . '/*.php');

    foreach ($files as $file) {
        require($file);
    }
}

requireDirectory(__DIR__ . '/libImagickContained');

set_time_limit(30);

CLIFunction::setupErrorHandlers();

$injector = new \Auryn\Injector();
$injector->share($injector);
$injector->delegate(\Redis::class, 'ImagickContained\createRedis');

try {
    $injector->execute('ImagickContained\ImageProcessor::run');
}
catch (\Exception $e) {
    fwrite(STDERR, "time: " . date('Y_m_d_H_i_s') . " ");
    fwrite(STDERR, $e->getMessage());
    fwrite(STDERR, "Stacktrace:\n");
    fwrite(STDERR, $e->getTraceAsString() . "\n");

    exit(-1);
}
