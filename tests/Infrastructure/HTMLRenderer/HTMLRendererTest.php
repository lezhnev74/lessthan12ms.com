<?php
declare(strict_types=1);

namespace Tests\Infrastructure\HTMLRenderer;

use Carbon\CarbonImmutable;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;
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

        $anchors = $this->loadHtml($html)->query('//a[@class="anchor"]');
        $this->assertEquals(3, $anchors->length);
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

        $toc = $this->loadHtml($html)->query('//*[@id="toc"]')->item(0);
        $this->assertNotNull($toc);
        $this->assertStringContainsString('Sub title', $toc->textContent);
        $this->assertStringContainsString('Sub sub title', $toc->textContent);
        $this->assertStringContainsString('Sub title 2', $toc->textContent);
        $this->assertStringContainsString('Sub title 4', $toc->textContent);
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

        $tocs = $this->loadHtml($html)->query('//*[@id="toc"]');
        $this->assertEquals(0, $tocs->length);
    }

    private function loadHtml(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $document->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        return new DOMXPath($document);
    }
}
