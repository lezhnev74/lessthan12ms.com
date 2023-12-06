- slug:how-to-validate-a-php-array-format-structure
- date:Jun 19, 2017 16:04
# How to validate(check) a php array format (structure)
I have been working with arrays for ages and sometimes I needed a way to validate that array has expected structure. I usually need to know if an array has specific keys and specific values (at least type of values).

For example, I want to validate this array which represents an HTTP request:
```
$http_request = [
    "method" => "post",
    "url" => "https://example.org/shop/cart.php?promo=A1",
    "time_to_response_ms" => 300
];
```

I want to make sure that:

* 3 specific keys exist
* method can only be one of these values (get, post, delete, put, head, options)
* url looks like an url
* time_to_response_ms is a non-negative number

How do I do that?

## With built-in PHP functions
So let's start with the most vanilla way - using `array_key_exists()` and `is_<type>()` and other functions. As easy as it sounds, one needs to write many if statements to validate a simple array.

```
function validate_array(array $a): bool {
    // 1. validate keys exist
    if(!array_key_exists('method', $a) ||
     !array_key_exists('url', $a) ||
     !array_key_exists('time_to_response_ms', $a)) {
        return false;
    }

    // 2. validate method value
    if(!in_array($a['method'], ['get','post','delete','put','head','options'])) {
        return false;
    }

    // 3. validate url value according to http://www.faqs.org/rfcs/rfc2396.html
    if(!filter_var($a['url'],FILTER_VALIDATE_URL,FILTER_FLAG_SCHEME_REQUIRED |  FILTER_FLAG_HOST_REQUIRED)) {
        return false;
    }

    // 4. validate time_to_response_ms is a non-negative integer
    if(!is_int($a['time_to_response_ms']) || $a['time_to_response_ms'] < 0) {
        return false;
    }

    return true;
}
```

Ok, looks good. No external packages - just built-in functions. 

## With popular Assert package
Very popular [Assert package](https://github.com/beberlei/assert) can execute many checks on your data. Assert is also suitable for our use case. This is how it can be done:

```
function validate_array(array $a): bool {
	try {
		// 1. validate keys
		Assert::that($a)->keyExists('method');
		Assert::that($a)->keyExists('url');
		Assert::that($a)->keyExists('time_to_response_ms');

		// 2. validate method value
		Assert::that($a['method'])->string()->inArray(['get', 'post', 'delete', 'put', 'head', 'options']);

		// 3. validate url value according to http://www.faqs.org/rfcs/rfc2396.html
		Assert::that($a['url'])->url();

		// 4. validate time_to_response_ms is a non-negative integer
		Assert::that($a['time_to_response_ms'])->integer()->greaterThan(-1);

	} catch (\Assert\InvalidArgumentException $e) {
		return false;
	}

    return true;
}
```

Since assert will throw an exception we will transform it into the boolean result.
I'd say this code is more readable than the first version and yet it does the same thing.

## With Matchmaker
**(Update 2018) - this package is abandoned and has known flaws. DO NOT USE IT.**
This is a gem I found on the Internet. The original package seemed to be abandoned, but people forked it and made few nice improvements. I recommend to fork and use this version: [https://github.com/cdrubin/matchmaker](https://github.com/cdrubin/matchmaker) 

This package allows you to specify a visual pattern which your array will be compared against. The cool thing is that a pattern is so visual that you have no problem reading the validation code. 

Look at the example:

```
function validate_array(array $a): bool {
    $pattern = [
        "method" => ":string in(get,post,delete,put,head,options)",
        "url" => ":url",
        "time_to_response_ms" => ":int gte(0)",
    ];

    return \matchmaker\matches($data, $pattern);
}
```

Look how easy that is. The sideeffect of this - it won't allow an array to have any other keys except defined in the pattern.

## PASVL ([read more about it](https://lessthan12ms.com/php-array-validation-gets-simpler/))
Since matchmaker is abandoned, I decided to replicate similar library called [PASVL](https://github.com/lezhnev74/pasvl) - PHP Array Structure Validation Library. 
It allows to set expectation as a pattern and then match it to the array. It is written in OO manner and offers hints about where the mismatch actually happened.

So far, my choises are:

* Assert for all the scalar checks
* "PASVL" for array validation


## More references
- [filtering and validation library from Aura project](https://github.com/auraphp/Aura.Filter).