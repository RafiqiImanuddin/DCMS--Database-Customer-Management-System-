<?php

use Illuminate\Http\Request;

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

$app->register(\Illuminate\View\ViewServiceProvider::class);

$app->handleRequest(Request::capture());
