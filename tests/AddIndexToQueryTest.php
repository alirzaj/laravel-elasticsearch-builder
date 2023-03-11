<?php

use Alirzaj\ElasticsearchBuilder\Tests\Indices\Users;
use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elasticsearch\Client;

it('can add multiple indexes to the query', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs', 'users_index'],
            'body' => [
                'query' => [
                    'match' => [
                        'field' => [
                            'query' => 'test',
                            'fuzziness' => 'AUTO',
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

    Blog::elasticsearchQuery()->addIndex(Users::class)->match('field', 'test')->get();

    expect(true)->toBeTrue();
});
