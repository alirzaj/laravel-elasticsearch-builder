<?php

namespace Alirzaj\ElasticsearchBuilder\Query;

use Alirzaj\ElasticsearchBuilder\Query\Aggregation\Aggregation;
use Closure;
use Elastic\Elasticsearch\Client;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;

class Query
{
    use Conditionable;

    private array $params = ['body' => []];
    private array $compounds = [
        Should::class,
        Filter::class,
        MustNot::class,
        Must::class,
    ];
    protected array $query;
    protected array $aggregations = [];

    public function addIndex(string $index): Query
    {
        if (class_exists($index)) {
            $this->params['index'][] = (new $index())->getName();

            return $this;
        }

        $this->params['index'][] = $index;

        return $this;
    }

    public function boost(array $indicesBoost) : Query
    {
        $this->params['body']['indices_boost'] = $indicesBoost;

        return $this;
    }

    public function searchType(string $type): Query
    {
        $this->params['search_type'] = $type;

        return $this;
    }

    public function only(string|array $fields): Query
    {
        $this->params['body']['_source'] = $fields;

        return $this;
    }

    public function size(int $size): Query
    {
        $this->params['body']['size'] = $size;

        return $this;
    }

    public function from(int $from): Query
    {
        $this->params['body']['from'] = $from;

        return $this;
    }

    //TODO add options (minimum should match)
    public function boolean(Closure ...$queries): Query
    {
        $booleanQuery = [];
        foreach ($queries as $query) {
            $booleanQuery += app()->call($query)->toArray();
        }

        $this->add('bool', $booleanQuery);

        return $this;
    }

    public function aggregation(string $name, Aggregation $aggregation) : Query
    {
        $this->aggregations[$name] = $aggregation->toArray();

        return $this;
    }

    public function toArray(): array
    {
        return $this->query;
    }

    public function match(
        string           $field,
        float|int|string $value,
        string           $analyzer = null,
        string           $fuzziness = 'AUTO'
    ): Query
    {
        return $this->add('match', [
            $field => array_filter([
                'analyzer' => $analyzer,
                'query' => $value,
                'fuzziness' => $fuzziness,
            ]),
        ]);
    }

    public function term(string $field, float|int|string|bool $value, float|int $boost = 1.0): Query
    {
        return $this->add('term', [
            $field => [
                'value' => $value,
                'boost' => $boost,
            ],
        ]);
    }

    public function terms(string $field, array $values): Query
    {
        return $this->add('terms', [$field => $values]);
    }

    public function exists(string $field): Query
    {
        return $this->add('exists', ['field' => $field]);
    }

    public function multiMatch(
        array            $fields,
        float|int|string $value,
        string           $analyzer = null,
        string           $fuzziness = 'AUTO',
        string           $type = 'best_fields'
    ): Query
    {
        return $this->add(
            'multi_match',
            array_filter([
                'analyzer' => $analyzer,
                'query' => $value,
                'fuzziness' => $fuzziness,
                'type' => $type,
                'fields' => $fields,
            ])
        );
    }

    //TODO add options (tie breaker)
    public function disjunctionMax(Closure ...$queries): self
    {
        return $this->add(
            'dis_max',
            [
                'queries' => collect($queries)
                    ->map(fn($query) => app()->call($query)->toArray())
                    ->toArray(),
            ]
        );
    }

    public function nested(Closure $query, string $path, string $scoreMode = 'avg', bool $ignoreUnmapped = false): self
    {
        return $this->add(
            'nested',
            [
                'query' => app()->call($query)->toArray(),
                'path' => $path,
                'ignore_unmapped' => $ignoreUnmapped,
                'score_mode' => $scoreMode,
            ]
        );
    }

    public function matchAll(float|int $boost = 1.0): self
    {
        return $this->add('match_all', ['match_all' => ['boost' => $boost]]);
    }

    public function range(
        string           $field,
        mixed            $gte = null,
        mixed            $lte = null,
        mixed            $gt = null,
        mixed            $lt = null,
        float|int        $boost = null,
    ): Query
    {
        return $this->add('range', [
            $field => array_filter([
                'gte' => $gte,
                'lte' => $lte,
                'gt' => $gt,
                'lt' => $lt,
                'boost' => $boost
            ]),
        ]);
    }

    private function add(string $name, array $query): self
    {
        in_array(get_class($this), $this->compounds)
            ? $this->query[][$name] = $query
            : $this->query[$name] = $query;

        return $this;
    }

    public function get(): Collection
    {
        if (blank($this->aggregations)) {
            return collect($this->executeQuery()['hits']['hits'])->pluck('_source', '_id');
        }

        return collect($this->executeQuery());
    }

    public function find(mixed $id) : array
    {
        return resolve(Client::class)
            ->get(['index' => $this->params['index'], 'id' => $id])['_source'] ?? [];
    }

    public function count() : int
    {
        $this->params['body']['query'] = $this->query;

        return resolve(Client::class)->count($this->params)['count'];
    }

    public function hydrate(): EloquentCollection
    {
        $indices = collect(config('elasticsearch.indices'))
            ->map(fn($index) => (new $index())->getName())
            ->flip();

        return EloquentCollection::make($this->executeQuery()['hits']['hits'])->map(
            fn(array $hit) => $this->toModel($indices[$hit['_index']], $hit['_id'], $hit['_source'])
        );
    }

    private function toModel(string $model, mixed $id, array $source): Model
    {
        /** @var Model $model */
        $model = new $model();

        $model->exists = true;

        return $model->forceFill($source + [$model->getKeyName() => $id]);
    }

    private function executeQuery(): \ArrayAccess
    {
        $this->params['body']['query'] = $this->query;

        if (filled($this->aggregations)) {
            $this->params['body']['aggs'] = $this->aggregations;
        }

        return resolve(Client::class)->search($this->params);
    }

    public function toRaw(): array
    {
        $this->params['body']['query'] = $this->query;

        return $this->params;
    }

    public function dd(): void
    {
        dd($this->query);
    }

    public function dump(): Query
    {
        dump($this->query);

        return $this;
    }
}
