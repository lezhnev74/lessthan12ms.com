date: May 10, 2017 16:12
slug: error-handling-in-php-and-formatting-pretty-error-responses-to-users
# Error handling in PHP and formatting pretty error responses to users
An error handler is developer's best friend. It will take responsibility when normal code execution stops because of the error or a thrown exception.

Historically there were Errors and then Exceptions appeared as an evolution of the errors. Nowadays with 7th version (I don't really pay much attention to older versions of PHP) of PHP, we mostly use Exceptions rather than Errors, but the old legacy code can still produce errors. And we must handle them in our projects as long as necessary. 

## Default error handler
PHP has a default error/exception handler built in which will output a message and additional information with respect to [INI settings](http://php.net/manual/en/errorfunc.configuration.php).

## Set your own Error handler
For a much better configurability, you can set your own error handler. In that case, no logging and outputting will be done automatically, you take full control of what to do with thrown exceptions and errors.

A good idea is to allow PHP [to emit any kind of errors](http://php.net/manual/en/function.error-reporting.php) so you have full control over each one of it:

```php
// Report all PHP errors
error_reporting(-1);
```

Note also that error handler function will return and code execution will continue from the point of error. To avoid that developer should (this is a known practice) convert any error into exception and then handle it as an exception in Exception Handler:

```php
set_error_handler(function ($code, $message) {
    // convert error to ErrorException
    throw new ErrorException($message, $code);
});
```

### Uncatchable errors
There are few errors that won't end up in your error handler. Those errors are of these types: E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR. For example, "max execution time of N seconds exceeded" will not be caught. 
Then how do you catch them?

The only way we have left is registering a function which will be executed right before the ending of any program. It is done like this:

```php
register_shutdown_function(function () {
        // check if the script ended up with an error
    $lastError    = error_get_last(); 
    $fatal_errors = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
    if ($lastError && in_array($lastError['type'], $fatal_errors, true)) {
        // Ok script ended with a problem:
                // let's do something about it
                // handle last error ...
    }
});
```

The shutdown handler works as append-only, so you can append your handler to the default one. It means that if INI setting `display_errors` is set true then you will see the message on the screen because your shutdown function will be executed right after the built-in one. To avoid that you need explicitly turn messaging off:

```php
ini_set('display_errors', 'Off');
```

## Set your own Exception handler
This is your point of handling any errors your system gets. Whether it is an Error or an exception, the code execution will end up in this handler (see the previous paragraph about converting errors to exceptions).

Now when automatic logging feature is off, developer have full responsibility to log exceptions by himself. [Monolog](https://github.com/Seldaek/monolog) is one of the best options here. It has [built in error and exception handlers](http://stackoverflow.com/a/36744961/1637031):

```php
// in your index.php
$log = new Logger('name');

// create writing handler
$writingHandler = new StreamHandler(__DIR__ . "/log.log", Logger::WARNING);
$log->pushHandler($writingHandler);

// inject monolog's error/exception handler
$errorHandler = new ErrorHandler($log);
$errorHandler->registerErrorHandler();
$errorHandler->registerExceptionHandler();
$errorHandler->registerFatalHandler();

// your code goes here
```

Or you can inject it into your handler and just call for logging feature:

```php
set_exception_handler(function (Throwable $exception) {
        // initialize $monolog from DI container or manually
        // then call it
        $monolog->error($e->getMessage(), [
        'trace' => $e->getTraceAsString(),
    ]);
});
```

## Pretty error response for every IO channel
There are many ways a user can interact with your app - through the HTTP channel (here we can have multiple formats - HTML, JSON, XML, etc), through the console or as a queue worker. 
Each channel has different communication style and requires a different format for error handling.
So basically, you will need an error formatter for each channel.

One way of doing this is by registering an individual exception handler for each channel. It is done in the application or infrastructure layer. By this you can set HTML response for web channel, JSON response for API channel, console output for terminal and no output for queue job.

The other way is setting single handler which can detect the environment automatically and adjust response format accordingly. 

It is up to developer how to set it up.

### Phpunit automatic error conversion to exceptions
Worth noting that phpunit has [built-in feature](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html#writing-tests-for-phpunit.errors) which automatically converts any PHP errors into exception for your convinience. 
