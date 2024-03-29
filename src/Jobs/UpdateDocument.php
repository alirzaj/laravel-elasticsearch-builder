<?php

namespace Alirzaj\ElasticsearchBuilder\Jobs;

use Elastic\Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateDocument implements ShouldQueue
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
        $client->update([
            'refresh' => true,
            'retry_on_conflict' => config('elasticsearch.retry_on_conflict'),
            'index' => $this->name,
            'id' => $this->id,
            'body' => [
                'doc' => $this->document,
            ],
        ]);
    }
}
