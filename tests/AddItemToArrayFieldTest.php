<?php

use Elastic\Elasticsearch\Client;
use Alirzaj\ElasticsearchBuilder\Jobs\AddItemToArrayField;

it('can add an item to an array field', function () {
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
                    'params' => ['item' => 'php']
                ],
            ],
        ])
        ->andReturn([]);

    AddItemToArrayField::dispatchSync(
        'blogs',
        10,
        'tags',
        'php',
    );

    expect(true)->toBeTrue();
});
