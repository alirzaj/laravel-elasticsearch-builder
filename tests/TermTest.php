<?php

use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elasticsearch\Client;

it('can build a term query', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'term' => [
                        'field' => [
                            'value' => 'aaa',
                            'boost' => 1.5,
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

    Blog::elasticsearchQuery()->term('field', 'aaa', 1.5)->get();

    expect(true)->toBeTrue();
});
