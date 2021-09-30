<?php

namespace Alirzaj\ElasticsearchBuilder\Jobs;

use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IndexDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private string $name, private mixed $id, private array $document)
    {
        $this->onQueue(config('elasticsearch.queue'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Client $client)
    {
        $client->index([
            'index' => $this->name,
            'id' => $this->id,
            'body' => $this->document,
            'refresh' => true
        ]);
    }
}
