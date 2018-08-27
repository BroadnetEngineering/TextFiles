<?php

/**
 * Broadnet CSV text file coding challenge - Main executable
 */

spl_autoload_register(function ($class) {
    include 'classes/' . $class . '.php';
});

$appStartTime = time();
CsvManip::run();
$appEndTime = time();

$totalAppTime = $appEndTime - $appStartTime;
echo 'Execution finished in ' . $totalAppTime . ' seconds' . PHP_EOL;
