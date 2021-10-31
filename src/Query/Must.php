<?php

namespace Alirzaj\ElasticsearchBuilder\Query;

class Must extends Query
{
    public function toArray()
    {
        return [
            'must' => $this->query,
        ];
    }
}
