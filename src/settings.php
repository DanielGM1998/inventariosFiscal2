<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        'rpt_renderer' => [
            'template_path' => __DIR__ . '/../rpt/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Configuración de mi DNS data base
        
        'connectionString' => [
            'dns'  => 'mysql:host=localhost;dbname=baceh2022_fiscal;charset=utf8',
            'user' => 'root',
            'pass' => ''
        ]
    ],
];
