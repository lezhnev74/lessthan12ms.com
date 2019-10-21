slug:validation-is-documentation
date: May 18, 2018 17:14
# Validation is documentation
## Separation of concerns

In clean architecture, we keep our codebase decoupled. It means, for example, keeping data retrieval mechanism hidden from the consumer. Let’s say a developer who writes front-end code (controllers, routes, views) needs some data to draw a page. He is not allowed to query the database directly, so what he can do is execute a query. A query is a way of reading the data from a backend system and it returns a result in a form of an array of scalars.

After first iteration frontend code would look like this:
```php
PageController {
    function welcome() {
        $currentUser = [
            “id” => 605,
            “name” => “John Doe”,
            “joinedAt” => “22.04.2017”
        ];
        return $this->render(“welcome.tpl”,[“user”=>$currentUser]);
    }
}
```

The view expects some specific data structure, so we need to ask backend to provide it.
The second iteration would be like this:

```php
PageController {
    function welcome() {
        // backend, please give my data
        $query = new FindUserQuery($this->currentUser->id);
        $queryResult = $this->queryBus->find($query);
        $currentUser = $queryResult->asArray();

        return $this->render(“welcome.tpl”,[“user”=>$currentUser]);
    }
}
```

Here I as frontend developer do not control the data retrieval and thus data format. I pass data from the backend to the view here, the problem is that I cannot be sure that the format of the query was expected. It could be that backend for whatever reason missed some array keys since I can’t control it I can’t be sure. 

What do I do?

## Protocol is the key

We could add a note to the documentation or a comment in the code about the structure this query returns, but it is not safe. Since if someone changes the code, the comment immediately becomes out of date. 
The second thing I could do is making format check at my end. Something like this:

```php
PageController {
    function welcome() {
        // backend, please give my data
        $query = new FindUserQuery($this->currentUser->id);
        $queryResult = $this->queryBus->find($query);
        $currentUser = $queryResult->asArray();

        // Validate query result here in controller
        // I use PASVL library for this purpose
        $pattern = [
            “id” => “:int”,
            “name” => “:string”,
            “joinedAt” => “:date”
        ];
        $traverser = new Traverser(new ValidatorLocator());
        if(!$traverser->check($pattern, $queryResult->asArray())) {
            // array has different structure!
						throw new Exception("Ooops!");
        }

        return $this->render(“welcome.tpl”,[“user”=>$currentUser]);
    }
}
```

I have already blogged about [array validation techniques](https://lessthan12ms.com/php-array-validation-gets-simpler/) (take a look). Here is just a perfect case to enforce the array structure before passing it to the view. Using a pattern I make sure that data has expected format.

Now I solved the problem mentioned above but I generated another one - the backend developer may be not aware of my expectations. It is a better practice to put validation inside the QueryResult object itself. Since it is a Value Object it should keep it’s validation inline. By doing this we prevent QueryResult from being ever created with a wrong data inside:

```php
Class FindUserQueryResult {
    private $array;
    private $pattern = [
            “id” => “:int”,
            “name” => “:string”,
            “joinedAt” => “:date”
        ];

    function __construct($id, $name, $joinedAt) {
        $data = [
            “name” => $name,
            “id” => $id,
            “joinedAt” => $joinedAt
        ];

        $this->validate($data);
        $this->array = $data;
    }

    public function asArray() {
        return $this->array;
    }

    private function validate($data) {
        $traverser = new Traverser(new ValidatorLocator());
        if(!$traverser->check($pattern, $queryResult->asArray())) {
            throw new InvalidArgumentException();
        }        
    }
}
```

Ok, this looks properly fine. The medium of data (the class) now contains built-in validation logic both parties can rely on. Frontend and Backend developers can easily control what structure this query must return.

## Conclusion

In this case, I applied the DRY principle for validation of data retrieved from the backend. The user is protected from the mistake in the data format and both developers can rely on the value object because it validates its state automatically. The internal validation serves two purposes:
- it enforces the structure of the data;
- and it documents the format (since you can easily read the pattern).

Isn’t it nice and clean?