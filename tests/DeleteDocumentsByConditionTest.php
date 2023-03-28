<?php

use Alirzaj\ElasticsearchBuilder\Jobs\DeleteDocumentsByCondition;
use Elasticsearch\Client;

it('can delete documents having some condition', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('deleteByQuery')
        ->with([
            "refresh" => true,
            "index" => "blogs",
            "body" => [
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

    DeleteDocumentsByCondition::dispatchSync(
        'blogs',
        [
            'condition-field' => 'condition-value',
            'condition-field-2' => null,
        ],
    );

    expect(true)->toBeTrue();
});
