<?php

namespace Alirzaj\ElasticsearchBuilder\Query;

class Must extends Query
{
    public function toArray(): array
    {
        return [
            'must' => $this->query,
        ];
    }
}
