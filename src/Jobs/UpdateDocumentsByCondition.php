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

class UpdateDocumentsByCondition implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    public function __construct(
        private string $name,
        private array  $conditions,
        private array  $document,
        private array  $largeFields = []
    )
    {
        $this->onQueue(config('elasticsearch.queue'));
    }

    public function handle(Client $client): void
    {
        $query = (new Query())
            ->addIndex($this->name)
            ->only('')
            ->size(10000) //elasticsearch MAX_WINDOW_SIZE is 10000
            ->boolean(
                ...collect($this->conditions)
                ->map(fn($value, string $field) => is_null($value)
                    ? fn(MustNot $mustNot) => $mustNot->exists($field)
                    : fn(Filter $filter) => $filter->terms($field, Arr::wrap($value))
                )
                ->values()
                ->toArray()
            );

        /**
         * script size is limited in elasticsearch.
         * therefor we must use bulk operation
         * if we want to update a large field
         */
        if (blank($this->largeFields) || !Arr::hasAny($this->document, $this->largeFields)) {
            $client->updateByQuery([
                'refresh' => true,
                'index' => $this->name,
                'body' => [
                    'script' => [
                        'source' => $this->setValueForMultipleFieldsScript(),
                        'params' => $this->document,
                    ],
                    'query' => $query->toRaw()['body']['query'],
                ],
            ]);

            return;
        }

        $ids = $query->get()->keys()->toArray();

        if (blank($ids)) {
            return;
        }

        $bulkBody = [];
        foreach ($ids as $documentId) {
            $bulkBody[] = ['update' => ['_index' => $this->name, '_id' => $documentId]];
            $bulkBody[] = ['doc' => $this->document];
        }

        $client->bulk(['refresh' => true, 'body' => $bulkBody,]);
    }

    private function setValueForMultipleFieldsScript(): string
    {
        $result = '';

        foreach ($this->document as $field => $value) {
            $result .= "ctx._source.$field = params.$field; ";
        }

        return $result;
    }
}
