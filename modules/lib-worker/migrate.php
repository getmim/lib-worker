<?php

return [
    'LibWorker\\Model\\WorkerResult' => [
        'fields' => [
            'id' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => true,
                    'primary_key' => true,
                    'auto_increment' => true
                ],
                'index' => 0
            ],
            'name' => [
                'type' => 'VARCHAR',
                'length' => 150,
                'attrs' => [
                    'null' => false
                ],
                'index' => 100
            ],
            'source' => [
                'type' => 'TEXT',
                'attrs' => [
                    'null' => false
                ],
                'index' => 200
            ],
            'result' => [
                'type' => 'TEXT',
                'attrs' => [
                    'null' => false
                ],
                'index' => 300
            ],
            'created' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 400
            ]
        ],
        'indexes' => [
            'by_name' => [
                'fields' => [
                    'name' => []
                ]
            ]
        ]
    ],
    'LibWorker\\Model\\WorkerJob' => [
        'fields' => [
            'id' => [
                'type' => 'INT',
                'attrs' => [
                    'unsigned' => true,
                    'primary_key' => true,
                    'auto_increment' => true
                ],
                'index' => 0
            ],
            'name' => [
                'type' => 'VARCHAR',
                'length' => 150,
                'attrs' => [
                    'unique' => true,
                    'null' => false
                ],
                'index' => 100
            ],
            'router' => [
                'type' => 'TEXT',
                'attrs' => [
                    'null' => false 
                ],
                'index' => 200
            ],
            'data' => [
                'type' => 'TEXT',
                'attrs' => [
                    'null' => false 
                ],
                'index' => 300
            ],
            'time' => [
                'type' => 'DATETIME',
                'index' => 400
            ],
            'created' => [
                'type' => 'TIMESTAMP',
                'attrs' => [
                    'default' => 'CURRENT_TIMESTAMP'
                ],
                'index' => 500
            ]
        ],
        'indexes' => [
            'by_time' => [
                'fields' => [
                    'time' => []
                ]
            ]
        ]
    ]
];
