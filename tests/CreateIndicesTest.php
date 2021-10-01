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
                            ],
                            'tokenizer' => [
                                'hashtag_tokenizer' => [
                                    'type' => 'pattern',
                                    'pattern' => '#\S+',
                                    'group' => 0,
                                ],
                            ],
                        ],
                    ],
                    'mappings' => [
                        'properties' => [
                            'text' => [
                                'type' => 'text',
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
                    ],
                ],
            ],
        ]);

    artisan('elastic:create-indices');
});
