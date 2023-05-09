<?php

namespace Alirzaj\ElasticsearchBuilder\Jobs;

use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddItemToArrayField implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private string $name,
        private mixed  $id,
        private string $field,
        private string  $item
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
                    'source' => "if(ctx._source.$this->field == null){ ctx._source.$this->field = []; } ctx._source.$this->field.add(params.item)",
                    'params' => [
                        'item' => $this->item,
                    ],
                ],
            ],
        ]);
    }
}
