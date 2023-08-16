<?php

return [
    '__name' => 'lib-worker',
    '__version' => '0.6.0',
    '__git' => 'git@github.com:getmim/lib-worker.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/lib-worker' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'lib-model' => NULL
            ],
            [
                'cli' => NULL
            ],
            [
                'lib-curl' => NULL
            ]
        ],
        'optional' => []
    ],
    '__gitignore' => [
        'etc/.worker' => true
    ],
    'autoload' => [
        'classes' => [
            'LibWorker\\Controller' => [
                'type' => 'file',
                'base' => 'modules/lib-worker/controller'
            ],
            'LibWorker\\Model' => [
                'type' => 'file',
                'base' => 'modules/lib-worker/model'
            ],
            'LibWorker\\Library' => [
                'type' => 'file',
                'base' => 'modules/lib-worker/library'
            ]
        ],
        'files' => []
    ],
    'gates' => [
        'lib-worker' => [
            'priority' => 3000,
            'host' => [
                'value' => 'CLI'
            ],
            'path' => [
                'value' => 'worker'
            ]
        ]
    ],
    'routes' => [
        'lib-worker' => [
            404 => [
                'handler' => 'Cli\\Controller::show404'
            ],
            500 => [
                'handler' => 'Cli\\Controller::show500'
            ],
            'libWorkerStart' => [
                'info' => 'Start application worker',
                'path' => [
                    'value' => 'start'
                ],
                'handler' => 'LibWorker\\Controller\\Worker::start'
            ],
            'libWorkerStop' => [
                'info' => 'Stop application worker',
                'path' => [
                    'value' => 'stop'
                ],
                'handler' => 'LibWorker\\Controller\\Worker::stop'
            ],
            'libWorkerStatus' => [
                'info' => 'Application worker status',
                'path' => [
                    'value' => 'status'
                ],
                'handler' => 'LibWorker\\Controller\\Worker::status'
            ],
            'libWorkerPID' => [
                'info' => 'Application worker pid',
                'path' => [
                    'value' => 'pid'
                ],
                'handler' => 'LibWorker\\Controller\\Worker::pid'
            ],
            'libWorkerRun' => [
                'info' => 'Run application worker to some job',
                'path' => [
                    'value' => 'run (:id)',
                    'params' => [
                        'id' => 'number'
                    ]
                ],
                'handler' => 'LibWorker\\Controller\\Worker::run'
            ]
        ]
    ],
    'libWorker' => [
        'concurency' => 5,
        'pidFile' => 'etc',
        'phpBinary' => 'php',
        'keepResponse' => true
    ]
];
