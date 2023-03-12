<?php

return [
    /**
     * the hosts of elasticsearch.
     * this can be a string or an array of hosts
     *
     * e.g:  [
        'host' => env('ELASTIC_HOST', ''),
        'port' => env('ELASTIC_PORT', ''),
        'scheme' => env('ELASTIC_SCHEME', 'https'),
        'user' => env('ELASTIC_USER', ''),
        'pass' => env('ELASTIC_PASS', ''),
     ],
     * e.g: 'elasticsearch:9200'
     */
    'hosts' => 'elasticsearch:9200',

    'ssl_verification' => true,

    /**
     * the name of the queue for all jobs related to elasticsearch
     */
    'queue' => 'elasticsearch',

    /**
     * this array connects models to their index class
     */
    'indices' => [
        // 'Model::class' => Index::Class
    ],

    /**
     * Specify how many times should the operation be retried when a conflict occur
     */
    'retry_on_conflict' => 3,
];
