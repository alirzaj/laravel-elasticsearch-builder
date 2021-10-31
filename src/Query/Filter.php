<?php

namespace Alirzaj\ElasticsearchBuilder\Query;

class Filter extends Query
{
    public function toArray()
    {
        return [
            'filter' => $this->query,
        ];
    }
}
