<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\Parser;

use Carbon\CarbonImmutable;
use Textsite\Domain\MarkdownPost;
use Textsite\Domain\Problems\IncorrectPostStructure;
use Textsite\Domain\Problems\PostMetaStructureIncorrect;

/**
 * This service will parse post's text and detect metadata and the body of the post itself
 */
final class MarkdownParser
{
    /**
     * @throws IncorrectPostStructure
     */
    public function parseText(string $text): MarkdownPost
    {
        // 1. Locate where markdown text starts
        // The start is the first "#" found in the first character of the line
        $pos = strpos($text, "\n#");
        if ($pos === false) {
            throw new IncorrectPostStructure('Unable to detect start of the body');
        }

        $meta = substr($text, 0, $pos + 1);
        $body = substr($text, $pos + 1);

        $title = $this->getTitleInBody($body);
        $slug = $this->parseMeta($meta, 'slug');
        $date = $this->parseMeta($meta, 'date');

        try {
            $unlisted = $this->parseMeta($meta, 'unlisted') === 'yes';
        } catch (PostMetaStructureIncorrect $e) {
            $unlisted = false;
        }
        try {
            $page = $this->parseMeta($meta, 'page') === 'yes';
        } catch (PostMetaStructureIncorrect $e) {
            $page = false;
        }

        return new MarkdownPost(
            $title,
            $slug,
            CarbonImmutable::parse($date),
            $body,
            $unlisted,
            $page
        );
    }

    private function parseMeta(string $meta, string $string): string
    {
        if (!preg_match('#^\s*-\s*' . $string . ':(.*)$#m',
                $meta,
                $p) || !trim($p[1])) {
            throw new PostMetaStructureIncorrect(sprintf('Unable to find meta for "%s"',
                $string));
        }
        return trim($p[1]);
    }

    private function getTitleInBody(string $body): string
    {
        if (!preg_match('#^\#([^\n$]+)#', $body, $p)) {
            throw new IncorrectPostStructure('Unable to detect title in the body');
        }
        return trim($p[1]);
    }
}