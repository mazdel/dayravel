<?php

return [
    'modules_path' => app_path('Modules'),

    'routes' => [
        'web' => [
            'middleware' => ['web'],
        ],

        'api' => [
            [
                'version' => 'v1',
                'filename' => 'api',
                'middleware' => ['api'],
            ],
        ],
    ],
];
