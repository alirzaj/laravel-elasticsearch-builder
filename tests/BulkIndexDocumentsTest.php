<?php

use Alirzaj\ElasticsearchBuilder\Jobs\BulkIndexDocuments;
use Alirzaj\ElasticsearchBuilder\Tests\Models\Blog;
use Elasticsearch\Client;
use Illuminate\Support\Str;

it('can index multiple documents at once', function () {
    $client = \Pest\Laravel\spy(Client::class);

    BulkIndexDocuments::dispatchSync(
        'blogs',
        [
            ['id' => 1, 'title' => 'abcd'],
            ['id' => 2, 'title' => 'efgh'],
        ]
    );

    $client
        ->shouldHaveReceived('bulk')
        ->once()
        ->with([
            'refresh' => true,
            'body' => [
                ['index' => ['_index' => 'blogs', '_id' => 1]],
                ['id' => 1, 'title' => 'abcd'],
                ['index' => ['_index' => 'blogs', '_id' => 2]],
                ['id' => 2, 'title' => 'efgh'],
            ],
        ]);
});

