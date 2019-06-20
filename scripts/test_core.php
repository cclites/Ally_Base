#!/usr/bin/env php
<?php
/*
 * A script to quickly test for syntax errors and test failures amongst the core functionality of the system
 * php scripts/test_core.php
 */

function base_path($path) {
    return __DIR__ . '/../' . $path;
}

require base_path('/vendor/autoload.php');

class_alias('Symfony\Component\Process\Process', 'Process');

$appDir = base_path('app');
$packageDir = base_path('packages');
$vendorDir = base_path('vendor');

$cmd = sprintf($vendorDir.'/bin/phplint',
    escapeshellarg($appDir), escapeshellarg($packageDir));

$recursiveLint = new Process($cmd);
$recursiveLint->run();
echo($recursiveLint->getOutput() . "\n");
if (!$recursiveLint->isSuccessful()) {
    echo($recursiveLint->getErrorOutput() . "\n");
    exit(1);
}

$cmd = sprintf('php %s ide-helper:models -N -F .test_core_models.php',
    escapeshellarg(base_path('artisan')));

$ideHelper = new Process($cmd);
$ideHelper->run();
if (!$ideHelper->isSuccessful()) {
    echo($ideHelper->getOutput() . "\n");
    echo($ideHelper->getErrorOutput() . "\n");
    exit(4);
}

echo "\xE2\x9C\x94\n";