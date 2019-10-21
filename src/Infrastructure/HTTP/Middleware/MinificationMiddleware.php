<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\HTTP\Middleware;


use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use voku\helper\HtmlMin;

class MinificationMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $handler->handle($request);

        if (
            $response->hasHeader('Content-Type') &&
            (strpos($response->getHeader('Content-Type')[0], 'text/html') !== false)
        ) {
            $response->getBody()->rewind();
            $text = $response->getBody()->getContents();

            $htmlMin = new HtmlMin();

            $body = Stream::create(fopen('php://temp','r+'));
            $body->write($htmlMin->minify($text));
            $body->rewind();

            $response = $response->withBody($body);
        }

        return $response;
    }

}