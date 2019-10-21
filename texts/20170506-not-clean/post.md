date: May 6, 2017 10:16
slug: when-clean-architecture-is-not-worth-it
# When clean architecture is not worth it
Clean (onion) architecture historically [came to solve](http://blog.thedigitalgroup.com/chetanv/2015/07/06/understanding-onion-architecture/) coupling problems which lead to a very time-consuming change of the software. It separates code into layers with strict rules of which layer can access another and in which way. Changes in a given layer will never affect the code in the other layer which leads to the fundamentally better capability to change.

The mentioned problem organically appears in long-term projects with many people involved in. New developers come in, make changes and then other people make changes and then all this starts to rot. On some occasion developer will just realize that he can't change the code accordingly to a new business requirement - it's impossible or hardly doable. This is the result of bad initial design (in my opinion).


## Do we invest time in the clean architecture now or later on?
Solving the coupling problem comes with the cost. And it usually boils down to a time (efforts) taken to produce an app. 

The time is what you pay for a well-designed app. If you tend to save on time and cut the corners then you will get a big ball of mud which (hopefully) works and can be brought to the market with little to no time.  But it has a terrible capability to change.

If you don't expect software to change much in the upcoming years, then you may just embrace high coupling and use well-known frameworks which based on that concept. Take, for example, Laravel framework. This will save you lot of time (read money). 

## When not to bother with clean architecture?
That is a real world. And we want to make informative decisions here. If we know that clean architecture will cost us additional time, then we need to compare it with the RAD (rapid application development).

I don't want to invest money and time in costly development if I am:

* **prototyping** a new product;
    My goal is to make something working, test user feedback and throw it away;
* making **a well-known type** of apps with known life cycle (for example a sports promo-page, a blog);
    Your goal is to make a known app and the scope of its features is clear and defined. 
    
### Prototyping
Usually, this happens when you work alone or with the small team and you can't afford many weeks of development. You need to test your hypothesis as soon as possible until you find a working business model or run out of resources.

This is a pretty valid case to not think about any "smart" architecture styles which will help you in the future. You usually need quick results right now.

### Simple (small) domains
The key idea here is that you don't expect to change it much in the future (which you can't predict actually but you can forecast the change). The project scope is defined, a budget is allocated and a life cycle of the app is predicted.

Possible domains are:

* promo pages;
* blogs;
* corporate websites;
* you name it.

## Closing thoughts 
The answer always sounds like "it depends...". If you hire a freelancer to make you a website, you may not need it to be well-designed internally. Because proper design will cost you money. 

If you are a company which hired a developer (or a team) and building up an internal CRM or something, which according to Conway's law will fit the company's structure. Then you may expect it to change because the market will change and company will change.
So yes, it is a good reason to make a software that has the capability to be modified with less cost, because you know that it will happen.


