<?php

use Alirzaj\ElasticsearchBuilder\Jobs\UpdateDocumentsByCondition;
use Elastic\Elasticsearch\Client;

it('can update documents without large fields in them', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('updateByQuery')
        ->with([
            "refresh" => true,
            "index" => "blogs",
            "body" => [
                "script" => [
                    "source" => "ctx._source.my-field = params.my-field; ",
                    "params" => [
                        "my-field" => "new-value"
                    ],
                ],
                "query" => [
                    "bool" => [
                        "filter" => [
                            [
                                "terms" => [
                                    "condition-field" => ["condition-value"]
                                ]
                            ]
                        ],
                        "must_not" => [
                            [
                                "exists" => [
                                    "field" => "condition-field-2"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ])
        ->andReturn([]);

    UpdateDocumentsByCondition::dispatchSync(
        'blogs',
        [
            'condition-field' => 'condition-value',
            'condition-field-2' => null,
        ],
        ['my-field' => 'new-value']
    );

    expect(true)->toBeTrue();
});

it('can update documents with large fields in them', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                '_source' => '',
                'size' => 10000,
                "query" => [
                    "bool" => [
                        "filter" => [
                            [
                                "terms" => [
                                    "condition-field" => ["condition-value"]
                                ]
                            ]
                        ],
                        "must_not" => [
                            [
                                "exists" => [
                                    "field" => "condition-field-2"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ])
        ->andReturn([
            'hits' => [
                'hits' => [
                    ['_source' => [], '_id' => 1],
                    ['_source' => [], '_id' => 2],
                    ['_source' => [], '_id' => 3],
                ],
            ],
        ])
        ->shouldReceive('bulk')
        ->with(['refresh' => true, 'body' => [
            ['update' => ['_index' => 'blogs', '_id' => 1]],
            ['doc' => ['my-field' => 'new-value']],
            ['update' => ['_index' => 'blogs', '_id' => 2]],
            ['doc' => ['my-field' => 'new-value']],
            ['update' => ['_index' => 'blogs', '_id' => 3]],
            ['doc' => ['my-field' => 'new-value']],
        ]])
        ->andReturn([]);

   /* \Pest\Laravel\mock(Client::class)
        ->shouldReceive('bulk')
        ->with(['refresh' => true, 'body' => [
            ['update' => ['_index' => 'blogs', '_id' => 1]],
            ['doc' => ['my-field' => 'new-value']],
            ['update' => ['_index' => 'blogs', '_id' => 2]],
            ['doc' => ['my-field' => 'new-value']],
            ['update' => ['_index' => 'blogs', '_id' => 3]],
            ['doc' => ['my-field' => 'new-value']],
        ]])
        ->andReturn([]);*/


    UpdateDocumentsByCondition::dispatchSync(
        'blogs',
        [
            'condition-field' => 'condition-value',
            'condition-field-2' => null,
        ],
        ['my-field' => 'new-value'],
        ['my-field']
    );

    expect(true)->toBeTrue();
});
