<?php

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use function Pest\Laravel\artisan;

it('can create indices', function () {
    $client = \Pest\Laravel\mock(Client::class);
    $indices = \Pest\Laravel\mock(IndicesNamespace::class);

    $client
        ->shouldReceive('indices')
        ->twice()
        ->andReturn($indices);

    $indices
        ->shouldReceive('create')
        ->once()
        ->with(
            [
                'index' => 'users_index',
                'body' => [
                    'settings' => [
                        'analysis' => [
                            'analyzer' => [
                                'hashtag' => [
                                    'type' => 'custom',
                                    'tokenizer' => 'hashtag_tokenizer',
                                    'filter' => ['lowercase'],
                                ],
                                'hashtag_2' => [
                                    'type' => 'custom',
                                    'tokenizer' => 'hashtag_tokenizer',
                                    'filter' => ['lowercase'],
                                ],
                            ],
                            'tokenizer' => [
                                'hashtag_tokenizer' => [
                                    'type' => 'pattern',
                                    'pattern' => '#\S+',
                                    'group' => 0,
                                ],
                            ],
                            'normalizer' => [
                                'my_normalizer' => [
                                    'type' => 'custom',
                                    'char_filter' => ['special_character_strip'],
                                    'filter' => ['lowercase',]
                                ],
                            ],
                            'filter' => [
                                '4_7_edge_ngram' => [
                                    'min_gram' => '4',
                                    'max_gram' => '7',
                                    'type' => 'edge_ngram',
                                    'preserve_original' => 'true',
                                ],
                            ],
                            'char_filter' => [
                                'special_character_strip' => [
                                    'type' => 'pattern_replace',
                                    'pattern' => '[._]',
                                ],
                            ]
                        ],
                    ],
                    'mappings' => [
                        'properties' => [
                            'text' => [
                                'type' => 'text',
                                'analyzer' => 'hashtag',
                                'search_analyzer' => 'hashtag_2',
                                'fields' => [
                                    'hashtags' => [
                                        'type' => 'text',
                                        'analyzer' => 'hashtag',
                                    ],
                                ],
                            ],
                            'user_id' => [
                                'type' => 'keyword',
                                'fields' => [],
                            ],
                            'ip' => [
                                'type' => 'ip',
                                'fields' => [],
                                'normalizer' => 'my_normalizer',
                            ],
                            'created_at' => [
                                'type' => 'date',
                                'fields' => [],
                            ],
                        ],
                    ],
                ],
            ],
        );

    $indices
        ->shouldReceive('create')
        ->once()
        ->with([
            'index' => 'blogs',
            'body' => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [],
                        'tokenizer' => [],
                        'normalizer' => [],
                        'filter' => [],
                        'char_filter' => []
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'text' => [
                            'type' => 'text',
                            'fields' => [],
                        ],
                        'title' => [
                            'type' => 'keyword',
                            'fields' => [],
                        ],
                        'description' => [
                            'type' => 'completion',
                            'fields' => [],
                        ],
                        'tags' => [
                            'type' => 'keyword',
                            'fields' => [],
                        ],
                    ],
                ],
            ],
        ]);

    artisan('elastic:create-indices');
});
