<?php

namespace Alirzaj\ElasticsearchBuilder\Query;

class Should extends Query
{
    public function toArray(): array
    {
        return [
            'should' => $this->query,
        ];
    }
}
