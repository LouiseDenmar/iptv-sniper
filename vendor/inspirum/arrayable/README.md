# Arrayable

**Created as part of [inspishop][link-inspishop] e-commerce platform by [inspirum][link-inspirum] team.**

[![Latest Stable Version][ico-packagist-stable]][link-packagist-stable]
[![Build Status][ico-workflow]][link-workflow]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![PHPStan][ico-phpstan]][link-phpstan]
[![Total Downloads][ico-packagist-download]][link-packagist-download]
[![Software License][ico-license]][link-licence]


## Motivation

Unfortunately PHP does not have a nice way how to typecast objects to `array`.

There is the `__toString()` magic method for [`\Stringable`](https://www.php.net/manual/en/class.stringable.php) interface (since PHP 8.0) and the `jsonSerialize()` method for [`JsonSerializable`](https://www.php.net/manual/en/class.jsonserializable.php) interface (since PHP 5.4), but `__toArray()` method is not (and will not) be supported – there are just several rejected draft RFC ([object_cast_to_types](https://wiki.php.net/rfc/object_cast_to_types), [to array](https://wiki.php.net/rfc/to-array), ...) that suggests some kind of object to scalar type casting.

But so far (at least) there is no way to implement some (not even magic) method to be called when cast to `array`.

Ideally, something like this would work:

```php
class Person
{
    public function __construct(
        public string $name,
        protected string $username,
        private string $password,
    ) {}
 
    public function __toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->username,
        ];
    }
}

$person = new Person('John Doe', 'j.doe@example.com', 'secret_pwd');

$personArray = (array) $person; // casting triggers __toArray()

/*
var_dump($personArray);
[
  "name" => "John Doe"
  "email" => "j.doe@example.com"
]
*/
```

but actually it cast to array like this:

```php
/*
var_dump($personArray);
[
  "name" => "John Doe"
  "*username" => "j.doe@example.com"
  "Person@password" => "secret_pwd"
]
*/
```


## Usage example

*All the code snippets shown here are modified for clarity, so they may not be executable.*

This package implements simple `\Arrayable` (or `\Inspirum\Arrayable\Arrayable`) interface.

```php
/** @implements \Arrayable<string, string> */
class Person implements \Arrayable
{
    public function __construct(
        public string $name,
        protected string $username,
        protected string $password,
    ) {}
 
    /** @return array<string, string> */
    public function __toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->username,
        ];
    }
}

$person = new Person('John Doe', 'j.doe@example.com', 'secret_pwd');
```

There is `is_arrayable()` function (or `\Inspirum\Arrayable\Convertor::isArrayable()` method) to check if given data are able to type cast itself to `array`.

```php
var_dump(is_arrayable([1, 2, 3])); // bool(true)
var_dump(is_arrayable(new \ArrayIterator([1, 2, 3]))); // bool(true)
var_dump(is_arrayable(new \ArrayObject([4, 5, 6]))); // bool(true)
var_dump(is_arrayable((function () { yield 1; })())); // bool(true)
var_dump(is_arrayable(1)); // bool(false)
var_dump(is_arrayable(new \stdClass())); // bool(false)
var_dump(is_arrayable(new class {})); // bool(false)
var_dump(is_arrayable(new class implements \Arrayable {})); // bool(true)
var_dump(is_arrayable($person); // bool(true)
```

Then there is `to_array()` function (or `\Inspirum\Arrayable\Convertor::toArray()` method) to recursively cast data to `array`.

```php
$personArray = to_array($person);

/*
var_dump($personArray);
[
  "name" => "John Doe"
  "*username" => "j.doe@example.com"
  "*password" => "secret_pwd"
]
*/
```

There is also helper abstract classes for common use for DAO ([`BaseModel`](./src/BaseModel.php)) and collection ([`BaseCollection`](./src/BaseCollection.php)) objects.


## Testing

To run unit tests, run:

```bash
$ composer test:test
```

To show coverage, run:

```bash
$ composer test:coverage
```


## Contributing

Please see [CONTRIBUTING][link-contributing] and [CODE_OF_CONDUCT][link-code-of-conduct] for details.


## Security

If you discover any security related issues, please email tomas.novotny@inspirum.cz instead of using the issue tracker.


## Credits

- [Tomáš Novotný](https://github.com/tomas-novotny)
- [All Contributors][link-contributors]


## License

The MIT License (MIT). Please see [License File][link-licence] for more information.


[ico-license]:              https://img.shields.io/github/license/inspirum/arrayable-php.svg?style=flat-square&colorB=blue
[ico-workflow]:             https://img.shields.io/github/workflow/status/inspirum/arrayable-php/Test/master?style=flat-square
[ico-scrutinizer]:          https://img.shields.io/scrutinizer/coverage/g/inspirum/arrayable-php/master.svg?style=flat-square
[ico-code-quality]:         https://img.shields.io/scrutinizer/g/inspirum/arrayable-php.svg?style=flat-square
[ico-packagist-stable]:     https://img.shields.io/packagist/v/inspirum/arrayable.svg?style=flat-square&colorB=blue
[ico-packagist-download]:   https://img.shields.io/packagist/dt/inspirum/arrayable.svg?style=flat-square&colorB=blue
[ico-phpstan]:              https://img.shields.io/badge/style-level%208-brightgreen.svg?style=flat-square&label=phpstan

[link-author]:              https://github.com/inspirum
[link-contributors]:        https://github.com/inspirum/arrayable-php/contributors
[link-licence]:             ./LICENSE.md
[link-changelog]:           ./CHANGELOG.md
[link-contributing]:        ./docs/CONTRIBUTING.md
[link-code-of-conduct]:     ./docs/CODE_OF_CONDUCT.md
[link-workflow]:            https://github.com/inspirum/arrayable-php/actions
[link-scrutinizer]:         https://scrutinizer-ci.com/g/inspirum/arrayable-php/code-structure
[link-code-quality]:        https://scrutinizer-ci.com/g/inspirum/arrayable-php
[link-inspishop]:           https://www.inspishop.cz/
[link-inspirum]:            https://www.inspirum.cz/
[link-packagist-stable]:    https://packagist.org/packages/inspirum/arrayable
[link-packagist-download]:  https://packagist.org/packages/inspirum/arrayable
[link-phpstan]:             https://github.com/phpstan/phpstan
