# a query builder for elasticsearch database

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alirzaj/laravel-elasticsearch-builder.svg?style=flat-square)](https://packagist.org/packages/alirzaj/laravel-elasticsearch-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/alirzaj/laravel-elasticsearch-builder.svg?style=flat-square)](https://packagist.org/packages/alirzaj/laravel-elasticsearch-builder)

---
This package can build queries for elasticsearch database and provides an easy way to add Eloquent models to an elasticsearch index.

It is not trying to provide Eloquent-like api to work with elasticsearch. instead, it will give you methods that are much like writing a query in elasticsearch but in a more object-oriented and nicer way.

this package contains the queries that I needed on my projects. so if you feel the need of a method or feature please open an issue, and I will try to implement it as soon as possible.

# Installation 
to install this package, require it via composer:

`composer require alirzaj/laravel-elasticsearch-builder`

## Usage

# define indices
first you need to define index classes. an index class must extend the **Alirzaj\ElasticsearchBuilder\Index** class. here is an example:


`<?php

namespace Alirzaj\ElasticsearchBuilder\Tests\Indices;

use Alirzaj\ElasticsearchBuilder\Index;

class Users extends Index
{
    public string $name = 'users_index';

    public array $properties = [
        'text' => 'text',
        'user_id' => 'keyword',
        'ip' => 'ip',
        'created_at' => 'date',
    ];

    public array $fields = [
        'text' => [
            'hashtags' => [
                'type' => 'text',
                'analyzer' => 'hashtag',
            ],
        ],
    ];

    public array $analyzers = [
        'hashtag' => [
            'type' => 'custom',
            'tokenizer' => 'hashtag_tokenizer',
            'filter' => ['lowercase'],
        ],
    ];

    public array $tokenizers = [
        'hashtag_tokenizer' => [
            'type' => 'pattern',
            'pattern' => '#\S+',
            'group' => 0,
        ],
    ];
}`

if you don't define `$name` index name will equal to class name.

`$properties` array is a map of property names and their data type. key is name of field and value is data type.

`$fields` is other definitions of a field. for example, you want to save another version of text field to match hashtags (with another analyzer).

`$analyzers` is custom defined analyzers for index.

`$tokenizers` contains config for tokenizers.

`$properties` is the only property that you **must** define.

# create indices
to create the indices defined in previous step, run **`php artisan elastic:create-indices`**

# configuration
publish the package's config file using **`php artisan vendor:publish --provider="Alirzaj\\ElasticsearchBuilder\\ElasticsearchBuilderServiceProvider"`** command. all options have description in file.

# making models searchable
you can use **`Alirzaj\ElasticsearchBuilder\Searchable`** trait in Eloquent models. this trait will automatically add & update documents in elasticsearch on the corresponding index. you can override **`toIndex`** method on your models to control the attributes that will save on elasticsearch. default behaviour is array representation of the model (toArray).

# querying indices
if you have searchable models you can begin to query the corresponding index like this:

`Model::elasticsearchQuery()`

you can also start querying indices by instantiating the **`Query`** class:
`new \Alirzaj\ElasticsearchBuilder\Query();`

# include an index in query:
you can add an index to the indices that are being queried:

`Blog::elasticsearchQuery()->addIndex(Users::class);`

# match 
you can use named arguments to only pass the options you need.

`Blog::elasticsearchQuery()->match('field', 'value', 'analyzer', 'fuzziness');`

# match_all
`blog::elasticsearchQuery()->matchAll(1.7);`

# multi_match

`Blog::elasticsearchQuery()->multiMatch(['field1', 'field2'], 'value', 'analyzer', 'fuzziness', 'type');`

# nested
`Blog::elasticsearchQuery()->nested(

    fn (Query $query) => $query->match('field', 'value'), //query
    
    'driver.vehicle', //path
    
    'sum',//score mode
    
    true //ignore_unmapped
);`

# exists
`Blog::elasticsearchQuery()->exists('title');`

# bool
Blog::elasticsearchQuery()
    ->boolean(
        fn (Must $must) => $must
            ->match('a', 'b')
            ->exists('description'),
        fn (MustNot $mustNot) => $mustNot
            ->match('a', 'b')
            ->exists('description'),
        fn (Filter $filter) => $filter
            ->match('a', 'b')
            ->exists('z'),
        fn (Should $should) => $should
            ->match('a', 'b')
            ->match('z', 'x', analyzer: 'custom-analyzer')
            ->multiMatch(['c', 'd'], 'e')
    );

# term
`Blog::elasticsearchQuery()->term('field', 'value', 1.5);`

# dis_max
`Blog::elasticsearchQuery()
    ->disjunctionMax(
        fn (Query $query) => $query->match('a', 'b'),
        fn (Query $query) => $query->boolean(
            fn (Should $should) => $should
                 ->exists('f')
                 ->term('g', 'h')
          ),
    );`


## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [AL!R3Z4](https://github.com/alirzaj)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
