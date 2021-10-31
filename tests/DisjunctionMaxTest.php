<?php

use Alirzaj\ElasticsearchBuilder\Query\Query;
use Alirzaj\ElasticsearchBuilder\Query\Should;
use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elasticsearch\Client;

it('can build disjunction max query', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'dis_max' => [
                        'queries' => [
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
                                    'c' => [
                                        'value' => 'd',
                                        'boost' => 1.0,
                                    ],
                                ],
                            ],
                            [
                                'bool' => [
                                    'should' => [
                                        [
                                            'exists' => [
                                                'field' => 'f',
                                            ],
                                        ],
                                        [
                                            'term' => [
                                                'g' => [
                                                    'value' => 'h',
                                                    'boost' => 1.0,
                                                ],
                                            ],
                                        ],
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
        ->disjunctionMax(
            fn (Query $query) => $query->match('a', 'b'),
            fn (Query $query) => $query->term('c', 'd'),
            fn (Query $query) => $query->boolean(
                fn (Should $should) => $should
                    ->exists('f')
                    ->term('g', 'h')
            ),
        )
        ->get();
});
