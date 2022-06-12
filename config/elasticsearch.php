<?php

return [
    /**
     * the hosts of elasticsearch.
     * this can be a string or an array of hosts
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
