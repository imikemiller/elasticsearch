<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 14/05/18
 * Time: 18:06
 */
return [
    'store'=>[
        env('ES_STORE_INDEX') => [

            'aliases' => [
                env('ES_STORE_INDEX_ALIAS')
            ],

            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ],

            'mappings' => [
                'queries' => [
                    'properties' => [
                        'query' => [//to contain the serialised QueryDsl object
                            'type' => 'text'
                        ],
                        'note' => [ //optionally for describing what the stored query does
                            'type' => 'text'
                        ]
                    ]
                ]
            ]

        ]
    ]
];
