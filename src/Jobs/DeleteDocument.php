<?php

namespace Alirzaj\ElasticsearchBuilder\Jobs;

use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteDocument implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private string $name, private mixed $id)
    {
        $this->onQueue(config('elasticsearch.queue'));
    }

    public function handle(Client $client): void
    {
        $client->delete([
            'refresh' => true,
            'index' => $this->name,
            'id' => $this->id,
        ]);
    }
}
