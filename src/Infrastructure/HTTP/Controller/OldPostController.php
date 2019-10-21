<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\HTTP\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

class OldPostController
{
    private App $app;

    public function __construct(App $app) { $this->app = $app; }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, string $slug)
    {
        $routeParser = $this->app->getRouteCollector()->getRouteParser();

        return $response
            ->withHeader('Location', $routeParser->urlFor('post', ['slug' => $slug]))
            ->withStatus(301);
    }
}