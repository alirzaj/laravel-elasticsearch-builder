<?php

namespace Alirzaj\ElasticsearchBuilder;

use Alirzaj\ElasticsearchBuilder\Jobs\IndexDocument;
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
            /**
             * @var $index \Alirzaj\ElasticsearchBuilder\Index
             */
            $index = new (config('elasticsearch.indices')[$model::class]);

            IndexDocument::dispatch($index->getName(), $model->getKey(), $index->toIndex($model));
        });
    }

    public static function elasticsearchQuery(): Query
    {
        return (new Query())->addIndex(config('elasticsearch.indices.' . self::class));
    }
}
