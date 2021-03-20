<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\HTTP;


use eftec\bladeone\BladeOne;
use Slim\App;
use Textsite\Domain\MarkdownPost;
use Textsite\Domain\Posts;

class BladeRendererFactory
{
    private Posts $posts;
    private App $app;
    private string $env;

    public function __construct(
        Posts $posts,
        App $app,
        string $env
    ) {
        $this->posts = $posts;
        $this->app = $app;
        $this->env = $env;
    }


    public function create(): BladeOne
    {
        $renderer = $this->makeInstance();
        $this->addSharedData($renderer);
        return $renderer;
    }

    private function makeInstance(): BladeOne
    {
        $views = base_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'views'; // to read the templates
        $cache = base_path() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache'; // to compile the result
        $mode = BladeOne::MODE_SLOW;

        if($this->env === 'production') {
            $mode = BladeOne::MODE_FAST;
        }

        return new BladeOne($views, $cache, $mode);
    }

    private function addSharedData(BladeOne $renderer): void
    {
        // 1. Load all posts links
        $routeParser = $this->app->getRouteCollector()->getRouteParser();
        $postLinks = array_map(function (MarkdownPost $post) use ($routeParser) {
            return [
                'title' => $post->title(),
                'url' => $routeParser->urlFor('post', ['slug' => $post->slug()]),
                'date' => $post->publishDate()->format('D, j M Y')
            ];
        }, $this->posts->getAllPostsOrderByDate());
        $renderer->share('postLinks', $postLinks);

        $renderer->share('env', $this->env);
    }
}