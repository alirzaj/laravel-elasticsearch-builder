<?php

namespace Alirzaj\ElasticsearchBuilder\Query;

class BooleanOptions
{
    private int $minimumShouldMatch;

    public function minimumShouldMatch(int $value) : self
    {
        $this->minimumShouldMatch = $value;

        return $this;
    }

    public function toArray(): array
    {
        $options = [];

        if (isset($this->minimumShouldMatch)){
            $options['minimum_should_match'] = $this->minimumShouldMatch;
        }

        return $options;
    }
}
