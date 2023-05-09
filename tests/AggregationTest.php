<?php

use Alirzaj\ElasticsearchBuilder\Query\Aggregation\Terms;
use Alirzaj\ElasticsearchBuilder\Query\Aggregation\TopHits;
use Alirzaj\ElasticsearchBuilder\Query\Must;
use Alirzaj\ElasticsearchBuilder\Query\Query;
use Alirzaj\ElasticsearchBuilder\Query\Should;
use Elasticsearch\Client;

it('can build aggregation (aggs) query', function () {


    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['posts', 'users'],
            'body' => [
                'size' => 0,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    'published' => [
                                        'value' => true,
                                        'boost' => 1.0
                                    ],
                                ],
                            ],
                        ],
                        'should' => [
                            [
                                'term' => [
                                    'title' => [
                                        'value' => 'aaaa',
                                        'boost' => 1.0
                                    ]
                                ]
                            ],
                            [
                                'term' => [
                                    'title' => [
                                        'value' => 'bbbb',
                                        'boost' => 1.0
                                    ]
                                ]
                            ]
                        ],
                    ],
                ],
                'aggs' => [
                    'types' => [
                        'terms' => [
                            'field' => 'morph_type',
                        ],
                        'aggs' => [
                            'latest' => [
                                'top_hits' => [
                                    'sort' => [
                                        [
                                            'created_at' => [
                                                'order' => 'desc',
                                            ],
                                        ],
                                    ],
                                    '_source' => [
                                        'includes' => ['title', 'morph_type', 'created_at'],
                                    ],
                                    'size' => 3,
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

    (new Query())
        ->addIndex('posts')
        ->addIndex('users')
        ->size(0)
        ->boolean(
            fn(Must $must) => $must->term('published', true),
            fn(Should $should) => $should
                ->term('title', 'aaaa')
                ->term('title', 'bbbb'),
        )
        ->aggregation('types', (new Terms('morph_type'))
            ->aggregation('latest', (new TopHits(source: ['title', 'morph_type', 'created_at'], size: 3))
                ->sort(field: 'created_at', direction: 'desc')
            )
        )
        ->get();

    expect(true)->toBeTrue();
});
