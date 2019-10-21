<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Symlinker;

use Nyholm\Psr7\ServerRequest;
use Slim\App;
use Tests\Test;

class SymlinkerTest extends Test
{
    public function testItUpdatesLocalLinksToSymlinks(): void
    {
        // 1. Send HTTP request
        $request = new ServerRequest('GET', '/post-title-2.html');
        $app = container()->get(App::class);
        $response = $app->handle($request);

        // 2. Assert contents
        $response->getBody()->rewind();
        $body = $response->getBody()->getContents();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringNotContainsString('./image.png', $body);
    }
}
