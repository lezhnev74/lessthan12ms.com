<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Storage\Files;

use Tests\Test;
use Textsite\Infrastructure\Storage\Files\FilesPosts;

class FilesPostsTest extends Test
{
    private string $path;

    public function testGetAllPostsOrderByDate(): void
    {
        $repo = new FilesPosts($this->postsPath);
        $posts = $repo->getAllPostsOrderByDate();
        $this->assertCount(2, $posts);
        $this->assertEquals('What is DDD?', $posts[0]->title());
        $this->assertEquals('Usecase Driven Development', $posts[1]->title());
    }

    public function testItReadsPostBySlug(): void
    {
        $repo = new FilesPosts($this->postsPath);
        $post = $repo->getPost('post-title-2');
        $this->assertNotNull($post);
        $this->assertEquals('Usecase Driven Development', $post->title());
    }

    public function testItReturnsNullForNotFOundPost(): void
    {
        $repo = new FilesPosts($this->postsPath);
        $post = $repo->getPost('unknown');
        $this->assertNull($post);
    }
}
