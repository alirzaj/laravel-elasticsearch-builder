<?php

namespace Alirzaj\ElasticsearchBuilder\Jobs;

use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteNestedFieldByCondition implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private string $name,
        private string $field,
       private array $conditions
    )
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
                    'source' => 'ctx._source.'.$this->field.'.removeIf(f -> '.$this->createConditions(),
                    'params' => $this->conditions,
                ],
                'query' => $this->createQuery(),
            ],
        ]);
    }

    private function createConditions(): string
    {
        $totalConditions = count($this->conditions);
        $conditionNumber = 1;
        $sourceScript = '';

        foreach ($this->conditions as $key => $value) {
            $sourceScript .= "(f.$key == params.$key)"
                . (
                $totalConditions > $conditionNumber
                    ? ' && '
                    : ');'
                );

            $conditionNumber += 1;
        }

        return $sourceScript;
    }

    private function createQuery(): array
    {
        $match = [];
        foreach ($this->conditions as $key => $value) {
            $match[] = [
                'match' => [
                    $this->field.'.'.$key => $value,
                ],
            ];
        }

        return [
            'nested' => [
                'ignore_unmapped' => true,
                'path' => $this->field,
                'score_mode' => 'sum',
                'query' => [
                    'bool' => [
                        'must' => $match,
                    ],
                ],
            ],
        ];
    }
}
