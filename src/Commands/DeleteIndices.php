<?php

namespace Alirzaj\ElasticsearchBuilder\Commands;

use Alirzaj\ElasticsearchBuilder\Index;
use Elasticsearch\Client;
use Illuminate\Console\Command;

class DeleteIndices extends Command
{
    protected $signature = 'elastic:delete-indices';

    protected $description = 'Deletes all indices in elasticsearch';

    public function handle(Client $client): int
    {
        $client->indices()->delete([
            'index' => dump(collect(config('elasticsearch.indices'))
                ->map(fn(string $index) => new $index())
                ->map(fn(Index $index) => $index->getName())
                ->values()
                ->toArray()),
            'ignore_unavailable' => true,
        ]);

        return Command::SUCCESS;
    }
}
