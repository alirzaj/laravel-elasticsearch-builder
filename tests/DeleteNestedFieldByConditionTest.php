<?php

use Alirzaj\ElasticsearchBuilder\Jobs\DeleteNestedFieldByCondition;
use Elasticsearch\Client;

it('can delete a nested field from all documents having some conditions', function () {
    $this->withoutExceptionHandling();

    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('updateByQuery')
        ->with([
            'refresh' => true,
            'index' => 'blogs',
            'body' => [
                'script' => [
                    'source' => 'ctx._source.tags.removeIf(f -> (f.id == params.id));',
                    'params' => ['id' => 20]
                ],
                'query' => [
                    'nested' => [
                        'ignore_unmapped' => true,
                        'path' => 'tags',
                        'score_mode' => 'sum',
                        'query' => [
                            'bool' => [
                                'must' => [
                                    ['match' => ['tags.id' => 20]]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ])
        ->andReturn([]);

    /**
     * find documents that have id:20 in their tags field and delete id:20 from them
     */
    DeleteNestedFieldByCondition::dispatch(
        'blogs',
        'tags',
        ['id' => 20]
    );

    expect(true)->toBeTrue();
});
