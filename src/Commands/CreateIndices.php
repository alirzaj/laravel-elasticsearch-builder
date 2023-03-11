<?php

namespace Alirzaj\ElasticsearchBuilder\Commands;

use Alirzaj\ElasticsearchBuilder\Index;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

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

    public function handle(Client $client): int
    {
        collect(config('elasticsearch.indices'))
            ->map(fn(string $index) => new $index())
            ->each(function (Index $index) use ($client) {
                $this->info("creating {$index->getName()}");

                try {
                    $client->indices()->create($this->createIndexQuery($index));
                } catch (BadRequest400Exception $e) {
                    $this->alert("{$index->getName()} already exists.");
                }

                $this->info("created {$index->getName()}");
            });

        return Command::SUCCESS;
    }

    private function createIndexQuery(Index $index): array
    {
        return [
            'index' => $index->getName(),
            'body' => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => $index->analyzers ?? [],
                        'tokenizer' => $index->tokenizers ?? [],
                        'normalizer' => $index->normalizers ?? [],
                        'filter' => $index->tokenFilters ?? [],
                        'char_filter' => $index->characterFilters ?? []
                    ],
                ],
                'mappings' => [
                    'properties' => collect($index->propertyTypes)
                        ->map(fn(string $type, string $name) => [
                                'type' => $type,
                                'fields' => $index->fields[$name] ?? [],
                            ] + $this->addOptionalParameters($index, $name)
                        )
                        ->toArray(),
                ],
            ],
        ];
    }

    private function addOptionalParameters(Index $index, string $fieldName): array
    {
        $optionalParameters = [];

        if (Arr::has($index->propertyNormalizers, $fieldName)) {
            $optionalParameters['normalizer'] = $index->propertyNormalizers[$fieldName];
        }

        if (Arr::has($index->searchAnalyzers, $fieldName)) {
            $optionalParameters['search_analyzer'] = $index->searchAnalyzers[$fieldName];
        }

        if (Arr::has($index->propertyAnalyzers, $fieldName)) {
            $optionalParameters['analyzer'] = $index->propertyAnalyzers[$fieldName];
        }

        return $optionalParameters;
    }
}
