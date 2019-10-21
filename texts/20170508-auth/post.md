slug: authorization-and-authentication-in-clean-architecture
date: May 8, 2017 18:07
# Authorization and authentication in clean architecture
When I follow the clean architecture idea, I isolate concerns in layers. Having 3 basic layers in mind: application, infrastructure, and domain, where should I put my authentication and authorization logic?

## Authentication
Authentication is a way to get to know who the user is. 
Each IO channel will have different ways to do that:

* normal web channel will probably have the session cookie with `user_id` in it.
* API web channel will probably have a header with access_token. And we can parse it and detect the author of the request.
* console input is trickier. Probably we will have to explicitly set the author of the request when running a command: `command --author_id=<ID>`.
* queued command (job) will also have some `user_id` data in it, which will set the caller id.

While each channel has different ways of detecting the user, they all reside in an application layer. It means that domain logic does not know about how the user interacts with the system, application layer does.

In the end of the day, you will probably have some `AuthenticationServiceInterface`  which lives in the `App/` namespace and can be injected in other neighbor classes. The implementation, as follows, will be chosen by IO channel user uses.

## Authorization
Authorization is much more intriguing. This process will answer the question: "Is the authenticated user allowed to perform this action?". 

In general, proper placing of authorization is not so trivial. Authorization rules will probably need access to domain implementation since they need to know details in order to provide a fine-grained access control. Having said that, to grant an access one may also need to have access to application layer's details - what channel is used, what environment is set, all those things are out of problem domain but still can affect authorization decisions in a way.

What holds true is that authorization is part of your domain, but sometimes it can also cover some app's layer details.

I would definitely put the authorization class in the Domain layer. Take command for example. In my architecture, a command is a class with data in it, it also has a corresponding handler and special authorizer.

Usual command's classes:

* Command class (DTO with user's intention)
* Command handler class (business logic which executes the domain actions);
* Authorizer class (a class which checks that given user is allowed to execute that command).

Example of how it looks in a real case: [source code](https://github.com/lezhnev74/ema/tree/master/src/Domain/Note/Commands/ModifyNote)

## References:
* [Security in Domain-Driven Design by Michiel Uithol](https://www.utwente.nl/ewi/trese/graduation_projects/2008/Uithol.pdf)
* [Access Control in Domain Driven Design](http://stackoverflow.com/questions/23464697/access-control-in-domain-driven-design)
* [Authentication and Authorization in DDD by José Luis Martínez de la](https://medium.com/@martinezdelariva/authentication-and-authorization-in-ddd-671f7a5596ac)
* [Don’t Do Role-Based Authorization Checks; Do Activity-Based Checks by Derick Bailey](https://lostechies.com/derickbailey/2011/05/24/dont-do-role-based-authorization-checks-do-activity-based-checks/)