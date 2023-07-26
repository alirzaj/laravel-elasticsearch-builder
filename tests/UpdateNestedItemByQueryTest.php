<?php

use Alirzaj\ElasticsearchBuilder\Jobs\UpdateNestedItemByQuery;
use Elasticsearch\Client;

it('can update an item of a nested field that satisfies some conditions in all documents having those conditions', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('updateByQuery')
        ->with([
            'retry_on_conflict' => 3,
            'refresh' => true,
            'index' => 'blogs',
            'body' => [
                'script' => [
                    'source' => 'def targets = ctx._source.tags.findAll(f -> (f.id == params.id)); for(t in targets) { t.id = params.id; t.name = params.name;  }',
                    'params' => ['id' => 20, 'name' => 'new-php']
                ],
                'query' => [
                    "nested" => [
                        "ignore_unmapped" => true,
                        "path" => "tags",
                        "score_mode" => "sum",
                        "query" => [
                            "bool" => [
                                "must" => [
                                    [
                                        "match" => [
                                            "tags.id" => 20
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ])
        ->andReturn([]);

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

    expect(true)->toBeTrue();
});
