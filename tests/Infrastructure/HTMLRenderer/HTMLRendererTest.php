<?php
declare(strict_types=1);

namespace Tests\Infrastructure\HTMLRenderer;

use Carbon\CarbonImmutable;
use Monolog\Test\TestCase;
use Textsite\Domain\MarkdownPost;
use Textsite\Infrastructure\HTMLRenderer\HTMLRenderer;

class HTMLRendererTest extends TestCase
{
    public function testItAddsAnchorsToEachHNode(): void
    {
        $text = <<<MD
# Title
text
## Sub title
text
### Sub sub title
MD;
        $post = new MarkdownPost('Title', 'slug', CarbonImmutable::now(), $text);
        $renderer = new HTMLRenderer();
        $html = $renderer->render($post);

        // Assert 3 anchors added
        preg_match_all('#<a class="anchor"#', $html, $matches);
        $this->assertCount(3, $matches[0]);
    }
}
