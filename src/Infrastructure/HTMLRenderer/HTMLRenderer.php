<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\HTMLRenderer;

use Cocur\Slugify\Slugify;
use League\CommonMark\MarkdownConverterInterface;
use Stringy\Stringy;
use Textsite\Domain\MarkdownPost;

/**
 * This service converts a MarkdownPost to HTML text
 */
class HTMLRenderer
{
    private MarkdownConverterInterface $converter;

    public function __construct(MarkdownConverterInterface $converter)
    {
        $this->converter = $converter;
    }


    public function render(MarkdownPost $post): string
    {
        $html = $post->text();
//        $html = $this->makeStandaloneUrlsClickable($html);
        $html = $this->converter->convertToHtml($html);
        [$html, $anchors] = $this->addAnchors($html);
        if (!$post->page()) {
            $html = $this->addTableContent($html, $anchors);
        }
        return $html;
    }

    /**
     * Add HTML <a> anchors to each H-level node
     */
    private function addAnchors(string $html): array
    {
        if (!preg_match_all('#(<h(\d)>)(([^<]+)</h\d>)#i', $html, $matches, PREG_SET_ORDER)) {
            return [$html, []];
        }

        $anchors = [];
        foreach ($matches as $m) {
            // matches:
            //0 = "<h1>Title</h1>"
            //1 = "<h1>"
            //2 = "1"
            //3 = "Title</h1>"
            //4 = "Title"
            $id = (new Slugify())->slugify($m[4]);
            $anchor = sprintf('<a class="anchor" href="#%s">#</a> ', $id);
            $html = str_replace(
                [$m[0], '>' . $anchor],
                [$m[1] . $anchor . $m[3], sprintf(' id="%s" >', $id) . $anchor],
                $html
            );

            $anchors[] = [
                'slug' => $id,
                'title' => $m[4],
                'level' => $m[2]
            ];
        }

        return [$html, $anchors];
    }

    private function addTableContent(string $html, array $anchors): string
    {
        $toc = '<div id="toc"><p>Table of Contents</p><ul>';

        foreach ($anchors as $i => $a) {
            if (!$i) continue;
            $off = ($a['level'] - 2) * 40;
            $toc .= '<li style="margin-left:' . $off . 'px;">' .
                '<a href="#' . $a['slug'] . '">' .
                $a['title'] .
                '</a></li>';
        }

        $toc .= '</ul></div>';

        return (string)Stringy::create($html)->replace('</h1>', '</h1>' . $toc);
    }

    /**
     * whenever there is a standalone link in the text, it is wrapped in an a-tag.
     */
    private function makeStandaloneUrlsClickable(string $html): string
    {
        $urlPattern = "/https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}\\.[a-zA-Z0-9()]{1,6}\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*)/";
        $html = preg_replace_callback($urlPattern, function (array $match) {
            return sprintf('<a href="%s">%1$s</a>', $match[0]);
        }, $html);

        return $html;
    }
}