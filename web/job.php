<?php

require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

$jobUrlsFile = __DIR__.'/job_urls_lezhnev.txt';
if (!empty($_POST['job_url'])) {
    $url = \Stringy\Stringy::create($_POST['job_url'])->first(300);
    file_put_contents($jobUrlsFile, $url."\n", FILE_APPEND);
}

$back = $_POST['_origin'];
header('Location: '.$back.'?thank-you');
exit;

