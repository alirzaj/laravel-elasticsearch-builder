<?php

namespace Alirzaj\ElasticsearchBuilder\Tests\Models;

use Alirzaj\ElasticsearchBuilder\Searchable;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use Searchable;

    public $table = 'blogs';
    protected  $guarded = [];
    public $timestamps = false;
}
