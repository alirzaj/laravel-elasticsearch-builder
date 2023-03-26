<?php

namespace Alirzaj\ElasticsearchBuilder\Jobs;

use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveArrayItem implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private string $name,private string $field, private mixed $search)
    {
        $this->onQueue(config('elasticsearch.queue'));
    }

    public function handle(Client $client): void
    {
        $client->updateByQuery([
            'refresh' => true,
            'index' => $this->name,
            'body' => [
                'script' => [
                    'source' => "ctx._source.$this->field.remove(ctx._source.$this->field.indexOf(params.search));",
                    'params' => [
                        'search' => $this->search,
                    ],
                ],
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => [$this->field => $this->search],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
