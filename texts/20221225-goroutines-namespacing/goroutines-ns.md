- date: 25 Dec 2022, 12:00
- slug: goroutines-request-isolation

# Namespaces For Goroutines

The problem is simple. In a stateless app, goroutines handle requests independently and must operate on request-scoped
data. This can be seen as namespacing or request-level isolation of goroutines. However, goroutines do not have local
state and there is no way to force them to only see "their" request data. Further, one goroutine can spawn new ones and
all of them must inherit the same request-level scope.

Namespacing is handy in many cases, to name a few:

- to profile certain work branches in a running app (ie how much resource a branch consumes)
- to scope certain resources to goroutines (ie select a certain DB connection which started a transaction)

## Current State

To solve this problem `context` package was introduced. It is a piece of data that goes through the whole execution
branch through layers and serves as a grouping factor (a namespace) for one or many goroutines. The need for a such
concept is obvious as there is no other way to group goroutines in a logical unit of work. Explicit grouping fits nicely
with the Go philosophy of not doing any magic (implicit things).

The problem has been seen in other languages as well. For example, in Java, they
introduced [local storage for threads (LTS)](https://www.baeldung.com/java-threadlocal). If every thread has local
storage then it is easy to scope resources to it as no other thread can access this storage. In Go, however, this is not
possible.

There is [a proposal to add the same feature to Go (GLS)](https://github.com/golang/go/issues/21355). The argument
against GLS is that it is an implicit concept that increases the complexity of a program. While passing around something
like `ctx` is visible and serves the same purpose. There is a strong correlation with error propagation in Go. We drag
errors everywhere (making them part of many function signatures), and Go went away from exceptions for a similar reason of
explicitness.

Some ideas from the discussion include having a new keyword for goroutine local variables like `let x int` or `local
(x int)`. But eventually, the discussion concluded with
this `Being explicit in the code tends to be clearer than implicit. We aren't going to make this change. Closing.`

## Manual Namespacing

Truly there is just one way of scoping - parameters that we pass through the execution branch (from function to
function). `Context` is the only natural way to do so. There is a good in-depth exploration of this
option [here](https://blog.merovius.de/posts/2017-08-14-why-context-value-matters-and-how-to-improve-it/) and later
[here](https://blog.merovius.de/posts/2020-07-20-parametric-context/).

My take on it follows the same ideas of passing context around. Since `context` is immutable and can be copied to
another `context` via `WithContext(...)` we can't use it directly as a namespace (the address changes). So we have
to put an immutable "address value" to it at the beginning of the request's life cycle. The value remains the same throughout
the whole request-handling branch. So we can use it to access request-scoped data (so we need a service to maintain
such state) as seen here:

```
// Request initiation
requestCtx := context.WithValue(context.Background(), "request", "<unique-id>") // can be used for tracing as well
result, err := handleRequest(requestCtx, ...)

// Within a nested goroutine we can access request storage
// Global storage manager is your custom type with a resource that your app needs 
func handlerRequest(ctx Context) any {
    data := request.GetData(ctx) // <-- we use ctx as a key for accessing request-level data
	// .. do work
}
```

The request-level storage is custom for each app and uses appropriate types of data. However, the idea remains the same,
such request-level isolated data is addressed by the value stored in `context`.