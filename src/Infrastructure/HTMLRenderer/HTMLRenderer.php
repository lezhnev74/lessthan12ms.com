<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\HTMLRenderer;

use Cocur\Slugify\Slugify;
use League\CommonMark\CommonMarkConverter;
use Textsite\Domain\MarkdownPost;

/**
 * This service converts a MarkdownPost to HTML text
 */
class HTMLRenderer
{
    public function render(MarkdownPost $post): string
    {
        $converter = new CommonMarkConverter([
            'renderer' => [
                'block_separator' => "\n",
                'inner_separator' => "\n",
                'soft_break'      => "<br>\n",
            ],
        ]);
        $html = $converter->convertToHtml($post->text());
        $html = $this->addAnchors($html);
        return $html;
    }

    /**
     * Add HTML <a> anchors to each H-level node
     */
    private function addAnchors(string $html): string
    {
        if (!preg_match_all('#(<h\d>)(([^<]+)</h\d>)#i', $html, $matches, PREG_SET_ORDER)) {
            return $html;
        }
        foreach ($matches as $m) {
            // matches:
            //0 = "<h1>Title</h1>"
            //1 = "<h1>"
            //2 = "Title</h1>"
            //3 = "Title"
            $id = (new Slugify())->slugify($m[3]);
            $anchor = sprintf('<a class="anchor" href="#%s">#</a> ', $id);
            $html = str_replace(
                [$m[0], '>' . $anchor],
                [$m[1] . $anchor . $m[2], sprintf(' id="%s" >', $id) . $anchor],
                $html
            );
        }

        return $html;
    }
}