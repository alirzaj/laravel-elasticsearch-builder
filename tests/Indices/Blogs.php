<?php

namespace Alirzaj\ElasticsearchBuilder\Tests\Indices;

use Alirzaj\ElasticsearchBuilder\Index;
use Illuminate\Database\Eloquent\Model;

class Blogs extends Index
{
    public array $properties = [
        'text' => 'text',
        'title' => 'keyword',
        'description' => 'completion',
    ];

    public function toIndex(Model $model): array
    {
        return [
            'title' => $model->title,
            'text' => $model->text,
            'description' => $model->description,
        ];
    }
}
