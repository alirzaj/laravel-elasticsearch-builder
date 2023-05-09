<?php

namespace Alirzaj\ElasticsearchBuilder\Query\Aggregation;

use Illuminate\Support\Str;

abstract class Aggregation
{
    protected string $name;
    protected Aggregation $aggregation;

    public function aggregation(string $name, Aggregation $aggregation): static
    {
        $this->name = $name;
        $this->aggregation = $aggregation;

        return $this;
    }

    public function toArray(): array
    {
        $query = [
            Str::snake(class_basename($this)) => $this->toRaw()
        ];

        if (isset($this->aggregation)) {
            $query['aggs'][$this->name] = $this->aggregation->toArray();
        }

        return $query;
    }

    abstract public function toRaw(): array;
}
