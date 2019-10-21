<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\HTTP\Controller;


use eftec\bladeone\BladeOne;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Textsite\Domain\Posts;
use Textsite\Infrastructure\HTMLRenderer\HTMLRenderer;
use Textsite\Infrastructure\Symlinker\Symlinker;

class PostController
{
    private Posts $posts;
    private BladeOne $viewRenderer;
    private HTMLRenderer $markdownRenderer;
    private Symlinker $linker;
    private App $app;

    public function __construct(
        Posts $posts,
        BladeOne $viewRenderer,
        HTMLRenderer $markdownRenderer,
        Symlinker $linker,
        App $app
    ) {
        $this->posts = $posts;
        $this->viewRenderer = $viewRenderer;
        $this->markdownRenderer = $markdownRenderer;
        $this->linker = $linker;
        $this->app = $app;
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, string $slug)
    {
        // 1. Look up the post by the slug
        $post = $this->posts->getPost($slug);
        if (!$post) {
            throw new HttpNotFoundException($request, sprintf("Post not found by slug '%s'", $slug));
        }

        // 2. Generate HTML for the post
        $postHtml = $this->markdownRenderer->render($post);

        // 2.2 Inject symlinks to local post files
        $postHtml = $this->linker->relinkFiles($postHtml, $post);

        // 3. Render the view with the view model
        $viewModel = [
            'title' => $post->title(),
            'date' => $post->publishDate()->format('D, j M Y'),
            'body' => $postHtml,
            'slug' => $post->slug(),
            'url' => $this->app->getRouteCollector()->getRouteParser()
                ->fullUrlFor($request->getUri(), 'post', ['slug' => $post->slug()])
        ];
        $html = $this->viewRenderer->run('post', $viewModel);

        // 4. Send Response
        $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        $response = $response->withHeader('Content-Language', 'en');
        $response = $response->withHeader('Cache-Control', 'max-age=86400');
        $response = $response->withHeader('Etag', crc32($postHtml));


        $response->getBody()->write($html);
        return $response;
    }
}