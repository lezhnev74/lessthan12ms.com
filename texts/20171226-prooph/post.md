- slug:clean-laravel-app-with-prooph-message-bus
- date:Dec 26, 2017 20:21
# Clean Laravel app with Prooph message bus
## What is Prooph
[Prooph](http://getprooph.org/) is a set of loosely coupled PHP packages that can be composed to a powerful toolbox. It has quite a few projects under the umbrella of the Prooph project on [GitHub](https://github.com/prooph), including message bus, an event store, event sourcing and snapshotting.

I was impressed how good those packages were designed. They are truly decoupled and easily integrated into any other framework or app. While the documentation of the project is still in its early days and cannot be ever compared to Laravel’s docs, I decided to make this tutorial to fill some white spots for you and other developers out there.

Here I will focus on message bus package and how I use it in my Laravel projects. The rest of interesting and nicely designed packages in Prooph is to be discovered some other time.


## Why do I need a message bus in Laravel project

In short, you need it to organize your code cleanly and decouple your I/O logic from your Business Logic. [Extracting Input\Output layer](https://medium.com/the-software-architecture-chronicles/ports-adapters-architecture-d19f2d476eca) from the business logic was one of the most significant shifts in my development practice. This is accompanied by testing each layer in isolation. At the end, I have really well-designed apps that other people (and myself months later) can maintain and change with a little effort.

In order to decouple your input channel (HTTP controllers or Console commands) from your business logic, you need a message bus of some sort. In short, your IO channel fires a message into the void where business logic must catch it and execute the intended action. Then business logic returns a result in case of a query message or just returns nothing in case of a command message. 

Laravel has built-in message bus, which can manage commands and events. Laravel's event bus is okay, I allows one to fire events and add listeners in a very simple manner. As for command/query bus - you can dispatch commands as so-called jobs synchronously or via the queue which is nice. While this command bus exists, it does not quite fit into the clean architecture minded app design.

The [CQRS technique](https://medium.com/the-software-architecture-chronicles/from-cqs-to-cqrs-e5f813988de9) that I use is about having separated Commands and Queries to change the app’s state (write) and to read the state correspondently. Also, each command (or a query) usually consists of three parts:
- Command object (simple plain PHP object with command data)
- Command handler - the class with actual execution logic which uses command’s data
- Command authorizer - the class which has authorization logic.

![An example of query folder](https://lessthan12ms.com/wp-content/uploads/2017/12/1.jpg)

Having the triple together I make my code readable and maintainable. Laravel does not exactly offer things to organize your business logic cleanly, so I tried Prooph components to fill this empty space. And it has fit just perfectly.

Prooph has a great design with pluggable add-ons and decoupled architecture. So implementing code cleanly was not a big deal for me. It has built-in plugins for usual tasks:
- dispatching messages (sync and async)
- firing events and listen to them
- authorizing users before dispatching a message
- there is also a brilliant plugin to authorize a user to access the query’s result after it has been executed. This allows you to make a decision on whether the user is allowed to access the data by evaluating the actual data returned from the query.

More here, Prooph has official integration [package](https://github.com/prooph/laravel-package) for Laravel which brings all the Prooph power to your regular Laravel app in minutes. This is awesome, by the way.

I suggest you follow [this man](https://twitter.com/hgraca), Herberto Graça. I enjoyed how great he explains architecture patterns. Great stuff for you there! 

## How to install Prooph

After you installed a fresh Laravel project, include the package in your Laravel project by running this command in terminal:

```
composer require prooph/laravel-package
```

Add `Prooph\Package\ProophServiceProvider` to your `config/app.php` file as well as facade definitions:

```php

'providers' => [
    //…

    /*
     * Package Service Providers...
     */
    Prooph\Package\ProophServiceProvider::class,

    //…

],

'aliases' => [

    // …
    'CommandBus' => \Prooph\Package\Facades\CommandBus::class,
    'QueryBus' => \Prooph\Package\Facades\QueryBus::class,

],
```

The package offers two config files that we will explicitly copy to our `config/` directory after executing this command:

```
php artisan vendor:publish --provider="Prooph\Package\ProophServiceProvider"
```

This will add two files to your config folder:
- `prooph.php` (here we actually set up how message bus should work)
- `dependencies.php` (here prooph sets up injection container)

## How to configure Prooph

Firstly, we need to set up routing of our messages to corresponding handlers. For example, when we dispatch a message of some kind we want message bus to route it to a specific message handler. This works both for query-like messages as well as for commands.

By default, prooph offers you a way to set up routes manually, explicitly. Go to the `config/prooph.php` file and see the `routes` array for a command bus.

![](https://lessthan12ms.com/wp-content/uploads/2017/12/2.jpg)

You can manually set routes like this:

```php
//…
'service_bus' => [
    'command_bus' => [
        'router' => [
            'routes' => [
                // list of commands with corresponding command handler
                \App\Component\Account\Command\RequestAccount\RequestAccount::class => \App\Component\Account\Command\RequestAccount\RequestAccountHandler::class
            ],
        ],
    ],
//…
```

If you have just a few commands in your project then it is fine. But in my case, I usually have dozens if not hundreds of messages and I prefer to route them implicitly. So when I send a message `RequestAccount` I want it to be routed exactly to the `RequestAccountHandler` class under the same namespace. It is also worth mentioning that commands can be as simple as strings, but I prefer to use classes which makes coding simpler and more durable in my projects.

To enable implicit routing I suggest you install this small [package](https://github.com/lezhnev74/prooph-direct-router). After installation, you can enable it in `prooph.php` file like this:

```php
//...
'service_bus' => [
    'command_bus' => [
        'router' => [
            'routes' => [
                // list of commands with corresponding command handler
            ],
            'type' => \DirectRouter\DirectRouter::class
        ],
    ],
//…
```

Now you need no explicit routing rules, just follow a convention that command(or query) handler must have the exact same name with `Handler` appended


## How to use Command Bus and dispatch commands

You can send a message (or a command) to the command bus easily with this facade:

```php
$command = new RequestAccount(“some@example.org”);
\CommandBus::dispatch($command);
```

Command bus does not return anything back to you. It is beneficial to think about command as if all of them were executed on another server. You send command and it is pushed to the queue where another server executes it somewhen in the future. This thinking will calm you down that command bus does not return a response immediately. 

How do you know if your command executed successfully? This is a very common question and the usual answer is by listening to events. Or in a simpler case if your command is executed synchronously then if `dispatch()` command did not throw an exception then command was executed successfully. 

## How to use Query Bus and dispatch queries

Queries require installing additional [package](https://github.com/reactphp/promise) which brings deferred execution to the project. This is package is a step against async programming in PHP. Looks nice but honestly, I had no chance to actually use it to the fullest.

Installation looks like this:
```
composer require react/promise
```

Now visit your `config/prooph.php` file again and adjust it like this:

```php
//…
'service_bus' => [
    'command_bus' => [
        'router' => [
            'routes' => [
                // list of commands with corresponding command handler
            ],
            ‘type’=>\DirectRouter\DirectRouter::class
        ]
    ],
    'query_bus' => [
        'router' => [
            'routes' => [
                // list of queries with corresponding handlers
            ],
            'type'=>\DirectRouter\DirectRouter::class
        ]
    ],
//…
```

Now your query handlers will catch your queries with no additional configuration. Worth mentioning, that in Prooph documentation query handler is called “query finder”.

When you want to send a query and receive result:

```php
$query = new FindAccountByEmail(“test@example.org”);
$account = \QueryBus::resultFrom($query);
```

## Sample project

I have prepared [a sample project](https://github.com/lezhnev74/demo-prooph-laravel) for you to analyze. It has two use-cases:
- Request a new account with email
- Confirm email and create a new account

Each use case has a business logic and I/O layers. Look at `app/Component` and at `tests` folders. I did not isolate business logic from the infrastructure stuff, but as a first step toward clean codebase, it is definitely enough.

## Conclusion

Prooph package helps you organize your code cleanly and manage your messages across your app. This is an advanced (as I see it) technique which aims at producing clean and maintainable codebase. It allows you to organize code predictably and test your layers independently.

Wish you a pleasant development and effortless maintaining of your apps!
 

P.s. 
I found also this [set](https://robertbasic.com/tags/prooph/) of articles about prooph usage by Robert Basic. 
