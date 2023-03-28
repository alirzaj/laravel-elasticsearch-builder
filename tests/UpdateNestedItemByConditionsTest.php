<?php

use Alirzaj\ElasticsearchBuilder\Jobs\UpdateNestedItemByCondition;
use Elasticsearch\Client;

it('can update an item of a nested field that satisfies some conditions in a document', function () {
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('update')
        ->with([
            'retry_on_conflict' => 3,
            'refresh' => true,
            'index' => 'blogs',
            'id' => 10,
            'body' => [
                'script' => [
                    'source' => 'def targets = ctx._source.tags.findAll(f -> (f.id == params.id)); for(t in targets) { t.id = params.id; t.name = params.name;  }',
                    'params' => ['id' => 20, 'name' => 'new-php']
                ],
            ],
        ])
        ->andReturn([]);

    UpdateNestedItemByCondition::dispatchSync(
        'blogs',
        10,
        'tags',
        ['id' => 20], // in document, we have a [nested] tags field. now we are looking for the ones with id of 20
        /**
         * we want all of those items having above condition to be updated to this item
         * note that if you have id key in conditions, and id key in document parameter, the values must be the same
         * in other words condition's value must not change in update.
         * in this example we find the tag via id and update its name. we couldn't find it via old name and set a new name
         */
        ['id' => 20, 'name' => 'new-php']
    );

    expect(true)->toBeTrue();
});
