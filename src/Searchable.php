<?php

namespace Alirzaj\ElasticsearchBuilder;

use Alirzaj\ElasticsearchBuilder\Jobs\IndexDocument;
use Alirzaj\ElasticsearchBuilder\Jobs\UpdateDocument;
use Alirzaj\ElasticsearchBuilder\Query\Query;
use Illuminate\Database\Eloquent\Model;

trait Searchable
{
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    public static function bootSearchable()
    {
        static::created(function (Model $model) {
            IndexDocument::dispatch(
                $model->elasticsearchIndex()->getName(),
                $model->getKey(),
                $model->toIndex()
            );
        });

        static::updated(function (Model $model) {
            UpdateDocument::dispatch(
                $model->elasticsearchIndex()->getName(),
                $model->getKey(),
                $model->toIndex()
            );
        });
    }

    public function toIndex(): array
    {
        return $this->toArray();
    }

    protected function elasticsearchIndex(): Index
    {
        $index = config('elasticsearch.indices.'.get_class($this));

        return new $index;
    }

    public static function elasticsearchQuery(): Query
    {
        return (new Query())->addIndex(config('elasticsearch.indices.' . self::class));
    }
}
