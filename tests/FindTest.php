<?php

use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elastic\Elasticsearch\Client;

it('can find a documet by its id', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('get')
        ->with([
            'index' => ['blogs'],
            'id' => 10
        ])
        ->andReturn([
            '_source' => [],
        ]);

    Blog::elasticsearchQuery()->find(10);

    expect(true)->toBeTrue();
});
