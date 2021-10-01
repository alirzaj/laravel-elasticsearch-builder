<?php

namespace Alirzaj\ElasticsearchBuilder\Commands;

use Alirzaj\ElasticsearchBuilder\Index;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Illuminate\Console\Command;

class CreateIndices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:create-indices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create all defined indexes in elasticsearch';

    private Client $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        parent::__construct();

        $this->client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        collect(config('elasticsearch.indices'))
            ->map(fn (string $index) => new $index())
            ->each(function (Index $index) {
                $this->info("creating {$index->getName()}");

                try {
                    $this->createIndex($index);
                } catch (BadRequest400Exception) {
                    $this->alert("{$index->getName()} already exists.");
                }

                $this->info("created {$index->getName()}");
            });

        return 0;
    }

    private function createIndex(Index $index): void
    {
        $this->client->indices()->create([
            'index' => $index->getName(),
            'body' => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => $index->analyzers ?? [],
                        'tokenizer' => $index->tokenizers ?? [],
                    ],
                ],
                'mappings' => [
                    'properties' => collect($index->properties)
                        ->map(fn (string $type, string $name) => [
                            'type' => $type,
                            'fields' => $index->fields[$name] ?? [],
                        ])
                        ->toArray(),
                ],
            ],
        ]);
    }
}
