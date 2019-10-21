date: 19 Oct 2019 
slug: software-development-discipline
# The Discipline Of Software Development
Let's face the truth, modern backend development is ruled by frameworks. But that is not the problem. The problem is
that we did not develop a discipline to use them properly.
 
Frameworks handle a lot of general low-level functions for us, like HTTP routing, session management, database and 
filesystem I/O, safe hashing, rendering HTML pages and more. All those things are low-level concerns. But yet we somehow 
allow these concerns to guide our development.

Remember, when you started your last project, did you start with designing database table schemas? Did you start with
designing HTTP endpoints? Did you start with making templates for pages? I bet you did. 

Why do we start with those things? Because they are easy to do with the help of frameworks and the progress is evident
from day 1. The rest of the system, this obscure business logic, it just emerges somehow as we work on low-level
details. We spread it all over active-record classes, views, HTTP controllers and middleware. Any place would fit, hence
framework is always there to help you do that.

We know what happens after. In months and years of such spreading of business rules everywhere we spend more and more
time to understand and change the system. And if there is something that we can be 100% sure of is that **requirements
change**.

## I Don't Need That, I Am Doing CRUD Pages.
If you work on websites that are purely about CRUD - like content management website(blogs), simple personal website,
dashboards to manipulate some assets, etc. In those apps, the main value of the system is in the UI. You create an
engaging, modern, expensive UI that would please the user. And the rest of the system is legitimately set aside.
Good point. And I believe you wouldn't have problems maintaining such apps.

Let's focus here on another group of systems, systems that involve complex rules and different actors following these
rules. I am talking about SaaS systems which intelligently manage customer's resources, internal corporate websites
that organize and control the working process of the company, monitoring systems of all kinds. 

What do these systems have in common? It is that UI here is not the most important thing, but instead, it is the rules 
and values that the system brings by enforcing the rules.

To make it more practical, here is a couple of examples from my experience:
- [Foodkit](https://foodkit.io) is a SaaS which automates the whole process of
ordering food, through accepting payments, to delivery and marketing intelligence. This service powers one of the
biggest food chains in Asia. And believe me, there is a lot of responsibilities that this service may take. Security is
the top priority, performance is a must nowadays, complex marketing promotions, reward system, multiple payment
gateways. Yeah, all those are here. And good UI as well, but comparing the cost of business rules and UI, it would be
definitely in favor of the former. Not to mention that all those parts constantly evolve by different reasons and
at a different pace.
- [Instaon.io](https://instaon.io) is a SaaS which automates ads management on
Google, Bing and other ad platforms for small business owners on the Internet. This service is about managing value
assets on behalf of customers. It creates ads, it sets bids, it manages to bill, it shows reports and uses machine
learning to optimize all of those for the better ROI for the customer. Believe me, the rules inside of the system is
not about the UI, there is a lot of complex decision making components which control how the money of the customer
converts to new prospects on their websites. Again all of the parts of the system change at a very different pace for
very different reasons. Adding a new ads platform (like facebook) should not affect at all the available payment
integration and the existing ads management workflow.

When I think of those systems, the first things which come to my mind are related to important architectural decisions:
- how to design the payment mechanics so different customers from different countries can seamlessly pay for the
  services 
- How to design the system which would tolerate network latencies, dropouts, and protocol errors when working with
  external service providers
- How to design fail tolerant processes so any failed code would be intelligently retried
- And how to isolate all the parts in a way that changes in one of them would not lead to unexpected outcomes in
  another?

## Address What Matters First (DDD discipline)
So having said all from above, it is time to talk about things that matter most.

If we put away all the details from the table, like relational table structure, HTTP endpoints, caches, logging, you
name it. What do we have left? Intriguing! You should see that only important stuff has been left on the table:
- **terms** (what are we working with, terminology of the business domain)
- **rules** (how information must be processed in the system)
- **actions** (what user can do with the system)
- **events** (how do we know that something has happened in the system)
- **outcomes** (what we expect to give to the user in good or exceptional cases)

And since we removed all the details, for now, those are explained in non-technical language. Reasoning about the
business problems in a non-technical way is super important. Consider a difference between "create a new record in the
`users` table" and "sign up a new user".

**Note**: I intentionally put `terms` to the top of the list. The terminology of the domain is vital. You need to reason
about the work you do in the terminology of the business (at most times, unless you discuss the type of column indexes
you want to add to a table, but you wouldn't discuss this with the client, huh?). Terminology preserves the true meaning
of the things you create. Can you create something you don't understand?

*Usually, developers impose their technical terminology on the business and that is the first step to misunderstanding.*

It is a naive approach that we can convert any business problem to CRUD-ish notation. That idea usually leads
to loosing of meaning and semantics of business processes and problems.

### Example: "Submit a bug report"
Let's discuss a hypothetical system where users can submit bug reports and support staff can address them. Let's quickly
imagine an action that a user can take in the system.

```
Input Data: <user Id>, <bug report text>, <memory dump>
Primary Course:
    User sends a bug report and provides a note and optional memory dump as a standalone file.
    System validates input data
    System publishes the report to the Review Queue and records an event BugReportSubmitted
    System delivers a unique identifier of the report in the system
Exception Course:
    System delivers error message to the user if note is empty or user is not allowed to publish bug reports
```

That is a usecase for the system. We can discuss it and reason about it in a pure business language without going deep into
details. But yet we can highlight all important terms, rules, and outcomes of the usecase. 

### Enforce important part with tests (TDD discipline)
After we have a usecase defined and thought through, the next step would be to enforce it in the code. We apply TDD
discipline to iterate over the usecase and test how it works with different input data. For speed of development, we
mock details like database or filesystem with in-memory alternatives.

After we have a bunch of tests for the usecase, we can work on details like actual database tables, logging, HTTP
endpoints etc. (Frameworks at last!)

**Note**: we work on details last of all. Because these are the least important. Only when we enforced the business rules,
we can work on technical details. This is how things meant to de done in the software development, huh?

## The discipline of software development 

> “One of our difficulties will be the maintenance of appropriate discipline so that we do not lose track of what
we are doing.”

Alan Turing, 1946, [Lecture to the London Mathematical Society (AMT/C/32, p18, §2)](http://www.turingarchive.org/viewer/?id=455&title=18)

I believe we as developers often create things that are easy to create, instead of things that need to be created. We
need to create manageable, maintainable software. We need to collaborate with the business/clients in their language. We
need to hide technical details in plugins for the core business system, instead of adapting business rules to match to
technical details.

Tools I use in my work in the order of importance:
- DDD (to talk and discover the domain problems, terms, and meaning)
- TDD (to enforce discovered rules and outcomes)
- Hexagonal Architecture (to hide low-level details from the top-level domain logic)
- Frameworks and packages (to do the dirty work for me - HTTP routing, hashing, DB and filesystem IO, and more)


## References 
- [Agile Software Development](https://painless.software/agile-software-development)
- [The Future of Programming - .NET Oxford - April 2019](https://www.youtube.com/watch?v=BHnMItX2hEQ&t=1790s)
- [[Maintainable] Victor Rentea: Never Separate The Refactoring From The Deliverable](http://podplayer.net/?id=81926332)
