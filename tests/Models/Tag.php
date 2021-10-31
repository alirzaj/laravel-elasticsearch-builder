<?php

namespace Alirzaj\ElasticsearchBuilder\Tests\Models;

use Alirzaj\ElasticsearchBuilder\Jobs\RemoveArrayItem;
use Alirzaj\ElasticsearchBuilder\Jobs\UpdateArrayItem;
use Alirzaj\ElasticsearchBuilder\Jobs\UpdateDocument;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public $table = 'tags';
    protected $guarded = [];
    public $timestamps = false;

    public static function booted()
    {
        static::created(function (Tag $tag) {
            UpdateDocument::dispatch(
                'blogs',
                $tag->blog_id,
                [
                    'tags' => static::query()
                        ->where('blog_id', $tag->blog_id)
                        ->pluck('tag_name')
                        ->toArray(),
                ]
            );
        });

        static::updated(function (Tag $tag) {
            UpdateArrayItem::dispatch('blogs', 'tags', $tag->getOriginal('tag_name'), $tag->tag_name);
        });

        static::deleted(function (Tag $tag) {
            RemoveArrayItem::dispatch('blogs', 'tags', $tag->tag_name);
        });
    }

    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id', 'id');
    }
}
