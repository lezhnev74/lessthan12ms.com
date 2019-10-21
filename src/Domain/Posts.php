<?php
declare(strict_types=1);

namespace Textsite\Domain;


interface Posts
{
    /**
     * @return  MarkdownPost[]
     */
    public function getAllPostsOrderByDate(): array;

    public function getPost(string $slug): ?MarkdownPost;
}