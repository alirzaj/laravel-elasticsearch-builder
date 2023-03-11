<?php

namespace Alirzaj\ElasticsearchBuilder\Tests\Indices;

use Alirzaj\ElasticsearchBuilder\Index;

class Blogs extends Index
{
    public array $propertyTypes = [
        'text' => 'text',
        'title' => 'keyword',
        'description' => 'completion',
        'tags' => 'keyword',
    ];
}
