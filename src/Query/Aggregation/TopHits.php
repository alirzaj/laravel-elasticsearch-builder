<?php

namespace Alirzaj\ElasticsearchBuilder\Query\Aggregation;

class TopHits extends Aggregation
{
    protected array $sorts = [];

    public function __construct(protected array $source = [], protected int $size = 3)
    {
    }

    public function sort(string $field, string $direction = 'desc'): static
    {
        $this->sorts[] = [
            $field => [
                'order' => $direction,
            ],
        ];

        return $this;
    }

    public function toRaw(): array
    {
        $raw = ['size' => $this->size];

        if (filled($this->sorts)) {
            $raw['sort'] = $this->sorts;
        }

        if (filled($this->source)) {
            $raw['_source']['includes'] = $this->source;
        }

        return $raw;
    }
}
