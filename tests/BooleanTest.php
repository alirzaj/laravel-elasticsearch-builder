<?php

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
                                    'fields' => ['c','d'],
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
                ->match('z', 'x', analyzer: 'rrr')
                ->multiMatch(['c', 'd'], 'e')
        )
        ->get();
});
