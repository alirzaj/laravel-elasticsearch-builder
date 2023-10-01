<?php

use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elastic\Elasticsearch\Client;

it('can build count query', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('count')
        ->with([
            'index' => ['blogs'],
            'body' => [
                'query' => [
                    'term' => [
                        'title' => [
                            'value' => 'aaa',
                            'boost' => 1.0,
                        ],
                    ],
                ],
            ],
        ])
        ->andReturn([
            "count" => 10,
            "_shards" => [
                "total" => 10,
                "successful" => 1,
                "skipped" => 0,
                "failed" => 0,
            ]
        ]);

    $count = Blog::elasticsearchQuery()->term('title','aaa')->count();

    expect($count)->toEqual(10);
});
