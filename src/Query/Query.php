<?php

namespace Alirzaj\ElasticsearchBuilder\Query;

use Closure;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Query
{
    private array $params = ['body' => []];
    private array $compounds = [
        Should::class,
        Filter::class,
        MustNot::class,
        Must::class,
    ];
    protected array $query;

    public function addIndex(string $index): Query
    {
        $this->params['index'][] = (new $index())->getName();

        return $this;
    }

    //TODO add options (minimum should match)
    public function boolean(Closure ...$queries): Query
    {
        foreach ($queries as $query) {
            $this->add('bool', app()->call($query)->toArray());
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->query;
    }

    public function match(
        string           $field,
        string|int|float $value,
        string           $analyzer = null,
        string           $fuzziness = 'AUTO'
    ): Query {
        return $this->add('match', [
            $field => array_filter([
                'analyzer' => $analyzer,
                'query' => $value,
                'fuzziness' => $fuzziness,
            ]),
        ]);
    }

    public function term(string $field, string|int|float $value, int|float $boost = 1.0): Query
    {
        return $this->add('term', [
            $field => [
                'value' => $value,
                'boost' => $boost,
            ],
        ]);
    }

    public function exists(string $field): Query
    {
        return $this->add('exists', ['field' => $field]);
    }

    public function multiMatch(
        array            $fields,
        string|int|float $value,
        string           $analyzer = null,
        string           $fuzziness = 'AUTO',
        string           $type = 'best_fields'
    ): Query {
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
                    ->map(fn ($query) => app()->call($query)->toArray())
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

    public function matchAll(int|float $boost = 1.0): self
    {
        return $this->add('match_all', ['match_all' => ['boost' => $boost]]);
    }

    private function add(string $name, array $query): self
    {
        in_array($this::class, $this->compounds) ?
            $this->query[][$name] = $query :
            $this->query[$name] = $query;

        return $this;
    }

    public function get(): Collection
    {
        return collect($this->executeQuery()['hits']['hits'])->pluck('_source');
    }

    public function hydrate(): EloquentCollection
    {
        $indices = collect(config('elasticsearch.indices'))
            ->map(fn ($index) => (new $index())->getName())
            ->flip();

        return EloquentCollection::make($this->executeQuery()['hits']['hits'])->map(
            fn (array $hit) => $this->toModel($indices[$hit['_index']], $hit['_id'], $hit['_source'])
        );
    }

    private function toModel(string $model, mixed $id, array $source): Model
    {
        /** @var Model $model */
        $model = new $model();

        $model->exists = true;

        return $model->forceFill($source + [$model->getKeyName() => $id]);
    }

    private function executeQuery(): array
    {
        $this->params['body']['query'] = $this->query;

        return resolve(Client::class)->search($this->params);
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
