<?php

use Alirzaj\ElasticsearchBuilder\Jobs\RemoveItemFromNestedField;
use Alirzaj\ElasticsearchBuilder\Jobs\UpdateNestedItemByCondition;
use Elasticsearch\Client;

it('can remove an item from a nested field', function () {
    $this->withoutExceptionHandling();

    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('update')
        ->with([
            'refresh' => true,
            'retry_on_conflict' => 3,
            'index' => 'blogs',
            'id' => 10,
            'body' => [
                'script' => [
                    'source' => 'ctx._source.tags.removeIf(r -> r.id == params.remove_param)',
                    'params' => ['remove_param' => 20]
                ],
            ],
        ])
        ->andReturn([]);

    /**
     * In tags field, remove all sub-fields with the key of id and value of 20
     */
    RemoveItemFromNestedField::dispatch('blogs', 10, 'tags', 'id', 20);

    expect(true)->toBeTrue();
});
