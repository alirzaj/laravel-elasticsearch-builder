<?php

use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elasticsearch\Client;

it('can build match query', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'match' => [
                        'field' => [
                            'analyzer' => 'aaa',
                            'query' => 'test',
                            'fuzziness' => 'AUTO'
                        ]
                    ]
                ]
            ]
        ])
        ->andReturn([
            'hits' => [
                'hits' => [
                    ['_source' => []],
                    ['_source' => []],
                    ['_source' => []],
                ]
            ]
        ]);

    Blog::elasticsearchQuery()->match('field', 'test', 'aaa', 'AUTO')->get();
});
