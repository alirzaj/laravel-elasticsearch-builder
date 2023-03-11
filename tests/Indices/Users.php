<?php

namespace Alirzaj\ElasticsearchBuilder\Tests\Indices;

use Alirzaj\ElasticsearchBuilder\Index;

class Users extends Index
{
    public string $name = 'users_index';

    public array $propertyTypes = [
        'text' => 'text',
        'user_id' => 'keyword',
        'ip' => 'ip',
        'created_at' => 'date',
    ];

    public array $fields = [
        'text' => [
            'hashtags' => [
                'type' => 'text',
                'analyzer' => 'hashtag',
            ],
        ],
    ];

    public array $analyzers = [
        'hashtag' => [
            'type' => 'custom',
            'tokenizer' => 'hashtag_tokenizer',
            'filter' => ['lowercase'],
        ],
        'hashtag_2' => [
            'type' => 'custom',
            'tokenizer' => 'hashtag_tokenizer',
            'filter' => ['lowercase'],
        ],
    ];

    public array $tokenizers = [
        'hashtag_tokenizer' => [
            'type' => 'pattern',
            'pattern' => '#\S+',
            'group' => 0,
        ],
    ];

    public array $propertyAnalyzers = [
        'text' => 'hashtag',
    ];

    public array $searchAnalyzers = [
        'text' => 'hashtag_2',
    ];

    public array $normalizers = [
        'my_normalizer' => [
            'type' => 'custom',
            'char_filter' => ['special_character_strip'],
            'filter' => ['lowercase',]
        ],
    ];

    public array $propertyNormalizers = [
        'ip' => 'my_normalizer'
    ];

    public array $characterFilters = [
        'special_character_strip' => [
            'type' => 'pattern_replace',
            'pattern' => '[._]',
        ],
    ];

    public array $tokenFilters = [
        '4_7_edge_ngram' => [
            'min_gram' => '4',
            'max_gram' => '7',
            'type' => 'edge_ngram',
            'preserve_original' => 'true',
        ],
    ];
}
