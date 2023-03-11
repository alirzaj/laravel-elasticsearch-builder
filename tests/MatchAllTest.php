<?php

use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elasticsearch\Client;

it('can build a match_all query with default parameters', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'match_all' => ['boost' => 1],
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

    blog::elasticsearchQuery()->matchAll();

    expect(true)->toBeTrue();
});

it('can build a match_all query and set boost option', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('search')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'match_all' => ['boost' => 1.7],
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

    blog::elasticsearchQuery()->matchAll(1.7);

    expect(true)->toBeTrue();
});
