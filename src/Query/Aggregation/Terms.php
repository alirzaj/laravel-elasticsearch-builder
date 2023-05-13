<?php

namespace Alirzaj\ElasticsearchBuilder\Query\Aggregation;

class Terms extends Aggregation
{
    public function __construct(protected string $field, public int|null $size = null)
    {
    }

    public function toRaw(): array
    {
        $raw = ['field' => $this->field];

        if (!is_null($this->size)) {
            $raw['size'] = $this->size;
        }

        return $raw;
    }
}
