<?php

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use function Pest\Laravel\artisan;

it('can delete indices', function () {
    $client = \Pest\Laravel\mock(Client::class);
    $indices = \Pest\Laravel\mock(IndicesNamespace::class);

    $client
        ->shouldReceive('indices')
        ->once()
        ->andReturn($indices);

    $indices
        ->shouldReceive('delete')
        ->once()
        ->with(
            [
                'index' => ['blogs', 'users_index'],
                'ignore_unavailable' => true,
            ],
        );

    artisan('elastic:delete-indices');
});
