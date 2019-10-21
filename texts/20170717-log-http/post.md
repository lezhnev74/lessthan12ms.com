slug: how-to-log-every-http-request-and-response-in-laravel
date: Jul 17, 2017 23:33
# How to log(see, view) every HTTP request and response in Laravel
> F: You API does not work!
> B: Oh my god, you must have sent a wrong request. 
> F: Nah, I did everything right. It is not working!
> B: I wish I could see what request he sent to my API...

Working in the team has its issues. As a backend developer, I want to be sure that my API works as expected. But sometimes mobile app devs or frontend devs report problems and this is why I want to log every request and response my app handled.

## Log HTTP requests and responses in Laravel
The easiest way is to dump every request and response right into the log file
. It can be done with simple middleware like this:

```php
final class LogMiddleware implements MiddlewareInterface
{
    private $logger;

public function __construct(
        Logger $logger,
    ) {
        $this->logger = $logger;
    }
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $this->logger->info('Dump request', [
            'request' => serialize($request),
            'response' => serialize($response),
        ]);
                
        return $response;
    }
}
```

This is the most easiest way. It works perfectly fine. You can open your log file and search for every request by hand. There is a drawback - you need to log into your server, and work with the console which is not very expressive.

## [PSR-7](https://www.php-fig.org/psr/psr-7/) compatible logger
Guzzle offers a handy function [`str()`](https://github.com/guzzle/psr7#function-str) which serializes PSR-7 request and response as text. Like this:
```
POST /api/v1/users HTTP/1.1
host: localhost
user-agent: Symfony/2.X
accept: application/json
accept-language: en-us,en;q=0.5
accept-charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7
content-length: 206
content-type: application/json
authorization: Bearer Tu11PBxFArZKsbHu1Cjr3OvwfbBjlz7B7h1aV6Og

{"email":"user@gmail.com", "password":"newpassword"}



HTTP/1.1 200 OK
cache-control: no-cache
date: Wed, 16 Jan 2019 05:56:12 GMT
content-type: application/json

{"id":2}
```

All you need is to convert Laravel's request or response to PSR-7 compatible.
Just install these packages:
- `symfony/psr-http-message-bridge` - this is a package to convert Symfony's (which Laravel is based on) http messages to psr-7 version
- `http-interop/http-factory-guzzle` - this factory helps in conversion
- `guzzlehttp/psr7` - this is a package with psr-7 implementation (since psr7 is just a set of interfaces)


Conversion sample:
```php
use Http\Factory\Guzzle\ResponseFactory;
use Http\Factory\Guzzle\ServerRequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Factory\Guzzle\UploadedFileFactory;

# 1. Create a PSR7 factory
$factory = new PsrHttpFactory(
                new ServerRequestFactory(),
                new StreamFactory(),
                new UploadedFileFactory(),
                new ResponseFactory()
            );
# 2. Convert (for example) a request model
$psrServerRequest = $factory->createRequest($request);

\Log::debug('HTTP Request', [
	'request' => \GuzzleHttp\Psr7\str($psrServerRequest)
]);
```




## UI to track requests and responses
Another option is to log requests and responses to external service and then use UI to search and analyze recorded data. It is much more enjoyable in day-to-day usage. 
[Apideveloper.io](http://apideveloper.io) is a free service which allows you to log all your request-response data and visually analyze it. FYI, I am a developer of this service.

It looks like that:
![](http://apideveloper.io/resources/screencast_preview.gif)

To use it you need:

* Sign up and get API key
* Install laravel package and paste the key into the config file
* Sing in to Dashboard and see what exactly people send to your app


In my team we use it as a default package along with other [nice packages](https://twitter.com/stauffermatt/status/885666786036899840) out there. 
When you have an option to find any request sent to your App - life gets much easier.

### Update, Jan 2018

<a href="https://lessthan12ms.com/wp-content/uploads/2017/07/2018-01-18_02-49-36.jpg"><img src="https://lessthan12ms.com/wp-content/uploads/2017/07/2018-01-18_02-49-36-300x175.jpg" alt="" width="300" height="175" class="aligncenter size-medium wp-image-788" /></a>
Apideveloper got updated. Fresh UI, new collaboration features are now available to you. 
It will offer more useful services for backend developers in 2018.
