<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\HTTP\Controller;


use eftec\bladeone\BladeOne;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HomeController
{
    private BladeOne $viewRenderer;

    public function __construct(BladeOne $viewRenderer) { $this->viewRenderer = $viewRenderer; }

    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $html = $this->viewRenderer->run('home', [
            'pageTitle' => 'Dmitriy Lezhnev'
        ]);

        $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        $response = $response->withHeader('Content-Language', 'en');

        $response->getBody()->write($html);
        return $response;
    }
}