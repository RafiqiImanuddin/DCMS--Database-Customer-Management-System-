<?php

use Illuminate\Http\Request;

$viewCompiledPath = '/tmp/storage/framework/views';
putenv("VIEW_COMPILED_PATH={$viewCompiledPath}");
$_ENV['VIEW_COMPILED_PATH'] = $viewCompiledPath;
$_SERVER['VIEW_COMPILED_PATH'] = $viewCompiledPath;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$bootstrapPath = '/tmp/bootstrap';
$bootstrapCachePath = $bootstrapPath.'/cache';

if (! is_dir($bootstrapCachePath)) {
    mkdir($bootstrapCachePath, 0777, true);
}

if (! is_dir($bootstrapPath.'/cache')) {
    mkdir($bootstrapPath.'/cache', 0777, true);
}

copy(__DIR__.'/../bootstrap/providers.php', $bootstrapPath.'/providers.php');
$app->useBootstrapPath($bootstrapPath);

$app->useStoragePath('/tmp/storage');

$storageDirectories = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
];

foreach ($storageDirectories as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }
}

$app->handleRequest(Request::capture());
