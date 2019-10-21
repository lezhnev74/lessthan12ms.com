<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\Symlinker;

use Textsite\Domain\MarkdownPost;
use Textsite\Infrastructure\Storage\Files\FilesPosts;

/**
 * This service finds links to local static files in the post
 * and will add symlinks to the webroot,
 * after that it will update the HTML  with proper links related to the webroot
 */
class Symlinker
{
    private FilesPosts $posts;
    private string $webrootPath;

    public function __construct(FilesPosts $posts, string $webrootPath)
    {
        $this->posts = $posts;
        $this->webrootPath = $webrootPath;
    }


    /**
     * @return string HTML with updated links
     */
    public function relinkFiles(string $html, MarkdownPost $post): string
    {
        $postFolder = $this->locateFolder($post);
        $links = $this->findLocalHrefs($html, $postFolder);
        $symlinks = $this->createSymlinks($links, $postFolder);
        $html = $this->replaceLinks($html, $symlinks);
        return $html;
    }

    /**
     * Find the folder of the post
     */
    private function locateFolder(MarkdownPost $post): string
    {
        $folder = $this->posts->findFolder($post);
        if (!$folder) {
            throw new \RuntimeException(sprintf(
                'Post folder not found for %s',
                $post->slug()
            ));
        }
        return realpath($folder);
    }

    /**
     * Checks the HTML to find a-nodes with href pointing to existing local files,
     * the files exist in the post's folder.
     * Return array of fulll paths to static files like (/posts/postA/image.png)
     */
    private function findLocalHrefs(string $html, string $postFolder): array
    {
        // 1. find all relative hrefs
        if (!preg_match_all('#(href|src)="([^"]+)"#', $html, $matches)) {
            return [];
        }
        $links = $matches[2];

        // 2. compare to existing local files
        // 3. filter out those pointing to non existent files
        $links = array_filter($links, function ($link) use ($postFolder) {
            return is_file((string)realpath($postFolder . DIRECTORY_SEPARATOR . $link));
        });

        return array_values($links);
    }

    /**
     * Create symlinks to the files from the web root
     * @return array [link=>relativeWebLink] like ['/posts/postA/image.png'=>'/links/abcdef.png']
     */
    private function createSymlinks(array $links, string $postFolder): array
    {
        $symlinks = [];

        $symlinksDir = $this->webrootPath . DIRECTORY_SEPARATOR . 'linked';
        if (!is_dir($symlinksDir) && !mkdir($symlinksDir, 0744, true) && !is_dir($symlinksDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $symlinksDir));
        }

        foreach ($links as $link) {
            $name = sprintf('%s.%s', crc32($link), pathinfo($link, PATHINFO_EXTENSION));
            $webLink = $this->webrootPath . DIRECTORY_SEPARATOR . 'linked' . DIRECTORY_SEPARATOR . $name;
            $linkFullPath = realpath($postFolder . DIRECTORY_SEPARATOR . $link);
            $webLinkFolder = dirname($webLink);
            $linkRelativePath = relativePath($webLinkFolder, $linkFullPath);

            if (!file_exists($webLink)) {
                `cd $webLinkFolder $webLinkFolder && ln -sr $linkRelativePath $webLink`;
            }
            $symlinks[$link] = DIRECTORY_SEPARATOR . relativePath($this->webrootPath, $webLink);
        }

        return $symlinks;
    }

    private function replaceLinks(string $html, array $symlinks): string
    {
        return str_replace(array_keys($symlinks), $symlinks, $html);
    }
}