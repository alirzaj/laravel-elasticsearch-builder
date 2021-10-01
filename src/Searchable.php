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
            $index = $model->elasticsearchIndex();

            IndexDocument::dispatch($index->getName(), $model->getKey(), $index->toIndex($model));
        });

        static::updated(function (Model $model){
            $index = $model->elasticsearchIndex();

            UpdateDocument::dispatch($index->getName(), $model->getKey(), $index->toIndex($model));
        });
    }

    protected function elasticsearchIndex(): Index
    {
        return new (config('elasticsearch.indices')[$this::class]);
    }

    public static function elasticsearchQuery(): Query
    {
        return (new Query())->addIndex(config('elasticsearch.indices.' . self::class));
    }
}
