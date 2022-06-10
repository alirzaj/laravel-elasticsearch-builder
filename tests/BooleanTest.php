<?php

use Alirzaj\ElasticsearchBuilder\Query\Filter;
use Alirzaj\ElasticsearchBuilder\Query\Must;
use Alirzaj\ElasticsearchBuilder\Query\MustNot;
use Alirzaj\ElasticsearchBuilder\Query\Should;
use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elasticsearch\Client;

it('can build a should query', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'match' => [
                                    'a' => [
                                        'query' => 'b',
                                        'fuzziness' => 'AUTO',
                                    ],
                                ],
                            ],
                            [
                                'match' => [
                                    'z' => [
                                        'query' => 'x',
                                        'fuzziness' => 'AUTO',
                                        'analyzer' => 'rrr',
                                    ],
                                ],
                            ],
                            [
                                'multi_match' => [
                                    'fields' => ['c', 'd'],
                                    'query' => 'e',
                                    'fuzziness' => 'AUTO',
                                    'type' => 'best_fields',
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

    Blog::elasticsearchQuery()
        ->boolean(
            fn (Should $should) => $should
                ->match('a', 'b')
                ->match('z', 'x', 'rrr')
                ->multiMatch(['c', 'd'], 'e')
        )
        ->get();
});

it('can build a filter query', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'match' => [
                                    'a' => [
                                        'query' => 'b',
                                        'fuzziness' => 'AUTO',
                                    ],
                                ],
                            ],
                            [
                                'term' => [
                                    'z' => [
                                        'value' => 'x',
                                        'boost' => 1.0,
                                    ],
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

    Blog::elasticsearchQuery()
        ->boolean(
            fn (Filter $filter) => $filter
                ->match('a', 'b')
                ->term('z', 'x')
        )
        ->get();
});

it('can build a must not query', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            [
                                'match' => [
                                    'a' => [
                                        'query' => 'b',
                                        'fuzziness' => 'AUTO',
                                    ],
                                ],
                            ],
                            [
                                'term' => [
                                    'z' => [
                                        'value' => 'x',
                                        'boost' => 1.0,
                                    ],
                                ],
                            ],
                            [
                                'exists' => ['field' => 'description'],
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

    Blog::elasticsearchQuery()
        ->boolean(
            fn (MustNot $mustNot) => $mustNot
                ->match('a', 'b')
                ->term('z', 'x')
                ->exists('description')
        )
        ->get();
});

it('can build a must query', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'match' => [
                                    'a' => [
                                        'query' => 'b',
                                        'fuzziness' => 'AUTO',
                                    ],
                                ],
                            ],
                            [
                                'term' => [
                                    'z' => [
                                        'value' => 'x',
                                        'boost' => 1.0,
                                    ],
                                ],
                            ],
                            [
                                'exists' => ['field' => 'description'],
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

    Blog::elasticsearchQuery()
        ->boolean(
            fn (Must $must) => $must
                ->match('a', 'b')
                ->term('z', 'x')
                ->exists('description')
        )
        ->get();
});
