<?php

namespace Alirzaj\ElasticsearchBuilder\Testing;

use Alirzaj\ElasticsearchBuilder\Index;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Common\Exceptions\BadRequest400Exception;

/** @mixin \Illuminate\Foundation\Testing\TestCase */

trait RefreshElasticsearchDatabase
{
    protected static bool $indicesPopulated = false;

    public function createIndices(): void
    {
        if (! self::$indicesPopulated) {
            self::$indicesPopulated = resolve(Client::class)
                ->indices()
                ->exists([
                    'index' => collect(config('elasticsearch.indices'))
                        ->map(fn(string $index) => new $index())
                        ->map(fn(Index $index) => $index->getName())
                        ->toArray()

                ])
                ->asBool();
        }

        if (self::$indicesPopulated) {
            return;
        }

        try {
            $this->artisan('elastic:create-indices');
        } catch (BadRequest400Exception $ex) {
            if (stripos($ex->getMessage(), 'resource_already_exists_exception') != false) {
                $this->artisan('elastic:delete-indices');

                $this->artisan('elastic:create-indices');
            }
        }

        self::$indicesPopulated = true;
    }

    public function clearElasticsearchData(): void
    {
        $data = [
            'refresh' => true,
            'index' => collect(config('elasticsearch.indices'))
                ->map(fn(string $index) => new $index())
                ->map(fn(Index $index) => $index->getName())
                ->toArray(),
            'body' => [
                'query' => [
                    'match_all' => ['boost' => 1],
                ],
            ],
        ];

        resolve(Client::class)->deleteByQuery($data);
    }
}
