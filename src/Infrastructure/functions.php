<?php

use Psr\Log\LoggerInterface;

function base_path() : string
{
    return realpath(
        __DIR__ . DIRECTORY_SEPARATOR
        . '..' . DIRECTORY_SEPARATOR
        . '..' . DIRECTORY_SEPARATOR
    );
}

function container() : \Psr\Container\ContainerInterface
{
    $builder = new \DI\ContainerBuilder();
    $builder->useAnnotations(false);
    $builder->useAutowiring(true);
    if (env('APP_ENV') !== 'local') {
        $builder->enableCompilation(base_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'tmp');
    }
    $builder->addDefinitions(base_path() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Infrastructure' . DIRECTORY_SEPARATOR . 'dependencies.php');
    return $builder->build();
}

/**
 * Gets the value of an environment variable.
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env(string $key, $default = null)
{
    static $variables;

    $dotenv = Dotenv\Dotenv::create(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
    $dotenv->load();

    $value = getenv($key);
    if ($value === false) {
        return $default;
    }

    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'empty':
        case '(empty)':
            return '';
        case 'null':
        case '(null)':
            return;
        default:
            return $value;
    }

}

function config(string $key)
{
    $config = container()->get(PHLAK\Config\Config::class);
    return $config->get($key);
}

/**
 * THis function will give me relative path between two given absolute paths
 * @see https://www.php.net/manual/en/function.realpath.php#105876
 */
function relativePath($from, $to, $ps = DIRECTORY_SEPARATOR)
{
    $arFrom = explode($ps, rtrim($from, $ps));
    $arTo = explode($ps, rtrim($to, $ps));
    while (count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0])) {
        array_shift($arFrom);
        array_shift($arTo);
    }
    return str_pad("", count($arFrom) * 3, '..' . $ps) . implode($ps, $arTo);
}

function logMessage(string $message, array $context = []) : void
{
    container()->get(LoggerInterface::class)->info($message, $context);
}