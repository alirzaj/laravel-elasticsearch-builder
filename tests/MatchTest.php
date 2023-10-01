<?php

use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elastic\Elasticsearch\Client;

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

    Blog::elasticsearchQuery()->match('field', 'test', 'aaa', 'AUTO')->get();

    expect(true)->toBeTrue();
});

test('users can determine search type', function() {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'search_type' => 'dfs_query_then_fetch',
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'match' => [
                        'field' => [
                            'analyzer' => 'aaa',
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

    Blog::elasticsearchQuery()
        ->searchType('dfs_query_then_fetch')
        ->match('field', 'test', 'aaa', 'AUTO')
        ->get();

    expect(true)->toBeTrue();
});

test('users can determine from option for getting results', function() {
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
                            'fuzziness' => 'AUTO',
                        ],
                    ],
                ],
                'from' => 10
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
        ->from(10)
        ->match('field', 'test', 'aaa', 'AUTO')
        ->get();

    expect(true)->toBeTrue();
});

test('boost score of some indices', function() {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'indices_boost' => ['blogs' => 2],
                'query' => [
                    'match' => [
                        'field' => [
                            'analyzer' => 'aaa',
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

    Blog::elasticsearchQuery()
        ->boost(['blogs' => 2])
        ->match('field', 'test', 'aaa', 'AUTO')
        ->get();

    expect(true)->toBeTrue();
});
