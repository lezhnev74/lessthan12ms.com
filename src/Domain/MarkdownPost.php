<?php
declare(strict_types=1);

namespace Textsite\Domain;

use Carbon\CarbonImmutable;

final class MarkdownPost
{
    private string $title;
    private string $slug;
    private bool $unlisted;
    private bool $page;
    private CarbonImmutable $publishDate;
    private string $text;

    public function __construct(string $title, string $slug, CarbonImmutable $publishDate, string $text, bool $unlisted, bool $page)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->publishDate = $publishDate;
        $this->text = $text;
        $this->unlisted = $unlisted;
        $this->page = $page;
    }

    public function page(): bool {
        return $this->page;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function publishDate(): CarbonImmutable
    {
        return $this->publishDate;
    }

    public function unlisted(): bool
    {
        return $this->unlisted;
    }

    public function text(): string
    {
        return $this->text;
    }
}