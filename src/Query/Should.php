<?php

namespace Alirzaj\ElasticsearchBuilder\Query;

class Should extends Query
{
    public function toArray()
    {
        return [
            'should' => $this->query,
        ];
    }
}
