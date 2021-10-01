<?php

use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elasticsearch\Client;

it('can build multi match query', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => 'test',
                        'fuzziness' => 'AUTO',
                        'type' => 'best_fields',
                        'fields' => ['field1', 'field2'],
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

    Blog::elasticsearchQuery()->multiMatch(['field1', 'field2'], 'test')->get();
});

it('can set type of matching and analyzer and fuzziness', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => 'test',
                        'fuzziness' => 'fff',
                        'analyzer' => 'aaa',
                        'type' => 'ttt',
                        'fields' => ['field1', 'field2'],
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

    Blog::elasticsearchQuery()
        ->multiMatch(['field1', 'field2'], 'test', 'aaa', 'fff', 'ttt')
        ->get();
});
