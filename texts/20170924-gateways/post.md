slug: request-handler-as-a-gateway-to-your-backend-keep-your-code-clean
date: Sep 24, 2017 12:37
# Request handler as a gateway to your backend – keep your code clean
Inspired by the great [post](https://jenssegers.com/85/goodbye-controllers-hello-request-handlers) from Jens Segers. It makes a lot of sense to me to separate different steps of request handling.

What Jens proposes is to isolate each request handler as a single class which purpose is to handle one particular request, validate input and pass the data further to the app bus. It allows a developer to inject required services in a constructor, write clean tests and keep codebase readable.

## Readability

I see lot of cleanness in having such separation:
```text
Http/
-- RequestHandlers/
---- LogUserIn.php
---- LogUserOut.php
---- RestorePassword.php
---- BookAppointment.php
```

By just reading it another developer will be able to maintain it and change it. 
The same applies to tests folders.

## Separation

For single user intent, there is a single request handler and a single domain command. The purpose of the request handler is to validate input from the user and stop execution right here if something seems to be wrong. If input looks good then request can make up proper value objects and pass them further to a command and domain layer. 

Yes, this cleanness comes with something that is against a speed of development. Each request handler will be located in a single file. Also, there will be a single test file for the request handler. Then there is a command, command handler, test for command handler, possible queries and so on.

## Confidence

I see that problems with maintainability start in controllers. A controller is the first place where developers put their code to. And this is the place where many dirty hacks are applied because it is very easy to write something right there and get a little task done. 

Sometimes it seems to me that such behavior triggered by not having a good system of where to put your code to. I think that developers just need to know exactly where each piece of code should be stored. HTTP input one should handle in Request Handler, changing data should be within a command handler, reading data should be within queries and so on. 

I have such guidance book for my team and I believe something similar exists in your team as well.


## :)
This picture illustrates how it affects your code in a long run:
![](https://lessthan12ms.com/wp-content/uploads/2017/09/DJNOs__WAAAnlXb.jpg)


