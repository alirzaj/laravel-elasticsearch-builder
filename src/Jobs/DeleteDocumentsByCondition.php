<?php

namespace Alirzaj\ElasticsearchBuilder\Jobs;

use Alirzaj\ElasticsearchBuilder\Query\Filter;
use Alirzaj\ElasticsearchBuilder\Query\MustNot;
use Alirzaj\ElasticsearchBuilder\Query\Query;
use Elastic\Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class DeleteDocumentsByCondition implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private string $name, private array $conditions)
    {
        $this->onQueue(config('elasticsearch.queue'));
    }

    public function handle(Client $client): void
    {
        $query = (new Query())
            ->boolean(
                ...collect($this->conditions)
                ->map(fn($value, string $field) => is_null($value)
                    ? fn(MustNot $mustNot) => $mustNot->exists($field)
                    : fn(Filter $filter) => $filter->terms($field, Arr::wrap($value))
                )
                ->values()
                ->toArray()
            );

        $client->deleteByQuery([
            'refresh' => true,
            'index' => $this->name,
            'body' => [
                'query' => $query->toRaw()['body']['query'],
            ],
        ]);
    }
}
