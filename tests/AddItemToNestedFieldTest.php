<?php

use Alirzaj\ElasticsearchBuilder\Jobs\AddItemToNestedField;
use Elasticsearch\Client;

it('can add an item to a nested field', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('update')
        ->with([
            'refresh' => true,
            'retry_on_conflict' => 3,
            'index' => 'blogs',
            'id' => 10,
            'body' => [
                'script' => [
                    'source' => 'if(ctx._source.tags == null){ ctx._source.tags = []; } ctx._source.tags.add(params.item)',
                    'params' => ['item' => ['id' => 20, 'name' => 'php']]
                ],
            ],
        ])
        ->andReturn([]);

    AddItemToNestedField::dispatchSync(
        'blogs',
        10,
        'tags',
        ['id' => 20, 'name' => 'php'],
    );

    expect(true)->toBeTrue();
});
