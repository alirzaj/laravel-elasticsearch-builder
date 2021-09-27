<?php

namespace Alirzaj\ElasticsearchBuilder\Tests\Indices;

use Alirzaj\ElasticsearchBuilder\Index;

class Users extends Index
{
    public string $name = 'users_index';

    public array $properties = [
        'text' => 'text',
        'user_id' => 'keyword',
        'ip' => 'ip',
        'created_at' => 'date',
    ];

    public array $fields = [
        'text' => [
            'hashtags' => [
                'type' => 'text',
                'analyzer' => 'hashtag'
            ]
        ]
    ];

    public array $analyzers = [
        'hashtag' => [
            'type' => 'custom',
            'tokenizer' => 'hashtag_tokenizer',
            'filter' => ['lowercase']
        ]
    ];

    public array $tokenizers = [
        'hashtag_tokenizer' => [
            'type' => 'pattern',
            'pattern' => '#\S+',
            'group' => 0
        ]
    ];
}
