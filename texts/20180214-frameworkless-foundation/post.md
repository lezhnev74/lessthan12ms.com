- slug:frameworkless-foundation-of-your-php-application
- date:Feb 14, 2018 18:36
# Frameworkless foundation of your PHP application
## TL; DR;
This post is about making a PHP from scratch with no framework. It explains how it is usually done by pulling few dependencies with composer and gluing them together in a solid foundation to build upon.

The demo repository is available here: [https://github.com/lezhnev74/php-foundation](https://github.com/lezhnev74/php-foundation).

(**UPDATE**: There was another challenge to build an app with no framework. [Take a look](https://github.com/lezhnev74/PHP-test-app).)

## Introduction
Occasionally I find myself halfway through the project implementing a feature that requires a very specific feature that chosen framework doesn't offer. And I have to hack the framework to implement what I need. The more advanced domains I work within the more often I have to hack and mess with a framework (by the way, I tend to work with Laravel mostly).

One day I realized that I want more freedom implementing clean architecture and maintaining the control over how things are designed. This is not about selfish desire to control every little detail, but rather the desire to keep things clean, decoupled and easily maintainable.

I love Laravel framework, I love that there is always a package for everything. These little-opinionated decisions here and there sometimes drive me crazy. For example, this Eloquent builder class that has [`paginate()`](https://github.com/laravel/framework/blob/5.6/src/Illuminate/Database/Eloquent/Builder.php#L713) method which depends on HTTP layer. This just does not feel right to me. And there is many more. When you quickly hack through the small project this is fine, but for bigger codebases with many specific cases, I'd like to keep things separated and controlled.

To fulfill my idea to gain more control I decided to follow the vision of no framework (which is always opinionated), instead, I need a bunch of quality libraries glued together. So I started to think through the basic things my PHP application requires in order to run.

## PHP Standard Recommendations (PSRs)
It is important to mention the PSRs. Those are recommendations given by [PHP interoperability group](https://www.php-fig.org/) on how to design applications, starting from how to include files, coding styles and a bunch of interfaces to rely on. Those are a product of many people working in the industry and they declared some best practices about PHP development.

I do respect the PSRs and use them in my apps.

## Foundation
I am not the first who went through this and there are packages to bootstrap a new project. For example:
- [Phoundation](https://github.com/nikolaposa/phoundation) by Nikola Poša 
- [Micro](https://github.com/mzdr/micro) by Sebastian Prein
- [Frameworkless](https://github.com/mmeyer724/Frameworkless) - It's technically still a framework 
**I highly suggest you inspect the sources codes of both.**

So let's review what makes a foundation for an app. From my perspective there are few basic things to cover:

**Bare minimum:**

* Configuration manager (including ENV configuration)
* Dependency Injection Container
* Error handler
* Logging

**But in real life an app needs more stuff:**

- HTTP Channel (handle web requests, routes, middleware, sessions, cookies, controllers)
- Console channel (to run manual and CRON commands)
- Message bus (send messages between layers, including events, queries, and commands)
- Templates (for HTML rendering - both for browser and emails)  
- Queues (to perform async execution)
- Database integration (repositories, cache)
- Mail Sending


## Bare minimum
For now let's focus on the essential parts of a PHP app that are vital: config, DI and error handling (including logging).

### App's configuration
We need to be able to configure the app's behavior depending on the environment and current requirements. Configuration is something complimentary to the codebase. It is a light way of changing code's behavior depending on the environment.

There can be many configuration sources, we can use plain PHP files, INI, YML, XML, or JSON format. I prefer to use PHP arrays to set configurable variables, I will stick to it. Also [it is recommended](https://www.12factor.net/config) for security reasons to use ENV variables as a way to configure the app (so no sensitive credentials are committed to the repo as part of the code).

Let's find some libraries for configuration management:
- [phpdotenv](https://github.com/vlucas/phpdotenv) - library to load environment variables. As it states in the README file: 
  > You should never store sensitive credentials in your code. [...] Anything that is likely to change between deployment environments – such as database credentials or credentials for 3rd party services – should be extracted from the code into environment variables.

- [Gestalt](https://github.com/samrap/gestalt) - PHP package for managing your configuration values.
- [Symfony/config](https://github.com/symfony/config) - a package from Symfony framework.
- [Zend/Config](https://docs.zendframework.com/zend-config/intro/) - a package from Zend framework supports INI, JSON, YAML, and XML files.
- [Dummy config loader](https://github.com/lezhnev74/dummy-config-loader) - as simple as it can be to load values from files.
- and there are more...

I believe Gestalt fits my app just perfectly. I will stick to it during this tutorial. **Combining it with `phpdotenv` package gives me all I need to build easily and safely configurable application.**
Worth mentioning that loading a config is something very simple, that [Phoundation](https://github.com/nikolaposa/phoundation) just offers a homegrown class for that.

### Dependency Injection Container
Let's start with a purpose. [DI](https://en.wikipedia.org/wiki/Dependency_injection) container is used to decouple configuration(making) of objects from using them. It is fortunate that we have a generic PSR interface for the container - [PSR-11](https://www.php-fig.org/psr/psr-11/). By using this interface within the app, it is easy to switch the actual container implementation with no changes in business logic at all.

Let's search for available implementations:
- [Pimple from Symfony](https://pimple.symfony.com/) - a simple PHP Dependency Injection Container
- [Aura.DI](https://github.com/auraphp/Aura.Di) - Dependency Injection System
- [PHP-DI](https://github.com/PHP-DI/PHP-DI) - The dependency injection container for humans
- [Thephpleague container](https://github.com/thephpleague/container) - Small but powerful dependency injection container from [the phpleague](https://github.com/thephpleague).

I've had a pleasant experience with PHP-DI container so I should stick to it here.

### Error handling
When app performs unexpectedly it may raise an Exception or an Error and with no special treatment that would end up as a generic error page. I want to have total control over what app's user sees when such error happens. In other words, I want to control the template of error page or format of JSON response. The error handling also includes logging such cases for future review.
If a current app is in `debug` mode then I'd like to see expanded exception page with full trace, while on a production deployment with `debug`=false I just want to see some nice page mentioning the problem with no details at all.

There are two established error handlers to choose from:
- [BooBoo](https://github.com/thephpleague/booboo) - a package from [the phpleague](https://github.com/thephpleague). An error handler for PHP that allows for the execution of handlers and formatters for viewing and managing errors in development and production.
- [Whoops](https://github.com/filp/whoops) - PHP error handling for cool kids.

I like how Whoops works, so it fits the case for now.

### Logging
Since we established a single gateway to handle exceptions, we definitely want to log those cases and review later. So I need a logging package. Again, we have a [PSR-3](https://www.php-fig.org/psr/psr-3/) interface for logging packages. We can review our options of what packages are available, but I always used [monolog](https://github.com/Seldaek/monolog) which is a de-facto the logger for PHP apps. So I will stick to it now.

## ⌨️ Practice time

[The repository is available here](https://github.com/lezhnev74/php-foundation)

What I do now is creating booting procedures and put them to a file `bootstrap/start.php`.

### ⚙️ Configuration part
After pulling in both packages: `vlucas/phpdotenv` and `samrap/gestalt` I import ENV variables, and then load config files. I used to having a global function `config()` so I implemented it. Any logic can access configuration values by calling it:
```php
// from bootstrap/start.php

// 1.1. ENV loader - env variables automatically loaded and object is no more required
(new Dotenv\Dotenv(__DIR__ . "/../"))->overload();
// 1.2. Configuration loader available as a global config function (just for convenience)
function config(string $var, mixed $default = null)
{
    static $config = null;
    if (!$config) {
        $config = \Gestalt\Configuration::load(new \Gestalt\Loaders\PhpDirectoryLoader(base_path('config')));
    }

    return $config->get($var, $default);
}
```

### 💉 Dependency Injection Container
Next thing I want to setup is DI container. I pull my chose package `php-di/php-di` and configure it. Like the `config()` global function, I use global `container()` function to access DI container during runtime. The dependencies map is available in `config/dependencies.php`.

```php
// from bootstrap/start.php

/// Container is also a global function
function container(): \Psr\Container\ContainerInterface
{
    static $container = null;
    if (!$container) {
        $builder = new \DI\ContainerBuilder();
        // Disable caching on production
        if (!config('app.debug')) {
            $builder->setDefinitionCache(new \Doctrine\Common\Cache\FilesystemCache(storage_path('cache')));
            $builder->writeProxiesToFile(true, storage_path('cache'));
        }
        $builder->addDefinitions(config('dependencies'));
        $container = $builder->build();
    }

    return $container;
}
```

### ⛑️ Single Error handler & 📝 Logging

Now the last part is to pull two packages: `filp/whoops` and `monolog/monolog`. First will catch exceptions and errors, second will log them to a file:

```php
// from bootstrap/start.php

/// Error handler has different formatters for different input channels: console, html or json
$whoops = new \Whoops\Run;
if (Whoops\Util\Misc::isCommandLine()) {
    $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
} else {
    if (Whoops\Util\Misc::isAjaxRequest()) {
        $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
    } else {
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    }
}
// Whenever error is handled, we log it to the text file
$whoops->pushHandler(new \Whoops\Handler\CallbackHandler(function ($exception, $inspector, $run) {
    $logger = container()->get(\Psr\Log\LoggerInterface::class);
    $logger->critical($exception->getMessage(), ['exception' => $exception]);
}));
$whoops->register();
```

## Conclusion
Ok, this post is kind of messy. I hope provided links to repositories and general thoughts will inspire you to try to write your PHP apps with no frameworks, but with libraries instead.

## Refs
- [Modern PHP Without a Framework](https://kevinsmith.io/modern-php-without-a-framework)
- [You don’t need a framework](https://www.brandonsavage.net/you-dont-need-a-framework/) by Brandon Savage
- [A small tutorial to show how to create a PHP application without a framework](https://github.com/PatrickLouys/no-framework-tutorial) by Patrick Louys
- [Modern PHP Without a Framework](https://kevinsmith.io/modern-php-without-a-framework) by Kevin Smith
- [12 factors](https://www.12factor.net/) - in the modern era, software is commonly delivered as a service: called web apps, or software-as-a-service. The twelve-factor app is a methodology for building software-as-a-service apps.
- [YOUTUBE, Hexagonal Architecture - Message-Oriented Software Design](https://www.youtube.com/watch?v=K1EJBmwg9EQ&t=2s) by Mathias Noback
- [Think frameworkless](https://lessthan12ms.com/think-frameworkless/)
- Lists of packages to use:
  - [http://thephpleague.com/](thephpleague.com)
  - [https://github.com/ziadoz/awesome-php](github.com/ziadoz/awesome-php)
  - [https://github.com/lezhnev74/blank](github.com/lezhnev74/blank)
  
