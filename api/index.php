<?php

use Illuminate\Http\Request;

$viewCompiledPath = '/tmp/storage/framework/views';
putenv("VIEW_COMPILED_PATH={$viewCompiledPath}");
$_ENV['VIEW_COMPILED_PATH'] = $viewCompiledPath;
$_SERVER['VIEW_COMPILED_PATH'] = $viewCompiledPath;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

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

try {
    $app->handleRequest(Request::capture());
} catch (Throwable $exception) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo $exception::class.': '.$exception->getMessage()."\n".$exception->getTraceAsString();
}
