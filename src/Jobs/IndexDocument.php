<?php

namespace Alirzaj\ElasticsearchBuilder\Jobs;

use Elastic\Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IndexDocument implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $name;
    /** @var mixed */
    private $id;
    private array $document;

    public function __construct(string $name, $id, array $document)
    {
        $this->name = $name;
        $this->id = $id;
        $this->document = $document;

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
            'refresh' => true,
        ]);
    }
}
