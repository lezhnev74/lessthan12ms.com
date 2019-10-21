<?php
declare(strict_types=1);


namespace Textsite\Infrastructure\HTTP\Controller;


use FeedWriter\ATOM;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Textsite\Domain\Posts;

class AtomController
{
    private Posts $posts;
    private App $app;

    public function __construct(Posts $posts, App $app)
    {
        $this->posts = $posts;
        $this->app = $app;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $feed = new ATOM();
        $feed->setTitle('Lessthan12ms.com by Dmitry Lezhnev');
        $feed->setDescription('Blog about php ecosystem and backend development');
        $feed->setDate(new \DateTime());

        foreach ($this->posts->getAllPostsOrderByDate() as $post) {
            $item = $feed->createNewItem();
            $item->setTitle($post->title());
            $item->setDate($post->publishDate());

            $routeParser = $this->app->getRouteCollector()->getRouteParser();
            $item->setLink($routeParser->fullUrlFor($request->getUri(), 'post', ['slug' => $post->slug()]));

            $feed->addItem($item);
        }

        $response = $response->withHeader('Content-Type', 'text/xml; charset=utf-8');
        $response = $response->withHeader('Cache-Control', 'max-age=600');
        $response->getBody()->write($feed->generateFeed());
        return $response;
    }
}