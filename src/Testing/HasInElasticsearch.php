<?php

namespace Alirzaj\ElasticsearchBuilder\Testing;

use Elasticsearch\Client;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Constraint\Constraint;

class HasInElasticsearch extends Constraint
{
    /**
     * The filters that will be used to search for target.
     */
    protected Collection $data;

    private Client $client;

    public function __construct(private $id, array $data, private bool $strict)
    {
        $this->data = collect($data);
        $this->client = resolve(Client::class);
    }

    /**
     * Check if the document found in the given index.
     *
     * @param string $index
     */
    public function matches($index): bool
    {
        $document = $this->client->get([
            'index' => $index,
            'id' => $this->id,
            '_source' => $this->data->keys()->toArray(),
        ])['_source'] ?? [];

        if (empty($document)) {
            return false;
        }

        $document = collect($document);

        return $document
                ->reject(function ($value, $key) {
                    if ($this->strict) {
                        return $value === $this->data[$key];
                    }

                    return $value == $this->data[$key];
                })
                ->isEmpty()
            &&
            $document
                ->diffKeys($this->data)
                ->isEmpty()
            &&
            $this
                ->data
                ->diffKeys($document)
                ->isEmpty();
    }

    /**
     * Get the description of the failure.
     *
     * @param string $index
     */
    public function failureDescription($index): string
    {
        return sprintf(
            "document in the index [%s] matches the attributes %s.\n\n",
            $index,
            $this->toString()
        );
    }

    /**
     * Returns a string representation of the object.
     */
    public function toString(): string
    {
        return $this
            ->data
            ->map(function ($item) {
                return $item instanceof Expression ? (string)$item : $item;
            })
            ->toJson(JSON_PRETTY_PRINT);
    }
}
