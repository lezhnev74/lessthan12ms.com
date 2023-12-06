<?php
declare(strict_types=1);

namespace Tests\Infrastructure\HTMLRenderer;

use Carbon\CarbonImmutable;
use Monolog\Test\TestCase;
use PHPHtmlParser\Dom;
use Textsite\Domain\MarkdownPost;
use Textsite\Infrastructure\HTMLRenderer\HTMLRenderer;

class HTMLRendererTest extends TestCase
{
    public function testItReplacesStandaloneURLsWithLinks(): void
    {
        $this->markTestSkipped('it is simpler to fix it manually in every text as it breaks twitter snippets as one example');

        $text = <<<MD
# Title
link appears https://example.org/2.html?a=10 in the text.
### Sub sub title
Or on a separate line:
https://lessthan12ms.com/a.html
but this one must not be detected [URL title](https://example.org/none.html)

MD;
        $post = new MarkdownPost('Title', 'slug', CarbonImmutable::now(), $text, false, false);
        $renderer = container()->get(HTMLRenderer::class);
        $html = $renderer->render($post);

        $expectedStandaloneUrls = [
            'https://example.org/2.html?a=10',
            'https://lessthan12ms.com/a.html',
        ];
        foreach ($expectedStandaloneUrls as $e) {
            $this->assertStringContainsString('<a href="' . $e . '">' . $e . '</a>', $html);
        }

        $this->assertStringNotContainsString('<a href="https://example.org/none.html">https://example.org/none.html</a>', $html);
    }

    public function testItAddsAnchorsToEachHNode(): void
    {
        $text = <<<MD
# Title
text
## Sub title
text
### Sub sub title
MD;
        $post = new MarkdownPost('Title', 'slug', CarbonImmutable::now(), $text, false, false);
        $renderer = container()->get(HTMLRenderer::class);
        $html = $renderer->render($post);

        // Assert 3 anchors added
        $dom = new Dom();
        $dom->load($html);
        $anchors = $dom->find('a.anchor');
        $this->assertEquals(3, $anchors->count());
    }

    public function testItAddsTableOfContents(): void
    {
        $text = <<<MD
# Title
text
## Sub title
text
### Sub sub title
## Sub title 2
#### Sub title 4
MD;
        $post = new MarkdownPost('Title', 'slug', CarbonImmutable::now(), $text, false, false);
        $renderer = container()->get(HTMLRenderer::class);
        $html = $renderer->render($post);

        // Assert ToC
        $dom = new Dom();
        $dom->load($html);
        /** @var Dom\AbstractNode $toc */
        $toc = $dom->find('#toc')[0];
        $this->assertStringContainsString('Sub title', $toc->innerhtml);
        $this->assertStringContainsString('Sub sub title', $toc->innerhtml);
        $this->assertStringContainsString('Sub title 2', $toc->innerhtml);
        $this->assertStringContainsString('Sub title 4', $toc->innerhtml);
    }

    public function testItSkipsTableOfContentsForPages(): void
    {
        $text = <<<MD
# Title
text
## Sub title
text
### Sub sub title
## Sub title 2
#### Sub title 4
MD;
        $post = new MarkdownPost('Title', 'slug', CarbonImmutable::now(), $text, false, true);
        $renderer = container()->get(HTMLRenderer::class);
        $html = $renderer->render($post);

        // Assert ToC
        $dom = new Dom();
        $dom->load($html);
        /** @var Dom\AbstractNode $toc */
        $tocs = $dom->find('#toc');
        $this->assertEmpty($tocs);
    }
}
