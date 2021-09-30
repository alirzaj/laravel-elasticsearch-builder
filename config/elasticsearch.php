<?php

return [
    /**
     * the hosts of elasticsearch.
     * this can be a string or an array of hosts
     */
    'hosts' => 'localhost:9200',

    /**
     * the name of the queue for all jobs related to elasticsearch
     */
    'queue' => 'elasticsearch',

    /**
     * this array connects models to their index class
     */
    'indices' => [
        // 'Model::class' => Index::Class
    ]
];
