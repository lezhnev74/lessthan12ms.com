<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\Storage\Files;


use Textsite\Domain\MarkdownPost;
use Textsite\Domain\Posts;
use Textsite\Domain\Problems\PostFileMissing;
use Textsite\Infrastructure\Parser\MarkdownParser;

final class FilesPosts implements Posts
{
    private string $path;

    public function __construct(string $path) { $this->path = $path; }

    /**
     * @return MarkdownPost[]
     */
    public function getAllPostsOrderByDate(): array
    {
        $posts = $this->getAllPosts();

        usort($posts, function (MarkdownPost $a, MarkdownPost $b) {
            return $a->publishDate()->lte($b->publishDate());
        });

        return $posts;
    }

    public function getPost(string $slug): ?MarkdownPost
    {
        $posts = $this->getAllPosts();
        foreach ($posts as $post) {
            if ($post->slug() === $slug) return $post;
        }
        return null;
    }

    /**
     * Given the post, locate it's folder
     */
    public function findFolder(MarkdownPost $post): ?string
    {
        $existingPosts = $this->getAllPosts();

        foreach ($existingPosts as $dir => $existingPost) {
            if ($post->slug() === $existingPost->slug()) {
                return $dir;
            }
        }

        return null;
    }

    private function buildPostFromFolder(string $folder): MarkdownPost
    {
        $text = $this->getPostText($folder);

        $parser = new MarkdownParser();
        return $parser->parseText($text);
    }

    /**
     * Text are found in the first .md file found
     */
    private function getPostText(string $folder): string
    {
        $mdFiles = array_filter(glob($folder . DIRECTORY_SEPARATOR . '*.md'), 'is_file');
        if (!$mdFiles) throw new PostFileMissing('No md files found in the folder ' . $folder);

        foreach ($mdFiles as $file) {
            if (!is_readable($file)) continue;
            $text = file_get_contents($file);
            if ($text === false) continue;
            return $text;
        };

        throw new PostFileMissing('No readable md files found in the folder ' . $folder);
    }

    /**
     * @return MarkdownPost[]
     */
    private function getAllPosts(): array
    {
        $posts = [];
        $dirs = array_filter(glob($this->path . DIRECTORY_SEPARATOR . '*'), 'is_dir');
        foreach ($dirs as $dir) {
            try {
                $posts[$dir] = $this->buildPostFromFolder($dir);
            } catch (PostFileMissing $e) {
                // skip this one
            }
        }
        return $posts;
    }
}