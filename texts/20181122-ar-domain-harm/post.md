- slug:how-active-record-harms-domain-logic
- date:Nov 22, 2018 12:42

# How Active Record harms domain logic
The most critical part of a system is writing, altering the state. This operation has the most business responsibility.

When you change data, you want to do this in a single place that you control. This might be a command like `UpdateBalance`. This command accepts data and performs a series of validations to enforce business rules. And only then it persists the change.

Nowadays, being pressured by deadlines a developer tend to use frameworks to cut on time. For the data manipulation many developers use [ORMs](https://en.wikipedia.org/wiki/Object-relational_mapping), and [ActiveRecord](https://en.wikipedia.org/wiki/Active_record_pattern)(AR) pattern in many cases. The framework makes it convenient and quick. Now, when we use ActiveRecord we embrace these concepts:

- AR object reflects a table in a database
- AR's attributes are mutable
- AR object contains logic to persist mutated data anytime in any place

These intrinsic properties of ORM make a system less responsible, and less controllable.

1. **One ORM object usually maps to a single table.** While for persisting purposes this is handy, it has no value for a business part. For example, an order model might be a single undividable business concept. It should be represented as a single object. Internally an order may contain a price, customer details, a list of goods. Each of these pieces might be accessed from various storages (tables). Business logic should not know about that, this information is no concern of it. If we use ORM throughout the app for access convenience, we harm the logic. Now suddenly our business part is dependent on how our database is organized. Infrastructure details must never dictate rules to the business code.  There is a business model which is useful for a business case. While the persisting strategy, tables, keys, indexes are just details. We should think about models as business concepts, they must reflect the real business papers, documents, processes and never the database structure.
2. **Mutability.** Any code that has a link to the ORM object can change it. We pass this object to various services, validators, speciations etc. How do we know that the data is still valid and not altered? Do we have to review each class which works with the object? Nah, not practical. Ok, then we can make attributes immutable and only accept the values which pass certain validation upon instantiation. That sounds good. Now the object can be sent to any other services while we know that the data won't be harmed. Let's move on.
3. **Persistability.** Anyone can persist the data any time. Just call `save()` on ORM object and the system is changed. How do you know who and when invokes this? Looking through the whole code? Not practical. Why? Because the valid ORM object might be still checked against various business rules (ie who saves the data, does he has rights, does this data meet certain dynamic criteria?) before persisted. There can be many parties involved:  if we save an order, we need to check a stock, a customer balance, current promotions etc. We could override the `save()` method and inject in it all required external services, checkers etc. But there can be multiple business cases when persisting an order is allowed and each case involves different business rules. Do we inject all of them in the ORM object? It will grow and end up unmaintainable practically. So what we can do is disable a `save()` method altogether. Sounds good.

But what has been left of ORM now? A safe immutable data object, designed to reflect the real world, with built-in input validation and without the ability to save. This is not an ORM object anymore, but rather a POPO (plain old PHP object).

Now you safely throw this data object to any service and stay confident data is unaltered. Perfect!

Now, when to persist the data? Let's talk about use cases. We might have a bunch of them:

- a customer may put an order if the balance is sufficient and the stock is full
- an admin can put an order for a good that is out of stock, why not?
- etc.

Now each case accepts an order data object, applies any business validation and processes on top and eventually persists the data. The actual persisting strategy is a detail, we might use ORM here, or raw SQL queries or whatever. This is an infrastructure detail. It never affects the business rules and use-cases.

```

[USER LOGIC]---(order object)--->[BUSINESS LOGIC]---(order object)--->[INFRASTRUCTURE LOGIC]

```

Now, if we are serious about the system and its state, then we need no ORM. ORM is harmful for the discussed reasons but convenient for the infrastructure part of the architecture.

### Refs

- [Think frameworkless](https://lessthan12ms.com/think-frameworkless/)
- [Clean architecture implemented as a PHP app](https://lessthan12ms.com/clean-architecture-implemented-as-a-php-app/)
- [Clean architecture links](https://lessthan12ms.com/clean-architecture-links/)
- [Marco Pivetta "From Helpers to Middleware"](https://www.youtube.com/watch?v=v1I57-_Rsv0&feature=youtu.be)
