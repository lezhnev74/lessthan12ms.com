slug: openapi-with-php-documenting-and-testing-api-automatically
date: May 3, 2019 18:01
# OpenAPI with PHP - documenting and testing API automatically
Note: the code mentioned in this post can be found here:
[https://github.com/lezhnev74/openapi-php-demo](https://github.com/lezhnev74/openapi-php-demo).

## OpenAPI introduction
OpenAPI Initiative (OAI) ([official website](https://www.openapis.org/)) is an attempt to create a universal (vendor-neutral) description language for REST APIs. It is a part of the Linux Foundation and a lot of experts and companies are contributing to it.

Currently, there are this much members in the OAI:
![OAI members](./openapi-members.jpg)

There were multiple attempts and initiatives to develop an API description language. Some of those: Swagger (a predecessor of OAI), APIBlueprint, RAML, WSDL, etc. None of these were adopted widely to win the race. OpenAPI looks promising and actively developed so far.

I have good experience using it in my PHP projects.

## Writing my first OpenAPI spec
Let's write a specification for a simple web API. Let it just generate a random password for us. OpenAPI specification is well documented here at [https://swagger.io/docs/specification/about/](https://swagger.io/docs/specification/about/).

For describing the spec we use JSON or it's superset called YAML (you can use JSON inside of it).

I've created a file in the project folder: `openapi/spec.yaml`
```yaml
openapi: 3.0.2
info:
  title: Password generation API
  version: 1.0.0
  contact:
    name: Dmitry Lezhnev
    url: /
    email: lezhnev.work@gmail.com
tags:
  - name: Demo
  - name: Password generation
paths:
  /generate:
    get:
      operationId: getPassword
      tags:
        - Password
      parameters:
        - in: query
          name: length
          description: How long the password should be
          schema:
            type: number
            default: 20

      description: Password generator

      responses:

        200:
          description: |
            It generates a random password
          content:
            application/json:
              schema:
                type: object
                properties:
                  password:
                    type: string
                    example: abcde12345
            text/plain:
              schema:
                type: string
              example: abcde12345

```

This specification describes a single endpoint: `GET /generate` which returns a new password in one of the two formats: `text/plain` or `application/json`. One can control the length of the password by providing a query argument `length`.

Looks quite simple, isn't it?

## Generating pretty documentation from the spec
Now that we have described our API in a yaml file, we can generate pretty documentation out of it. There is a nice tool [redoc-cli](https://github.com/Rebilly/ReDoc/tree/master/cli) written in nodejs which can produce a valid HTML code from the YAML specification.

- Install it like this (globally): `npm install -g redoc-cli`
- To generate an HTML file rune: `redoc-cli bundle openapi/spec.yaml`. An HTML file will be Created in the current folder.

It'll look similar to this:
<a href="https://lessthan12ms.com/wp-content/uploads/2019/05/2019-05-03_17-48-46.jpg"><img src="https://lessthan12ms.com/wp-content/uploads/2019/05/2019-05-03_17-48-46-1024x441.jpg" alt="" width="1024" height="441" class="aligncenter size-large wp-image-1005" /></a>

This is a perfect tool to integrate into your Git flow. Trigger new HTML building on each new commit to the specification file.

## Implement API in PHP
Ok, now that we know what our API should look like, let's implement it in PHP. I think the package [Slim](http://www.slimframework.com/) would be a great fit here.

1. Install it with this command: `composer require slim/slim "^3.12"`
2. Create a new file at `public/index.php` with this contents:
    ```php
    <?php

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    require __DIR__ . '/../vendor/autoload.php';

    $app = new \Slim\App;
    $app->get('/generate', function (Request $request, Response $response, array $args) {

        $length   = $request->getQueryParams()['length'] ?? 20;
        $password = substr(bin2hex(random_bytes($length)), $length);

        $accept = $request->getHeader('Accept')[0];

        switch ($accept) {
            case "text/plain":
                $response = $response->withHeader('Content-Type', 'text/plain');
                $response->getBody()->write($password);
                break;
            case "application/json":
            default:
                $response = $response->withHeader('Content-Type', 'application/json');
                $response->getBody()->write(json_encode(['password' => $password]));

        }


        return $response;
    });
    $app->run();
    ```
3. You can now run the built-in webserver and test it manually: `php -S 0.0.0.0:8081 -t ./public`
 
- run `curl 127.0.0.1:8081/generate` to get JSON response:
```
> curl 127.0.0.1:8081/generate
 {"password":"073f84a10c6c3f1a37ee"}
```

- run `curl --header "Accept: text/plain" 127.0.0.1:8081/generate` to get text response
```
> run `curl --header "Accept: text/plain" 127.0.0.1:8081/generate
073f84a10c6c3f1a37ee
```



## Test PHP code with OpenAPI spec
Now that we have our code in place, let's add tests. Let's test that API works as expected and returns different passwords in different formats. For the sake of testing, let's install `phpunit` package.

1. Install phpunit: `composer require --dev phpunit/phpunit ^7`
2. We'll need a way to create fake PSR-7 requests in order to test the API. Let's pull in this package for this purpose: [nyholm/psr7](https://github.com/Nyholm/psr7)
3. Now create a test file at `tests/APITest.php` like [this](https://github.com/lezhnev74/openapi-php-demo/blob/9fe553370425ae4a7cf8c85a23469d93ad0415b1/tests/APITest.php). 
4. You can run it like this: `vendor/bin/phpunit tests/APITest.php`

Now we have tests in place, we validated that API works as we want it to. But! We did not use our OpenAPI specification in our tests. Let's work on that now.

### `lezhnev74/openapi-psr7-validator`
![](https://github.com/lezhnev74/openapi-psr7-validator/raw/master/image.jpg)

We need somehow to check if responses that generate our API match the specification. We can do this by adding a special middleware to our application. It will validate request and responses against the OpenAPI spec. Note, we only want this to be present during tests. Let's install that middleware, there is a package for that: [lezhnev74/openapi-psr7-validator](https://github.com/lezhnev74/openapi-psr7-validator).
1. Install it: `composer require --dev lezhnev74/openapi-psr7-validator`
2. Now update the tests code like [this](https://github.com/lezhnev74/openapi-php-demo/blob/master/tests/APITest.php).

## Change the spec
Now imagine that our OpenAPI specification changed. Let's remove `text/plain` response type. Tests should've failed now, this is how we protect API to be a mismatch with the docs.

Change the spec back again to make tests green again.

## OpenAPI as documentation and testing foundation
We used OpenAPI spec to create a description of our API and used that for both purposes: building beautiful documentation that our development team can use, as well as testing the API against the specification.