<?php
declare(strict_types=1);


namespace Tests;


use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected string $webPath;
    protected string $postsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webPath = base_path() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'stub' . DIRECTORY_SEPARATOR . 'web';
        $this->postsPath = base_path() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'stub' . DIRECTORY_SEPARATOR . 'text';

        putenv(sprintf('TEXTS_PATH=%s', $this->postsPath));
        putenv(sprintf('WEB_PATH=%s', $this->webPath));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        shell_exec('rm -rf ' . escapeshellarg($this->webPath) . DIRECTORY_SEPARATOR . '*');
    }
}