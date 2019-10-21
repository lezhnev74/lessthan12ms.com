<?php
declare(strict_types=1);

namespace Textsite\Domain;

use Carbon\CarbonImmutable;

final class MarkdownPost
{
    private string $title;
    private string $slug;
    private CarbonImmutable $publishDate;
    private string $text;

    public function __construct(string $title, string $slug, CarbonImmutable $publishDate, string $text)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->publishDate = $publishDate;
        $this->text = $text;
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

    public function text(): string
    {
        return $this->text;
    }


}