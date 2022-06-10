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

    private string $name;
    private string $field;
    /** @var mixed */
    private $search;

    public function __construct(string $name, string $field, $search)
    {
        $this->name = $name;
        $this->field = $field;
        $this->search = $search;

        $this->onQueue(config('elasticsearch.queue'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Client $client)
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
