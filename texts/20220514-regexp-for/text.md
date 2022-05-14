- date: 14 May 2022, 12:00
- slug: regular-expressions-for

# Like Regular Expressions But For...

It is hard to argue that regular expressions are magical. With so simple syntax, it can do things that otherwise could be hardly possible - finding pattern matches in strings. Finding patterns is a powerful tool, so many tasks can be deduced to finding a known piece in a pile of unknown data. The problem is that regular expressions are for strings only. Or are they?

## Regular Expressions For PHP Arrays

In PHP, the most common data structure is the Array. Unlike the usual arrays in other languages, this one is not typed, so keys and values can be of any type possible. That is a whole another topic about dynamic typing, the point is that I needed a way to check if a given array matches some expected data pattern: it has certain keys and values are of certain types.

Guess what, I immediately thought it'd be nice if I could specify something like a regular expression but... for PHP arrays. I was not the only one who came up with the idea, this nice package [ptrofimov/matchmaker](https://github.com/ptrofimov/matchmaker) does roughly the same. It inspired me a lot, I started using it. However, after in-depth review I found a few design limitations in that package, so I took the ideas and designed a new package: [lezhnev74/pasvl](https://github.com/lezhnev74/pasvl). I have been using it for years now.

Now I could quickly write a pattern for an array and test if it matches. Just like we do with `preg_match()` in PHP. This tool falls in a category of code quality control, it improves the readability of the code, you can easily tell what we expect from an array. Notice how clean and easy to read the pattern is:

```php
$pattern = [
    '*' => [
        'type' => 'book',
        'title' => ':string :contains("book")',
        'chapters' => [
            ':string :len(2) {1,3}' => [
                'title' => ':string',
                ':exact("interesting") ?' => ':bool',
            ],
        ],
    ],
];

if (!array_match($pattern, $data)) { // oops }
```

A post in 2018: [PHP Array validation gets simpler](https://lessthan12ms.com/php-array-validation-gets-simpler.html).

## Regular Expressions For HTTP Messages

Through time, I developed many HTTP APIs. API is a protocol (a pattern) of messages that come in and out. I needed a way to check if a message conforms to the protocol. Again, a good task for a "regular expression"-ish tool.

Luckily, the community already developed a pattern specification language for HTTP messages. It is called [OpenAPI](https://www.openapis.org/). It is a language to specify HTTP APIs. The only missing part was to make sure that a protocol described in OpenAPI language matches to a given HTTP message. That is how I came to [lezhnev74/openapi-psr7-validator](https://github.com/lezhnev74/openapi-psr7-validator). It was based on an existing OpenAPI parser: [cebe/php-openapi](https://github.com/cebe/php-openapi). Notice how one package grows on top of another:

<blockquote class="twitter-tweet"><p lang="en" dir="ltr">A great open-sourcing has occurred. This OpenAPI validator for PHP uses PSR7 middlewares to implement server-side validation in pretty much any PHP framework, and is built on <a href="https://twitter.com/cebe?ref_src=twsrc%5Etfw">@cebe</a>&#39;s php-openapi validator. <a href="https://t.co/DJynVF1IOm">https://t.co/DJynVF1IOm</a> <a href="https://t.co/Pe0cWdFZ9J">pic.twitter.com/Pe0cWdFZ9J</a></p>&mdash; Wandering Woodsman 🔥🌍🇺🇦 (@philsturgeon) <a href="https://twitter.com/philsturgeon/status/1125287278631378944?ref_src=twsrc%5Etfw">May 6, 2019</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

This package got good attention from the community, and I was offered to contribute it to [The League of Extraordinary Packages](https://thephpleague.com/), so the whole community can drive it's development further. So I did and now the package is growing under this handle: [thephpleague/openapi-psr7-validator](https://github.com/thephpleague/openapi-psr7-validator).

A post in 2019: [OpenAPI with PHP - documenting and testing API automatically](https://lessthan12ms.com/openapi-with-php-documenting-and-testing-api-automatically.html)

## Regular Expressions For Streams Of Events

Recently, I have been playing with event sourcing techniques. Event Sourcing implies that your data is purely made of small events that you need to process in order to extract meaningful insights. This is a new domain for me and a lot of uncharted lands ahead. However, even in this new area I found an opportunity to apply pattern matching.

Given a lot of events, I needed to find customers who exposed certain behavior. Imagine a task to find all customers who purchased something after visit our blog. It is easy to specify somebody's behavior as a sequence of facts: "last week a person did A then did B then within 1 hour did C", find that person. It refers us to logical programming and declarative languages. When we define WHAT we need and WHAT we know, while we don't know HOW to get what we need. A program that can accept such input + a steam of events is something I was working on.

At that time I discovered a nice paper about [sequenced event sets](https://dl.acm.org/doi/10.1145/1951365.1951372) which inspired me a lot. It talks about finding sequences of events based on a pattern. I must say that is quite an interesting domain in general and a lot of research has been going in there.

Combined all my past knowledge and new ideas, I developed a proof-of-concept package: [lezhnev74/ses-go](https://github.com/lezhnev74/ses-go). This package allows one to specify a few events that must happen in a sequence and later apply such a pattern on top of a stream. A sample pattern would look like this:

```
// Sample Query: find all web sessions where users read blog before purchasing

within 3 days                                    // set a window for a match
     event website_visit+ where page~="blog/.*"  // find at least one blog visit
then event website_visit+ where page="cart.html" // then find at least one cart visit
then within 1 hour event purchase                // and then there must be a purchase within 1 hour after the cart visit
group by session_id                              // so all events must be within one session
```

Here we see an SQL-like declarative language that is parsed and turned to a state machine. Then we feed it with a series of events and finally can see if any sequences within the stream matched the pattern. The programs proved to be working, however I did not apply it to any real problem, so I am keeping that for future projects.

### Side Package For Specifying Time Windows

While I was working on the finding events in a stream, I needed a way to specify time windows. So I could say "find events within this time window". That task alone took me a while to figure out. I needed it to be flexible, so I can specify a window in different ways.

To solve that problem, I developed a new language and a package: [lezhnev74/window-spec](https://github.com/lezhnev74/window-spec). Now, I could define a window in a handful of ways:
- with absolute bounds: `from 9:00 am 22 June, 2022 to 1 January 2022`
- with a relative bound: `6 days BEFORE 1 September 2022`
- or I could specify a sliding window that slides through time and only has a duration: `within 12 hours`

I both package I wanted to have a language that is easy to write, an English-like language that looks like prose rather than machinery.

## Probably More...

At this point, it is obvious that software has a lot of opportunities for pattern matching (not a new insight, I know). The more data heavy applications I develop, the more pattern matching I do and find new applications for regular expression.

I want to praise open source movement for helping developers like me building new tools and sharing them back with the community. I think that it is fascinating how people from all around the world use each other's work for the good cause (I hope so).

To be continued...
