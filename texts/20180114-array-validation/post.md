slug: php-array-validation-gets-simpler
date: Jan 14, 2018 23:32
# PHP Array validation gets simpler
In this article, I am talking about a tool called [pasvl](https://github.com/lezhnev74/pasvl) (still in beta as of Jan 2018) which is for array validation purposes. 

## Enforce data within your app

In Domain Driven Designed apps it is usual to transfer data in a form of Value Objects(**VO**). Those are simple plain PHP objects which carry data and are pushed between layers and bounded contexts. But they are not just dummy storage objects, they have validation logic within when your code receives value object you treat its content as valid.

One of the most common cases for value objects is converting client's input (for example, from HTTP request) to set of value objects. And then pass these objects to a message bus for further handling. This is where data validation comes into play.

VOs can contain simple data - scalars like strings and numbers or compound values like arrays. While validating scalars is relatively simple with tools like well established [Assert library](https://github.com/beberlei/assert), validation of arrays was something I used to struggle with. That's when I [decided](https://lessthan12ms.com/how-to-validate-a-php-array-format-structure/) to implement easy to use tool which can validate complex nested arrays.


## Easy array validation

The array is a compound type, it can have scalar keys and any values, and it can be of multi-levels. I was inspired by the existing tool which does array validation called [matchmaker](https://github.com/ptrofimov/matchmaker) and how easy I can design validation patterns. The tool has been abandoned for years and had lack of some features, so I took it's concept and re-implemented in an OOP way.

The tool allows you to set expectations on array's structure and match existing data against the pattern. **You can set expectations for both keys and values**. If some array's value does not meet pattern's expectations the execution halts with an error report exception (or just returns false).

### Example 1 - validate visa card

```php
use \PASVL\Traverser\TraversingMatcher;
use \PASVL\ValidatorLocator\ValidatorLocator;

$visa_card = [
    'number' => '4242424242424242',
    'valid_till' => '05/22',
    'name' => 'RICHARD LEE',
    'code' => '455'
];
$visa_pattern = [
    "number" => ":string :len(16)",
    "valid_till" => ":string :regex(#^\d{2}/\d{2}$#)",
    "name" => ":string :min(1)",
    "code" => ":string :regex(#^\d{3}$#)"
];

$matcher = new Traverser(new ValidatorLocator());
$matcher->check($visa_pattern, $visa_card);
```

If data matches a pattern then no exception will be thrown. In this case, data was valid.

### Example 2 - validate list of parents

```php
use \PASVL\Traverser\TraversingMatcher;
use \PASVL\ValidatorLocator\ValidatorLocator;

$parents_list = [
    [
        'name' => 'Balla Valentina McNiall',
        'children' => [
            [
                'name' => 'Enis Tristan',
                'gender' => 'boy',
                'birthday' => '22.12.2016',
            ],
        ],
    ],
    [
        "name" => "Anže Svantepolk Garrett",
    ],
];

$parents_pattern = [
    "*" => [
        "name" => ":string :min(1)",
        "children?" => [
            "*" => [
                "name" => ":string :min(1)",
                "gender" => ":string :in(boy,girl)",
                "birthday" => ":string :date",
            ]
        ],
    ],
];

$matcher = new Traverser(new ValidatorLocator());
$matcher->check($parents_pattern, $parents_list);
```

This example represents a valid data set and a pattern which tolerates that some array keys can be absent (`children` key is marked with `?` which means optional).

### Smart validation of different combinations

Sometimes when you define your expectation for array keys, there can be ambiguity. The same key can match different patterns and in this case, the tool intelligently tries to match a pattern against the data in different combinations and find out which one fits. 

Consider this case:
```php
use \PASVL\Traverser\Traverser;
use \PASVL\ValidatorLocator\ValidatorLocator;

// Data keys are in mixed order
$data = [
    "A" => "",
    "AB" => "",
    "B" => "",
    "BB" => "",
];
// Pattern expects 2 keys to be exactly 2 chars long, and 2 other keys can be 1-2 chars long.
$pattern = [
    // all of the keys match this pattern
    ':string :regex(#\w{1,2}#) {2}' => ":any",
    // only two keys match this one
    ':string :regex(#\w{2}#) {2}' => ":any",
];
$matcher = new Traverser(new ValidatorLocator());
$matcher->check($pattern, $data);
```


## Conclusion

Currently, the lib has a version lower than 1.0 which makes it still a beta version. But I am already actively using it and constantly working on new features.

There are a limited amount of such tools that I am aware of, so the effort of developing it helped me in quite a few cases where I had to validate complex user input.
