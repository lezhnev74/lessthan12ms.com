<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\HTTP\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StaticCachedHtmlMiddleware implements MiddlewareInterface
{
    protected string $webRootPath;

    public function __construct(string $webRootPath) { $this->webRootPath = $webRootPath; }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $handler->handle($request);

        if (
            $response->hasHeader('Content-Type') &&
            (strpos($response->getHeader('Content-Type')[0], 'text/html') !== false)
        ) {
            $this->cacheFile($request, $response);
        }

        return $response;
    }

    private function cacheFile(ServerRequestInterface $request, ResponseInterface $response): void
    {
        // 1. Detect a relative path to put the request at
        $path_parts = explode('/', trim($request->getUri()->getPath(), '/'));
        $file_part = array_pop($path_parts);
        $file = ($file_part !== '' ? $file_part : 'index.html');
        if (!preg_match('#\.html$#', $file)) {
            return;
        }
        $relative_path = implode('/', $path_parts);
        $cachePath = $this->webRootPath . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $relative_path;

        // 2. Create a folder
        if (!file_exists($cachePath) && !mkdir($cachePath, 0744, true) && !is_dir($cachePath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $cachePath));
        }

        // 3. Put a file with response HTML
        $response->getBody()->rewind();
        file_put_contents($cachePath . DIRECTORY_SEPARATOR . $file, $response->getBody()->getContents());
    }

}