slug: think-frameworkless
date: Nov 7, 2018 11:36
# Think "frameworkless"
A great discussion about frameworkless happened in comments under [this post](https://lessthan12ms.com/frameworkless-foundation-of-your-php-application/). Answering Leo here.

I am a big fan of Laravel as well. The framework has solved a lot of problems:

* DB data exchange
* HTTP routing and middlewares
* dependency container
* a queue and more.

I don't have any intention to write anything from scratch and instead I use those features daily to save my time.

## The problem

However, there are certain problems emerge when we blindly use the framework. Let's talk about these problems that I had in my past work. In general, the core problem that I've had was a poor communication between developers who worked on the same code at different times. By poor communication, I mean the difficulty of understanding the codebase. The lack of a discipline makes maintaining difficult. Why?

When we just use the framework's features everywhere it leads us to a highly coupled code. Let's briefly review some Laravel's features:

- Facades are global and can be accessed from anywhere (yes we can [inject interfaces](https://stackoverflow.com/questions/35011364/using-dependancy-injection-over-laravel-facades), but this requires a discipline, who does that? Very few.)
- Eloquent:
    - models are mutable (I have no idea if somebody has a quick hack somewhere which alters the state)
    - it couples the whole codebase with the DB schema (this is very convenient for a short-lived project and very harmful for a bigger one. Why would we want to depend on the table schema throughout the app?)
    - it mixes the concerns for convenience (like a paginator somehow knows about the current page in the URL)
- Various framework's interfaces which you are expected to inherit and literally "marry the framework" for better or worse.

In the end, it all boils down to coupling. Framework conveniently allows you to access anything anytime. It requires a certain discipline to design the logic cleanly. If I had to pick one that would be the Eloquent (or ORM in general). We blindly carry the DB schema everywhere for our convenience.

Developers are under pressure. They usually just use convenient framework's tools to do the job quick. The time and effort to think and design are usually neglected. In the end, we have a codebase with non-trivial intentions of code pieces, mixed concerns, interconnected parts and usual lack of tests. This is a usual case in a brownfield project. How would you approach this sort of projects?

So if you are the only one who maintains the code, then you might be alright. You do remember how you designed the app, how it works and how not to break it. Add a new developer here, add another one a year later and then get back and work on this codebase again. Do you feel confident now?


## The discipline

I am a big fan of a so-called hexagonal architecture. And I found it to be most useful to break the app into at least two layers:

- domain logic
- anything else (let's name it an infrastructure layer).

The first should be never dependent on the second. When I design a use case for purchasing a product I don't want to know about SQL, transactions, rabbitMQ, HTTP and etc. All I care about is certain domain objects, classes, interfaces and behaviour. It takes a huge amount of discipline (and time eventually) to design an app in a decoupled fashion. But this is what we are supposed to do as a profession, right?

Now let's see on the "infrastructure" layer. This is where Laravel is most helpful. It solves all these problems with intercating with a DB, queues, HTTP, authentication, middlewares and everything we love about Laravel. I clearly decouple the framework from my Domain logic.

Please see this wonderful image from H.Graca:
![hexagonal architecture](https://herbertograca.files.wordpress.com/2018/11/020-explicit-architecture-svg.png)

This allows me to:

- focus on domain logic
- understand other people's intentions in code
- change/remove domain logic with enough confidence (since I am sure that this piece of logic has no direct connections with another part of the application. It is decoupled and thus changeable.)
- make simpler tests and run them faster in isolation
- upgrade the PHP version and framework anytime


## I think "frameworkless"

I study the frameworkless not because I hate frameworks, but because I build the discipline to not rely on them when I design my domain logic. I study how to omit DB schemas, HTTP arguments, filesystems, and all these small details when I work on the mission-critical use cases.

This discipline helps me and my teammates and those who will inherit my code later to be able to change the software whenever the business needs it. Effortlessly.


## References:

- [Too Clean?](https://blog.cleancoder.com/uncle-bob/2018/08/13/TooClean.html)
- [DDD, Hexagonal, Onion, Clean, CQRS, … How I put it all together](https://blog.cleancoder.com/uncle-bob/2018/08/13/TooClean.html)
- [The Craftsman's Oath](https://blog.cleancoder.com/uncle-bob/2018/08/13/TooClean.html)
- [Marco Pivetta "From Helpers to Middleware"](https://www.youtube.com/watch?v=v1I57-_Rsv0&feature=youtu.be)
