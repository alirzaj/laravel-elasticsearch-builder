<?php

namespace Alirzaj\ElasticsearchBuilder\Jobs;

use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateNestedItemByCondition implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private string $name,
        private mixed  $id,
        private string $field,
        private array  $conditions,
        private array  $document
    )
    {
        $this->onQueue(config('elasticsearch.queue'));
    }

    public function handle(Client $client): void
    {
        $client->update([
            'retry_on_conflict' => config('elasticsearch.retry_on_conflict'),
            'refresh' => true,
            'index' => $this->name,
            'id' => $this->id,
            'body' => [
                'script' => [
                    'source' => "def targets = ctx._source.$this->field.findAll(f -> "
                        . $this->createConditions($this->conditions)
                        . ' for(t in targets) { '
                        . $this->setValueForMultipleFieldsScript()
                        . ' }',
                    'params' => array_merge($this->conditions, $this->document),
                ],
            ],
        ]);
    }

    private function createConditions(array $conditions): string
    {
        $totalConditions = count($conditions);
        $conditionNumber = 1;
        $sourceScript = '';

        foreach ($conditions as $key => $value) {
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

    private function setValueForMultipleFieldsScript(): string
    {
        $result = '';

        foreach ($this->document as $field => $value) {
            $result .= "t.$field = params.$field; ";
        }

        return $result;
    }
}
