<?php

namespace Alirzaj\ElasticsearchBuilder\Query;

class MustNot extends Query
{
    public function toArray()
    {
        return [
            'must_not' => $this->query,
        ];
    }
}
