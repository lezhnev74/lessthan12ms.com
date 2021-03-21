- date:Apr 8, 2017 22:31
- slug:clean-exceptions-with-logging-and-translation
# Clean exceptions with logging and translation
While PHP is exception-light language (comparing to Java, Python, Ruby etc), I still want to bring all the strictness into it and protect the code execution from unpredicted behaviour.

## Layers and APIs
A maintainable app is:

* divided into layers (usually app layer, infrastructure, and domain);
* each layer acts independently from another using public APIs;
* if an error occurs exception is thrown back to the caller.

Working with public APIs of some other layer you never know what will be the result. The API offers known response type, but there is always a chance of unpredicted results (problems, errors) which end up in catching an exception.

> By knowing what exceptions some code can rise we improve our code to make decisions in real-time. 
[from my old note about safe code](https://lessthan12ms.com/writing-code-which-is-safe-by-design/)

If we know what exception can be thrown back to us then we can adapt our code to handle that particular case. For example, we can tell our user that his username has been already taken by someone else:

```php
try {
    sign_up_with_username($username);
} catch (UsernameTaken $e) {
  // known problem handled well
    return "Sorry, your username's been taken";
}
```

In contrast, if we have no knowledge as to what exceptions to expect then all we can do is this:

```php
try {
    sign_up_with_username($username);
} catch (\Exception $e) {
  // only admin can say what was wrong - general problem handling
    return "Sorry, something went wrong";    
}
```

It's best to know what exceptions to expect. In case that is impossible - handle any exception with the general approach, at least we won't let end user see the exception itself.

## Decorating exceptions
The general rule is to not allow exceptions travel through the app. So if an exception is thrown somewhere - we generally don't want to pass it along, instead, we may want to wrap it in another exception. 

For example:

```php
try {
    sign_up_with_username($username);
} catch (\TakenUsername $e) {
    throw new AppException("Sorry, username is taken", "USERNAME_TAKEN", $e);
}
```

See how I wrapped internal `TakenUsername` exception into top-level `AppException`. Doing this I protect my client from details (which is a good thing, because details must be usually encapsulated/hidden).

Top-level code must only be aware of some general exceptions like `AppException` which has an explicit message, some code which can be shown to tend user. Along with that, it has a previous initial exception which we can and should log before rendering the response.

## Logging exceptions
My normal approach to logging is to set up an exception handler (in the case of Laravel framework I use standard [Handler](https://laravel.com/docs/5.4/errors#the-exception-handler)) where I log exceptions and send it to some external bug tracking. Also, that handler controls the way I output exceptions to the end user (i.e. formatting).

Look at this sample:

```php
// ... Handler.php
public function report(Exception $exception) {
        ...
        \Bugsnag::notifyException($exception);
        \Log::error($e->getMessage());
        ...
}
...
public function render($request, Exception $e) {
    ...
    if ($e instanceof AppException) {
            $error_response_body['error_code']    = $e->getErrorCode();
            $error_response_body['error_message'] = $e->getMessage();            
            return new Response($error_response_body, 422, $headers);
        };
    ...
}
...
```


Now having the handler for exceptions, I can safely throw top-level exceptions from my code and I am sure the user is protected, he only gets an allowed message to see, while source detailed exception goes to logs and to bug tracker. 

That is safe by design.

## Translating messages / Managing translations
I believe the exception thrown to the user must have two parts:

* Readable message, which probably will explain what went wrong (this one can be easily translated automatically);
* Problem code, which should be unique for caused problem (e.g `USERNAME_TAKEN`). This code can be used to lookup for solutions on the web / on the support website.

Having this in mind, I prefer "smart" exceptions which have few parts:

* a code
* a payload section
* default message template (made by developer usually in English)

When an exception is rendered on the screen, the message template can be swapped by a properly translated version, while payload and the code remain the same. 

For example:

<script src="https://gist.github.com/lezhnev74/30355c6a392823f599235305de6f89dc.js"></script>

Not see how this trait can be easily used in practice:

```php
final class NotFound extends Exception
{
    use KnownProblem;
    protected $message_template = "Resource with id :id not found";
}
....

// thow it with
throw new NotFound::withProblem('NOT_FOUND', "", ["id"=>$id]);
```

It is now easy to hook translation service just before rendering this exception, because we have a message code which can be a key for translation dictionary, we have payload which is a source of data for templates and we have a default message template built-in which will allow us to show message even if no translation available.

Nice, huh?

## References/Links

* [Exceptional PHP: Nesting Exceptions In PHP](http://www.brandonsavage.net/exceptional-php-nesting-exceptions-in-php/)
* [Structuring php exceptions](https://www.alainschlesser.com/structuring-php-exceptions/)
* [Exceptional behaviour best practices](http://blog.nikolaposa.in.rs/2016/08/17/exceptional-behavior-best-practices/)
* [Nikola Poša: Journey through "unhappy path" - Dealing with exceptional conditions](https://www.youtube.com/watch?v=1YAGxJVuuws&t=4s)
* [Formatting Exception Messages](http://rosstuck.com/formatting-exception-messages)

I hope all this ideas and thoughts will help you improving your codebases :)