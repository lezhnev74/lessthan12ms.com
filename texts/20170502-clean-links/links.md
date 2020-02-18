date:May 2, 2017 19:02
slug: clean-architecture-links
# Clean architecture links
In this post I will maintain the list of blog posts. projects and related things which cover the whole Clean Architecture subject (aka onion architecture, hexagonal architecture or "ports and adapters". This list is language/framework agnostic. 

**update 2018**: As of recently, I consider Event Sourcing as a way towards clean architecture, thats why I am including extra links about it.

## Implemented projects
* [Clean Architecture Example (Java): Example of what clean architecture would look like (in Java)](https://github.com/mattia-battiston/clean-architecture-example) + [slides](https://www.slideshare.net/mattiabattiston/real-life-clean-architecture-61242830)
* [Wikimedia Deutschland fundraising software (PHP)](https://www.entropywins.wtf/blog/2016/11/24/implementing-the-clean-architecture/)
* [Ema (php)](https://lessthan12ms.com/clean-architecture-implemented-as-a-php-app/)

## Posts on the web
* [The concept of Clean architecture by Uncle Bob](https://8thlight.com/blog/uncle-bob/2012/08/13/the-clean-architecture.html)
* [Screaming architecture by Uncle Bob](https://8thlight.com/blog/uncle-bob/2011/09/30/Screaming-Architecture.html)
* [Hexagonal architecture by Alex Cockburn](http://alistair.cockburn.us/Hexagonal+architecture)
* [Hexagonal Architecture (@fideloper)](http://fideloper.com/hexagonal-architecture)
* [Cleaning up your codebase with a clean architecture by Barry O Sullivan](https://dev.to/barryosull/cleaning-up-your-codebase-with-a-clean-architecture)
* [Layers, ports & adapters - Part 1, Foreword by M.Noback](https://php-and-symfony.matthiasnoback.nl/2017/07/layers-ports-and-adapters-part-1-introduction/) - nice overview of developer's mind shifting towards "Simple architecture" + [part2](https://php-and-symfony.matthiasnoback.nl/2017/08/layers-ports-and-adapters-part-2-layers/)
* [A github repo with lots of related links](https://github.com/PhpFriendsOfDdd/state-of-the-union) - worth reading and learning
* [DDD, Hexagonal, Onion, Clean, CQRS, … How I put it all together](https://herbertograca.com/2017/11/16/explicit-architecture-01-ddd-hexagonal-onion-clean-cqrs-how-i-put-it-all-together/) - good introduction from seasoned developer whos been applying all these practices in real life
* [Blog posts from M.Noback on Architecture subject](https://matthiasnoback.nl/tags/design/)

## Slides
* [Clean architecture with ddd layering in php by Leonardo Proietti](https://www.slideshare.net/_leopro_/clean-architecture-with-ddd-layering-in-php-35793127)
* [Real life clean architecture by Mattia Battiston](https://www.slideshare.net/mattiabattiston/real-life-clean-architecture-61242830)
* [Living Documentation by M.Noback](https://www.slideshare.net/matthiasnoback/living-documentation-presentation)


## Books
* **[Clean Architecture: A Craftsman's Guide to Software Structure and Design (Robert C. Martin Series)](https://www.amazon.com/Clean-Architecture-Craftsmans-Software-Structure/dp/0134494164/ref=sr_1_1?s=books&ie=UTF8&qid=1493734217&sr=1-1&keywords=clean+architecture)**
* [The Clean Architecture in PHP by K.Wilson](https://leanpub.com/cleanphp)

## Videos
* **[Robert C Martin - Clean Architecture and Design](https://www.youtube.com/watch?v=Nsjsiz2A9mg) - I've been coming back to this video again and again.**
* **[What went wrong with the IT-industry? - James Coplien](https://www.youtube.com/watch?v=gPP7Bleg214)**
* **[DevTernity 2017: Ian Cooper - TDD, Where Did It All Go Wrong](https://www.youtube.com/watch?v=EZ05e7EMOLM)**
* [The Future of Programming by R.Martin](https://www.youtube.com/watch?v=BHnMItX2hEQ)
* [Kill "Microservices" before its too late by Chad Fowler](https://www.youtube.com/watch?v=-UKEPd2ipEk)
* [Gordon Skinner - Hexagonal Architecture in DDD](https://www.youtube.com/watch?v=u6oTg5oRH24)
* [Is TDD dead?](https://www.youtube.com/playlist?list=PLJb2p0qX8R_qSRhs14CiwKuDuzERXSU8m). Clean architecture inspires developer to have testable isolated chunks of code, so TDD ussually complement clean architecture. Worth watching!
* [Alistair in the "Hexagone"](https://www.youtube.com/watch?v=th4AgBcrEHA) - author of hexagonal architecture talks explains why it was invented with examples
* [Matthias Noback - Hexagonal Architecture - Message-Oriented Software Design](https://www.youtube.com/watch?v=K1EJBmwg9EQ) - M.Noback talks about why modern frameworks don't let you decouple from its details and how to write clean apps with heaxgonal architecture in mind.
* [DPC2018: Advanced Laravel: Avoid unified data models - Shawn McCool](https://www.youtube.com/watch?v=jPnhTxRfOVk)
* [Shawn McCool - Laravel.IO, A Use Case Architecture](https://www.youtube.com/watch?v=2_380DKU93U) + [slides](https://www.slideshare.net/ShawnMcCool/laravelio-a-usecase-architecture)
* [prooph/micro and FPP - less is more by Sasha Prolic](https://engineers.sg/video/prooph-micro-and-fpp-less-is-more-phpconf-asia-2018--2889)
* [Explicit Architecture - Herberto Graca](https://www.youtube.com/watch?v=5CVU5rrlHqs)
* [Kevlin Henney - Seven Ineffective Coding Habits of Many Programmers](https://www.youtube.com/watch?v=SUIUZ09mnwM)
* [Andrew Cassell | Domain-driven Design Deconstructed](https://www.youtube.com/watch?v=bgJafJI8mp8)
* [Coding a Better World Together - with Uncle Bob - Day 1](https://www.youtube.com/watch?v=SVRiktFlWxI) + [day 2](https://www.youtube.com/watch?v=qnq9syXUuFE)

## Podcasts
* [Uncle Bob Martin on Clean Software, Craftsperson, Origins of SOLID, DDD, & Software Ethics](https://www.infoq.com/podcasts/uncle-bob-solid-ddd)

## Packages
* [Prooph](http://getprooph.org/) - well designed service bus, event sourcing package. Awesome lib to design a decoupled system.
* [Symfony Messenger](https://github.com/symfony/messenger) - The Messenger component helps application send and receive messages to/from other applications or via message queues.
* [Deptrac](https://github.com/sensiolabs-de/deptrac) - a tool to enforce architectural boundaries (handy!)
