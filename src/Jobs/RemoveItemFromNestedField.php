<?php

namespace Alirzaj\ElasticsearchBuilder\Jobs;

use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveItemFromNestedField implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private string $name,
        private int    $id,
        private string $field,
        private string $itemKey,
        private mixed  $itemValue
    )
    {
        $this->onQueue(config('elasticsearch.queue'));
    }

    public function handle(Client $client): void
    {
        $client->update([
            'refresh' => true,
            'retry_on_conflict' => config('elasticsearch.retry_on_conflict'),
            'index' => $this->name,
            'id' => $this->id,
            'body' => [
                'script' => [
                    'source' => "ctx._source.$this->field.removeIf(r -> r.$this->itemKey == params.remove_param)",
                    'params' => [
                        'remove_param' => $this->itemValue,
                    ],
                ],
            ],
        ]);
    }
}
