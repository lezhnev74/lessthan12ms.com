<?php
declare(strict_types=1);


namespace Tests\Infrastructure\HTTP;


use Nyholm\Psr7\ServerRequest;
use Slim\App;
use Tests\Test;

class HTTPTest extends Test
{
    public function testItReturns404ForNonFoundPages(): void
    {
        $request = new ServerRequest('GET', '/unknown/page');
        $app = container()->get(App::class);
        $response = $app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());

        $response->getBody()->rewind();
        $body = $response->getBody()->getContents();
        $this->assertStringContainsString(sprintf('<title>Page Not Found</title>'), $body);
    }

    public function testItReturnsPostPage(): void
    {
        // 2. Send HTTP request
        $request = new ServerRequest('GET', '/post-title.html');
        $app = container()->get(App::class);
        $response = $app->handle($request);

        // 3. Assert contents
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html; charset=utf-8', $response->getHeader('Content-Type')[0]);
        $this->assertNotNull($response->getHeader('Content-Length'));

        $response->getBody()->rewind();
        $body = $response->getBody()->getContents();
        $this->assertStringContainsString(sprintf('<title>What is DDD?</title>'), $body);
        $this->assertTrue((bool)preg_match('#<h1[^>]*>.*What is DDD\?</h1>#', $body));
    }

}