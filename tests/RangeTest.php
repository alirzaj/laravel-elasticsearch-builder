<?php

use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elastic\Elasticsearch\Client;

it('can build range query', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'range' => [
                        'field' => [
                            'gte' => 10,
                            'lte' => 20,
                        ],
                    ],
                ],
            ],
        ])
        ->andReturn([
            'hits' => [
                'hits' => [
                    ['_source' => []],
                    ['_source' => []],
                    ['_source' => []],
                ],
            ],
        ]);

    Blog::elasticsearchQuery()->range(field: 'field', gte: 10, lte: 20)->get();

    expect(true)->toBeTrue();
});
