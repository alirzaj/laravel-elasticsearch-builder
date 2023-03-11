<?php

use Alirzaj\ElasticsearchBuilder\Query\Must;
use Alirzaj\ElasticsearchBuilder\Query\Query;
use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elasticsearch\Client;

it('can make a nested query and set ignore_unmapped and score mode to default values', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'nested' => [
                        'ignore_unmapped' => false,
                        'path' => 'driver.vehicle',
                        'score_mode' => 'avg',
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

    Blog::elasticsearchQuery()->nested(
        fn (Query $query) => $query->match('field', 'test', 'aaa'),
        'driver.vehicle'
    );

    expect(true)->toBeTrue();
});

it('can build a nested query and set options to desired values', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'nested' => [
                        'ignore_unmapped' => true,
                        'path' => 'driver.vehicle',
                        'score_mode' => 'sum',
                        'query' => [
                            'match' => [
                                'field' => [
                                    'analyzer' => 'aaa',
                                    'query' => 'test',
                                    'fuzziness' => 'AUTO',
                                ],
                            ],
                            'nested' => [
                                'query' => [
                                    'bool' => [
                                        'must' => [
                                            ['exists' => 'text'],
                                        ],
                                    ],
                                ],
                                'ignore_unmapped' => false,
                                'path' => 'obj.1',
                                'score_mode' => 'avg',
                            ],
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

    Blog::elasticsearchQuery()->nested(
        fn (Query $query) => $query
            ->match('field', 'test', 'aaa')
            ->nested(
                fn (Query $query) => $query
                    ->boolean(
                        fn (Must $query) => $query->exists('text')
                    ),
                'obj.1'
            ),
        'driver.vehicle',
        'sum',
        true
    );

    expect(true)->toBeTrue();
});
