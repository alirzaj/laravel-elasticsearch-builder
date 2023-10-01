<?php

namespace Alirzaj\ElasticsearchBuilder\Jobs;

use Elastic\Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class BulkIndexDocuments implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private string $name, private array $documents)
    {
        $this->onQueue(config('elasticsearch.queue'));
    }

    public function handle(Client $client): void
    {
        if (blank($this->documents)) {
            return;
        }

        $bulkBody = [];
        foreach ($this->documents as $document) {
            if (!Arr::has($document, 'id') || blank($document['id'])) {
                throw new \Exception('missing id parameter in bulk index documents');
            }

            $bulkBody[] = ['index' => ['_index' => $this->name, '_id' => $document['id']],];
            $bulkBody[] = $document;
        }

        $client->bulk(['refresh' => true, 'body' => $bulkBody,]);
    }
}
