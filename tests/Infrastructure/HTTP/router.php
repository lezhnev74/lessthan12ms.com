<?php
declare(strict_types=1);

// This is a test router to use with the PHP built-in webserver
// http://www.lornajane.net/posts/2012/php-5-4-built-in-webserver
// ./develop serve routing.php

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$base = base_path() . DIRECTORY_SEPARATOR . 'web';
if (file_exists($base . '/' . $_SERVER['REQUEST_URI'])) {
    return false; // serve the requested resource as-is.
}
include_once $base . DIRECTORY_SEPARATOR . 'index.php';


