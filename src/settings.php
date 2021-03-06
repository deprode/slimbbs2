<?php
return [
    'settings' => [
        'displayErrorDetails'    => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer'               => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        // Monolog settings
        'logger'                 => [
            'name'  => 'slim-app',
            'path'  => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'db' => [
            'driver'    => 'mysql',
            'host'      => getenv('MYSQL_HOST'),
            'database'  => getenv('MYSQL_DATABASE'),
            'username'  => getenv('MYSQL_USER'),
            'password'  => getenv('MYSQL_PASSWORD'),
            'port'      => getenv('MYSQL_PORT'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ],

        'admin_id' => getenv('ADMIN_ID'),

        'comment_limit' => getenv('COMMENT_LIMIT'),

        's3' => [
            'key'    => getenv('AWS_S3_KEY'),
            'secret' => getenv('AWS_S3_SECRET'),
            'bucket' => getenv('AWS_S3_BUCKET_NAME'),
            'region' => getenv('AWS_S3_REGION')
        ],
    ],
];
