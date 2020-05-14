<?php

/**
 * Broadnet CSV text file coding challenge - Main executable
 * @author jecklund
 */

spl_autoload_register(function ($class) {
    include 'classes/' . $class . '.php';
});

$appStartTime = time();

try {
    CsvManip::run();
} catch (Exception $exception) {
    die("Exception occurred: " . $exception);
}

$appEndTime = time();

$totalAppTime = $appEndTime - $appStartTime;
echo 'Execution finished in ' . $totalAppTime . ' seconds' . PHP_EOL;
