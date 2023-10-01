# a query builder for elasticsearch database

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alirzaj/laravel-elasticsearch-builder.svg?style=flat-square)](https://packagist.org/packages/alirzaj/laravel-elasticsearch-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/alirzaj/laravel-elasticsearch-builder.svg?style=flat-square)](https://packagist.org/packages/alirzaj/laravel-elasticsearch-builder)

---
This package can build queries for elasticsearch database and provides an easy way to add Eloquent models to an elasticsearch index.

It is not trying to provide Eloquent-like api to work with elasticsearch. instead, it will give you methods that are much like writing a query in elasticsearch but in a more object-oriented and nicer way.

this package contains the queries that I needed on my projects. so if you feel the need of a method or feature please open an issue, and I will try to implement it as soon as possible.

<!-- TOC -->
* [a query builder for elasticsearch database](#a-query-builder-for-elasticsearch-database)
* [Installation](#installation-)
  * [Usage](#usage)
* [define indices](#define-indices)
* [create indices](#create-indices)
* [delete indices](#delete-indices)
* [configuration](#configuration)
* [making models searchable](#making-models-searchable)
* [indexing documents without eloquent models and using searchable trait](#indexing-documents-without-eloquent-models-and-using-searchable-trait)
* [bulk indexing documents](#bulk-indexing-documents)
* [update documents having a condition](#update-documents-having-a-condition)
* [add an item to a nested field](#add-an-item-to-a-nested-field)
* [add an item to an array field](#add-an-item-to-an-array-field)
* [update a document's nested field items having a condition](#update-a-documents-nested-field-items-having-a-condition)
* [update all documents' nested field items having a condition](#update-all-documents-nested-field-items-having-a-condition)
* [remove item from a nested field in a specific document](#remove-item-from-a-nested-field-in-a-specific-document)
* [remove item from a nested field by conditions](#remove-item-from-a-nested-field-by-conditions)
* [delete a document](#delete-a-document)
* [delete all documents that meet some conditions](#delete-all-documents-that-meet-some-conditions)
* [querying indices](#querying-indices)
* [include an index in query:](#include-an-index-in-query)
* [boost the score of some indices](#boost-the-score-of-some-indices)
* [determine search type](#determine-search-type)
* [find a document by its id](#find-a-document-by-its-id)
* [match](#match-)
* [match_all](#matchall)
* [multi_match](#multimatch)
* [nested](#nested)
* [exists](#exists)
* [bool](#bool)
* [term](#term)
* [terms](#terms)
* [range](#range)
* [dis_max](#dismax)
* [aggregations (aggs)](#aggregations-aggs)
* [working with array fields](#working-with-array-fields)
* [getting results](#getting-results)
* [determine a size limit for results](#determine-a-size-limit-for-results)
* [determine from option for getting results (pagination)](#determine-from-option-for-getting-results-pagination)
* [select specific fields](#select-specific-fields)
* [build queries based on conditions](#build-queries-based-on-conditions)
* [debugging](#debugging)
* [using the low-level elasticsearch client](#using-the-low-level-elasticsearch-client)
* [logging](#logging)
* [Testing Helpers](#testing-helpers)
  * [refreshing indices state](#refreshing-indices-state)
  * [assertions](#assertions)
* [Credits](#credits)
* [License](#license)
<!-- TOC -->

# Installation 
to install this package, require it via composer:

`composer require alirzaj/laravel-elasticsearch-builder`

## Usage

# define indices
first you need to define index classes. an index class must extend the **Alirzaj\ElasticsearchBuilder\Index** class. here is an example:

```php
<?php

namespace Alirzaj\ElasticsearchBuilder\Tests\Indices;

use Alirzaj\ElasticsearchBuilder\Index;

class Users extends Index
{
    public string $name = 'users_index';

    public array $propertyTypes = [
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
    
    public array $dateFormats = [
        'created_at' => 'strict_date_optional_time||strict_date_optional_time_nanos||yyyy-MM-dd HH:mm:ss',
    ];

    public array $analyzers = [
        'hashtag' => [
            'type' => 'custom',
            'tokenizer' => 'hashtag_tokenizer',
            'filter' => ['lowercase'],
        ],
        'hashtag_2' => [
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

    public array $propertyAnalyzers = [
        'text' => 'hashtag',
    ];

    public array $searchAnalyzers = [
        'text' => 'hashtag_2',
    ];

    public array $normalizers = [
        'my_normalizer' => [
            'type' => 'custom',
            'char_filter' => ['special_character_strip'],
            'filter' => ['lowercase',]
        ],
    ];

    public array $propertyNormalizers = [
        'ip' => 'my_normalizer'
    ];

    public array $characterFilters = [
        'special_character_strip' => [
            'type' => 'pattern_replace',
            'pattern' => '[._]',
        ],
    ];

    public array $tokenFilters = [
        '4_7_edge_ngram' => [
            'min_gram' => '4',
            'max_gram' => '7',
            'type' => 'edge_ngram',
            'preserve_original' => 'true',
        ],
    ];
}
```

if you don't define `$name` index name will equal to class name.

`$propertyTypes` array is a map of property names and their data type. key is name of field and value is data type.

`$fields` is other definitions of a field. for example, you want to save another version of text field to match hashtags (with another analyzer).

`$analyzers` is custom defined analyzers for index.

`$tokenizers` contains config for tokenizers.

 the only property that you **must** define is `$properties`.

 If you don't know what other properties do, please refer to doc comment of each property to read more about it.

# create indices
to create the indices defined in previous step, run **`php artisan elastic:create-indices`**

# delete indices
to delete all indices defined via this package, run **`php artisan elastic:delete-indices`**


# configuration
publish the package's config file using **`php artisan vendor:publish --provider="Alirzaj\\ElasticsearchBuilder\\ElasticsearchBuilderServiceProvider"`** command. all options have description in file.

# making models searchable
you can use **`Alirzaj\ElasticsearchBuilder\Searchable`** trait in Eloquent models. this trait will automatically add & update documents in elasticsearch on the corresponding index. you can override **`toIndex`** method on your models to control the attributes that will save on elasticsearch. default behaviour is array representation of the model (toArray).

# indexing documents without eloquent models and using searchable trait

in some situations you may need to index or update a document without using searchable trait on Eloquent models. this package offers two jobs for indexing and updating.

```php
IndexDocument::dispatch(
                'name_of_index',
                'id',
                ['name' => 'alirzaj'] //an array that you want indexed in elasticsearch
            );

UpdateDocument::dispatch(
                'name_of_index',
                'id',
               ['name' => 'alirzaj'] //an array that you want to add in your existing elasticsearch document
            );
```

# bulk indexing documents

```php
BulkIndexDocuments::dispatchSync(
        'blogs',
        [
            ['id' => 1, 'title' => 'abcd'],
            ['id' => 2, 'title' => 'efgh'],
        ]
    );
```

# update documents having a condition

```php
UpdateDocumentsByCondition::dispatchSync(
        'blogs',
        [
            'condition-field' => 'condition-value',
            'condition-field-2' => null,
        ],
        ['my-field' => 'new-value'],
    );
```

if you want to update a **large field** of documents having some condition, be sure to use 4th argument of `UpdateDocumentsByCondition` job.

```php
UpdateDocumentsByCondition::dispatchSync(
        'blogs',
        [
            'condition-field' => 'condition-value',
            'condition-field-2' => null,
        ],
        ['text' => 'large-value'],
        ['text']
    );
```

# add an item to a nested field

```php
AddItemToNestedField::dispatchSync(
        'blogs',
        10,
        'tags',
        ['id' => 20, 'name' => 'php'],
    );
```


# add an item to an array field

```php
AddItemToArrayField::dispatchSync(
        'blogs',
        10,
        'tags',
        'php',
    );
```


# update a document's nested field items having a condition

```php
UpdateNestedItemByCondition::dispatchSync(
        'blogs',
        10,
        'tags',
        ['id' => 20], // in document, we have a [nested] tags field. now we are looking for the ones with id of 20
        /**
         * we want all of those items having above condition to be updated to this item
         * note that if you have id key in conditions, and id key in document parameter, the values must be the same
         * in other words condition's value must not change in update.
         * in this example we find the tag via id and update its name. we couldn't find it via old name and set a new name
         */
        ['id' => 20, 'name' => 'new-php']
    );
```

# update all documents' nested field items having a condition

```php
 UpdateNestedItemByQuery::dispatchSync(
        'blogs',
        'tags',
        ['id' => 20], // in documents, we have a [nested] tags field. now we are looking for all documents with this criteria
        /**
         * we want all of those items having above condition to be updated to this item
         * note that if you have id key in conditions, and id key in document parameter, the values must be the same
         * in other words condition's value must not change in update.
         * in this example we find the tag via id and update its name. we couldn't find it via old name and set a new name
         */
        ['id' => 20, 'name' => 'new-php']
    );

```

# remove item from a nested field in a specific document

```php
 /**
     * In tags field, remove all sub-fields with the key of id and value of 20
     */
    RemoveItemFromNestedField::dispatch('blogs', 10, 'tags', 'id', 20);
```

# remove item from a nested field by conditions

```php
 /**
     * find documents that have id:20 in their tags field and delete id:20 from them
     */
    DeleteNestedFieldByCondition::dispatch(
        'blogs',
        'tags',
        ['id' => 20]
    );
```


# delete a document

```php
 DeleteDocument::dispatchSync('blogs',10);
```

# delete all documents that meet some conditions

```php
DeleteDocumentsByCondition::dispatchSync(
        'blogs',
        [
            'condition-field' => 'condition-value',
            'condition-field-2' => null,
        ],
    );
```

# querying indices
if you have searchable models you can begin to query the corresponding index like this:

```php
Model::elasticsearchQuery()
```

you can also start querying indices by instantiating the **`Query`** class:

```php
new \Alirzaj\ElasticsearchBuilder\Query();
```

# include an index in query:
you can add an index to the indices that are being queried:

```php
Blog::elasticsearchQuery()->addIndex(Users::class)->addIndex('blogs');
```

# boost the score of some indices

```php
Blog::elasticsearchQuery()->addIndex('blogs')->addIndex('posts')->boost(['blogs' => 2]);

```

# determine search type

```php
Blog::elasticsearchQuery()->addIndex('blogs')->searchType('dfs_query_then_fetch');

```
for more information visit https://www.elastic.co/guide/en/elasticsearch/reference/current/search-search.html

# find a document by its id


```php
Blog::elasticsearchQuery()->find(150);
```

# match 
you can use named arguments to only pass the options you need.

```php
Blog::elasticsearchQuery()->match('field', 'value', 'analyzer', 'fuzziness');
```

# match_all
```php
Blog::elasticsearchQuery()->matchAll(1.7);
```

# multi_match
```php
Blog::elasticsearchQuery()->multiMatch(['field1', 'field2'], 'value', 'analyzer', 'fuzziness', 'type');
```

# nested
```php
Blog::elasticsearchQuery()->nested(
    fn (Query $query) => $query->match('field', 'value'), //query
    'driver.vehicle', //path
    'sum',//score mode
    true //ignore_unmapped
);
```

# exists
```php
Blog::elasticsearchQuery()->exists('title');
```

# bool
you can pass closures to the boolean method and type hint the type of query you want:

```php
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
        fn(BooleanOptions $booleanOptions) => $booleanOptions->minimumShouldMatch(1)
    );
```

# term
```php
Blog::elasticsearchQuery()->term('field', 'value', 1.5);
```

# terms
```php
Blog::elasticsearchQuery()->terms('field', ['value-1', 'value-2']);
```

# range
```php
Blog::elasticsearchQuery()->range(field: 'field', gte: 10, lte: 20);
```

# dis_max
```php
Blog::elasticsearchQuery()
    ->disjunctionMax(
        fn (Query $query) => $query->match('a', 'b'),
        fn (Query $query) => $query->boolean(
            fn (Should $should) => $should
                 ->exists('f')
                 ->term('g', 'h')
          ),
    );
```

# aggregations (aggs)

you can build aggregation queries like this: 
(note that currently, when you use aggregations, you'll get raw elasticsearch results)

```php
(new Query())
        ->addIndex('posts')
        ->addIndex('users')
        ->size(0)
        ->boolean(
            fn(Must $must) => $must->term('published', true),
            fn(Should $should) => $should
                ->term('title', 'aaaa')
                ->term('title', 'bbbb'),
        )
        ->aggregation('types', (new Terms('morph_type'))
            ->aggregation('latest', (new TopHits(source: ['title', 'morph_type', 'created_at'], size: 3))
                ->sort(field: 'created_at', direction: 'desc')
            )
        )
        ->get();
```

# working with array fields

this package provides two jobs for updating/removing an item from an array field:

```php
RemoveArrayItem::dispatch('index_name', 'array_field_name', 'value_to_remove');
UpdateArrayItem::dispatch('index_name', 'array_field_name', 'old_value', 'new_value');
```

# getting results
after writing a query, you can call `get()` to get the results as a collection.

```php
Blog::elasticsearchQuery()->match('title', 'ttt')->get(); //a collection including _source of the resulting documents
```
you can also hydrate the results as eloquent models:

```php
Blog::elasticsearchQuery()->match('title', 'ttt')->hydrate(); //an Eloquent collection containing models filled with attributes from elasticsearch documents
```

note that the result collection's keys are _id of your documents.

# determine a size limit for results

```php
Blog::elasticsearchQuery()->match('title', 'ttt')->size(15)->get();
```

# determine from option for getting results (pagination)

```php
Blog::elasticsearchQuery()->match('title', 'ttt')->from(10)->get();
```

for more information visit https://www.elastic.co/guide/en/elasticsearch/reference/current/paginate-search-results.html

# select specific fields

`only()` method will add "_source" to your query.

```php
Blog::elasticsearchQuery()->match('title', 'ttt')->only(['title'])->get();
```

# build queries based on conditions

the query builder uses Laravel's Conditionable trait under the hood which means you can do sth like this:

```php
use Alirzaj\ElasticsearchBuilder\Query\Query;

Blog::elasticsearchQuery()
    ->match('title', 'ttt')
    ->when(isset($select), fn(Query $query) => $query->only(['title']))
    ->get(); 
```

# debugging

you can dump or die the query:

```php
Blog::elasticsearchQuery()->match('title', 'ttt')->dump()->exists('field')->dump();
Blog::elasticsearchQuery()->match('title', 'ttt')->dd();
```

# using the low-level elasticsearch client
this package will bind the `Elastic\Elasticsearch\Client` class to the service container as a singleton, so you can resolve it out of the container whenever you need to use it directly.

# logging
when the environment is testing or local, this package will log executed queries in `storage/logs/elasticsearch.log` file.

# Testing Helpers

## refreshing indices state

this package provides a `RefreshElasticsearchDatabase` trait that you can use to clean up the elasticsearch indices after each test.

first, you have to use this trait in your test case.

```php
abstract class TestCase extends BaseTestCase
{
    use RefreshElasticsearchDatabase;
}
```

then you should call two methods. one in your `setUp()` method and one in `tearDown()`

```php
public function setUp(): void
{
    parent::setUp();

    $this->createIndices();
}
```


```php
public function tearDown(): void
{
    $this->clearElasticsearchData();

    parent::tearDown();
}
```

## assertions

this package provides an `InteractsWithElasticsearch` trait that you can use in your test cases in order to make assertion on data in elasticsearch indices.


```php
abstract class TestCase extends BaseTestCase
{
    use InteractsWithElasticsearch;
}
```

you can assert if a certain document exists in an elasticsearch index:

```php
 $this->assertElasticsearchHas(
    'blogs',
    15,
    ['title' => 'my title']
 );
```

or make sure that a document is not indexed in elasticsearch:

```php
 $this->assertElasticsearchMissing(
    'blogs',
    15,
    ['title' => 'my title']
 );
```

# Credits

- [AL!R3Z4](https://github.com/alirzaj)
- [All Contributors](../../contributors)

# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
