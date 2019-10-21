<?php

return [
    \Slim\App::class => function (\Psr\Container\ContainerInterface $container) {
        $app = \Slim\Factory\AppFactory::create(null, $container);
        $app->addRoutingMiddleware();
        $app->addMiddleware($container->get(\Textsite\Infrastructure\HTTP\Middleware\StaticCachedHtmlMiddleware::class));
        $app->addMiddleware(new \Textsite\Infrastructure\HTTP\Middleware\MinificationMiddleware());
        $app->add(new \Slim\Middleware\ContentLengthMiddleware());
        $errorMiddleware = $app->addErrorMiddleware(true, true, true);
        $errorMiddleware->setDefaultErrorHandler($container->get(\Textsite\Infrastructure\HTTP\ErrorHandler::class));

        $routeCollector = $app->getRouteCollector();
        $routeCollector->setDefaultInvocationStrategy(new \Slim\Handlers\Strategies\RequestResponseArgs());

        $app->get('/atom.xml', \Textsite\Infrastructure\HTTP\Controller\AtomController::class);
        $app->get('/', \Textsite\Infrastructure\HTTP\Controller\HomeController::class)->setName('home');
        $app->get('/{slug}.html', \Textsite\Infrastructure\HTTP\Controller\PostController::class)->setName('post');
        $app->get('/{oldslug}[/]', \Textsite\Infrastructure\HTTP\Controller\OldPostController::class);

        return $app;
    },
    \PHLAK\Config\Config::class => function (\Psr\Container\ContainerInterface $c) {
        $configData = require base_path() . DIRECTORY_SEPARATOR . 'config.php';
        return new \PHLAK\Config\Config($configData);
    },
    \Psr\Log\LoggerInterface::class => function (\Psr\Container\ContainerInterface $c) {
        $log = new \Monolog\Logger('app');
        $path = base_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'log.log';
        $handler = new \Monolog\Handler\StreamHandler($path);
        $formatter = new Monolog\Formatter\LineFormatter(
            null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%
            null, // Datetime format
            true, // allowInlineLineBreaks option, default false
            true  // ignoreEmptyContextAndExtra option, default false
        );
        $handler->setFormatter($formatter);
        $log->pushHandler($handler);
        return $log;
    },
    \Textsite\Domain\Posts::class => function (\Psr\Container\ContainerInterface $c) {
        $config = $c->get(\PHLAK\Config\Config::class);
        return new \Textsite\Infrastructure\Storage\Files\FilesPosts($config->get('texts_path'));
    },
    \eftec\bladeone\BladeOne::class => DI\factory([
        \Textsite\Infrastructure\HTTP\BladeRendererFactory::class,
        'create'
    ]),
    \Textsite\Infrastructure\HTTP\ErrorHandler::class => \DI\autowire()->lazy(),
    \Textsite\Infrastructure\Symlinker\Symlinker::class => function (\Psr\Container\ContainerInterface $c) {
        return new \Textsite\Infrastructure\Symlinker\Symlinker(
            $c->get(\Textsite\Domain\Posts::class),
            config('webroot_path')
        );
    },
    \Textsite\Infrastructure\HTTP\Middleware\StaticCachedHtmlMiddleware::class => function () {
        return new \Textsite\Infrastructure\HTTP\Middleware\StaticCachedHtmlMiddleware(config('webroot_path'));
    },
];