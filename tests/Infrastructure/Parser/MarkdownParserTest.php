<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Parser;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;
use Textsite\Domain\Problems\PostMetaStructureIncorrect;
use Textsite\Infrastructure\Parser\MarkdownParser;

class MarkdownParserTest extends TestCase
{
    /**
     * This data has valid post structure, but misses some important meta keys
     */
    public function invalidMetaProvider(): array
    {
        return [
            [
                <<<POST

# What is DDD Actually?
POST
            ],
            [
                <<<POST


slug: what-is-ddd
# What is DDD Actually?
POST
            ],
            [
                <<<POST



# What is DDD Actually?
POST
            ],
            [
                <<<POST
date: 13 Oct 2019
slug: 
# What is DDD Actually? 
POST
            ],
        ];
    }

    /**
     * @dataProvider invalidMetaProvider
     */
    public function testItThrowsAnExceptionIfMetaMissesImportantInformation($text): void
    {
        $this->expectException(PostMetaStructureIncorrect::class);
        $parser = new MarkdownParser();
        $parser->parseText($text);
    }

    public function testItParsesValidTextCorrectly(): void
    {
        $text = <<<TEXT
date: 10 Oct 2019, 08:00
slug: what-is-ddd
# What is DDD Actually?
Well, there are many schools of **thought**.
TEXT;

        $parser = new MarkdownParser();
        $post = $parser->parseText($text);
        $this->assertEquals('What is DDD Actually?', $post->title());
        $this->assertTrue(CarbonImmutable::parse('10 Oct 2019, 08:00')->eq($post->publishDate()));
        $this->assertEquals('what-is-ddd', $post->slug());
        $this->assertEquals(<<<BODY
# What is DDD Actually?
Well, there are many schools of **thought**.
BODY
            ,
            $post->text()
        );

    }
}
