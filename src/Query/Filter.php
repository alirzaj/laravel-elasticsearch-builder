<?php

namespace Alirzaj\ElasticsearchBuilder\Query;

class Filter extends Query
{
    public function toArray(): array
    {
        return [
            'filter' => $this->query,
        ];
    }
}
