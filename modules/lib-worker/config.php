<?php

return [
    '__name' => 'lib-worker',
    '__version' => '0.0.1',
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
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
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
    ]
];