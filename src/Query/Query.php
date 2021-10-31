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

    public function boolean(Closure ...$queries): Query
    {
        foreach ($queries as $query) {
            $this->add('bool', app()->call($query)->toArray());
        }

        return $this;
    }

    public function match(
        string           $field,
        string|int|float $value,
        string           $analyzer = null,
        string           $fuzziness = 'AUTO'
    ): Query
    {
        $this->add('match', [
            $field => array_filter([
                'analyzer' => $analyzer,
                'query' => $value,
                'fuzziness' => $fuzziness,
            ]),
        ]);

        return $this;
    }

    public function multiMatch(
        array            $fields,
        string|int|float $value,
        string           $analyzer = null,
        string           $fuzziness = 'AUTO',
        string           $type = 'best_fields'
    ): Query
    {
        $this->add(
            'multi_match',
            array_filter([
                'analyzer' => $analyzer,
                'query' => $value,
                'fuzziness' => $fuzziness,
                'type' => $type,
                'fields' => $fields,
            ])
        );

        return $this;
    }

    private function add(string $name, array $query): void
    {
        in_array($this::class, $this->compounds) ?
            $this->query[][$name] = $query :
            $this->query[$name] = $query;
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
        /**
         * @var Model $model
         */
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
