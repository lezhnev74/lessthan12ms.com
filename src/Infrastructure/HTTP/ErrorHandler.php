<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\HTTP;


use eftec\bladeone\BladeOne;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Throwable;

class ErrorHandler
{
    protected LoggerInterface $log;
    protected BladeOne $viewRenderer;

    public function __construct(LoggerInterface $log, BladeOne $viewRenderer)
    {
        $this->log = $log;
        $this->viewRenderer = $viewRenderer;
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        if ($logErrors) {
            $this->log->error($exception->getMessage(), ['exception' => (string)$exception]);
        }

        $response = new Response(500);

        if ($exception instanceof HttpNotFoundException) {
            $response = new Response(404);
            $response->getBody()->write($this->viewRenderer->run('404'));
        }

        return $response;
    }

}