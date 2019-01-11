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

$cmd = sprintf('find %s %s -type f -name \'*.php\' -print0 | xargs -0 -n1 -P4 php -l -n | (! grep -v "No syntax errors detected" )',
    escapeshellarg($appDir), escapeshellarg($packageDir));

$recursiveLint = new Process($cmd);
$recursiveLint->run();
if (!$recursiveLint->isSuccessful()) {
    echo($recursiveLint->getOutput() . "\n");
    echo($recursiveLint->getErrorOutput() . "\n");
    exit(1);
}

$phpunitConfig = base_path('phpunit.xml');
$phpunitBin = "$vendorDir/bin/phpunit";
if (!file_exists($phpunitBin)) {
    echo("phpunit binary does not exist.\n");
    exit(3);
}
$phpunit = sprintf("$phpunitBin --configuration %s", escapeshellarg($phpunitConfig));

$phpunitCmds = [
    "$phpunit --testsuite Model",
    "$phpunit --testsuite Bugs",
    "$phpunit --filter '/ClockIn|ClockOut|Telefony|EncryptedData|RateFactory/'", // core feature tests
    "$phpunit --filter '/Payer|ClientRateTest/'", // new billing tests (add invoice generators once done)
];

foreach($phpunitCmds as $cmdline) {
    $phpunitCmd = new Process($cmdline);
    $phpunitCmd->setTimeout(600);
    $phpunitCmd->run();
    if (!$phpunitCmd->isSuccessful()) {
        echo($phpunitCmd->getOutput() . "\n");
        echo($phpunitCmd->getErrorOutput() . "\n");
        exit(2);
    }
}

echo "\xE2\x9C\x94\n";