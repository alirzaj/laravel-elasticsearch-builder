<?php

namespace Alirzaj\ElasticsearchBuilder\Query\Aggregation;

class Terms extends Aggregation
{
    public function __construct(protected string $field)
    {
    }

    public function toRaw(): array
    {
       return ['field' => $this->field];
    }
}
