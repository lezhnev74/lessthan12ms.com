date: Dec 2, 2018 19:50
slug: value-objects-in-php
# Value Objects in PHP
One of the best things happened to my code was using Value Objects (VOs). 

> In computer science, a value object is a small object that represents a simple entity whose equality is not based on identity: i.e. two value objects are equal when they have the same value, not necessarily being the same object.
https://en.wikipedia.org/wiki/Value_object

Value objects are used to represent some undividable concept through the app. Say, a customer address. Instead of throwing scalars between classes (which happens a lot in the wild) I throw VOs. When you receive a value object you don't need to validate it, it is valid by default. The other important trait of VOs is immutability. You can be 100% sure that an object that you passed to the external logic won't be altered, thus you gain both speed and durability.

## Value object attributes
- **Immutability**
  An object cannot be changed by anyone. Once created it can only be cloned, thus the logic who created the object is confident that the data is never altered. This adds extra durability to the system by design.
- **Built-in validation**
  An object contains built-in validators which enforce its internal format. Say, an address must have a city, a postcode and street address. Whenever you instantiate a new address object, you must give it valid data otherwise the object instantiation fails. 
	
	
## Example value object
```
class Address implements JsonSerializable {
	private $city;
	private $country;
	private $postalCode;
	private $streetAddress;

  function __construct(string $city, string $country, string $postalCode, string $streetAddress){
			Assert::that($city)->minLength(5);
			Assert::that($ccountry)->minLength(10);
			Assert::that($postalCode)->regex("#^[0-9]{5,7}$#");
			Assert::that($streetAddress)->minLength(10);
			
			$this->city = $city;
			$this->country = $country;
			$this->postalCode = $postalCode;
			$this->streetAddress = $streetAddress;
	}
	
	function getCity(): string { return $this->city; }
	function getCountry(): string { return $this->country; }
	function getPostalCode(): string { return $this->postalCode; }
	function getStreetAddress(): string { return $this->streetAddress; }
	
	
	function isEqualTo(Address $address): bool {
	  return $this->city === $address->getCity() &&
		    $this->country === $address->getCountry() &&
			$this->postalCode === $address->getPostalCode() &&
			$this->streetAddress === $address->getStreetAddress();
	}
	
	
	// I find it useful to be able to serialize/deserialize VOs in json representation (see interface JsonSerializable)
	function toArray(): array {
			return [
				'city' => $this->city,
				'country' => $this->country,
				'postalCode' => $this->postalCode,
				'streetAddress' => $this->streetAddress
			];
	}
	
	static function fromArray(array $input): self {
	  // TODO: optional $input validation
	
		return new self(
		  $input['city'],
			$input['country'],
			$input['postalCode'],
			$input['streetAddress']
		);
	}
}
```

## Refs:
- [ValueObject by M.Fowler](https://martinfowler.com/bliki/ValueObject.html)
- [PHP package with a few general purpose Value objects](https://github.com/bruli/php-value-objects)