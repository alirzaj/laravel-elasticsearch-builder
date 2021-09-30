<?php

namespace Alirzaj\ElasticsearchBuilder;

use Illuminate\Database\Eloquent\Model;

abstract class Index
{
    /**
     * name of the index
     *
     * @var string
     */
    public string $name;

    /**
     * properties (columns) and their types
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/explicit-mapping.html
     * 'title' => 'text' means that title field in the index will have text type
     *
     * @var array|string[]
     */
    public array $properties = [];

    /**
     * other definitions of a field
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     *
     * @var array
     */
    public array $fields = [];

    /**
     * define and config analyzers for index
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-custom-analyzer.html
     * @var array|\string[][]
     */
    public array $analyzers = [];

    /**
     * define & config tokenizers for index
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-custom-analyzer.html
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analysis-tokenizers.html
     * @var array
     */
    public array $tokenizers = [];

    public function getName(): string
    {
        return $this->name ?? strtolower(class_basename($this));
    }

    public abstract function toIndex(Model $model): array;
}
