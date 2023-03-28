<?php

use Alirzaj\ElasticsearchBuilder\Jobs\DeleteDocument;
use Elasticsearch\Client;

it('can delete a document', function (){
    \Pest\Laravel\mock(Client::class)
        ->shouldReceive('delete')
        ->with([
            "refresh" => true,
            "index" => "blogs",
            "id" => 10
        ])
        ->andReturn([]);

    DeleteDocument::dispatchSync('blogs',10);

    expect(true)->toBeTrue();
});
