<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Mcp\Servers\BasicServer;
use Illuminate\Contracts\Console\Kernel;
use Laravel\Mcp\Server\Contracts\Transport;
use Symfony\Component\HttpFoundation\Response;

$server = new BasicServer(new class implements Transport
{
    public function onReceive(Closure $handler): void {}

    public function send(string $message, ?string $sessionId = null): void {}

    public function run(): Response
    {
        return response('');
    }

    public function sessionId(): ?string
    {
        return null;
    }

    public function stream(Closure $stream): void {}
});

$context = $server->createContext();

echo "Resources:\n";
foreach ($context->resources() as $r) {
    echo '- '.$r->name().' (uri: '.$r->uri().")\n";
}

echo "Resource templates:\n";
foreach ($context->resourceTemplates() as $r) {
    echo '- '.$r->name().' (uriTemplate: '.(string) $r->uri().")\n";
}

echo 'Tools count: '.$context->tools()->count()."\n";
