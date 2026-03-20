<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Mcp\Resources\BasicResource;
use Illuminate\Contracts\Console\Kernel;
use Laravel\Mcp\Request;

$resource = new BasicResource;
$req = new Request([], null, null, 'basic');
try {
    $resp = $resource->handle($req);
    echo 'OK: '.get_class($resp)."\n";
} catch (Throwable $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
