<?php

return [
    'id' => 'basic-tests',
    'basePath' => dirname(__DIR__),    
    'language' => 'en-US',
    'components' => [
        'config_db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=postgres',
            'username' => 'jap',
            'password' => 'ETv6gsrA5ts4zmX',
            'charset' => 'utf8',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=localhost;port=5432;dbname=testing',
            'username' => 'jap',
            'password' => 'ETv6gsrA5ts4zmX',
            'charset' => 'utf8',
            'tablePrefix' => 'tbl_',
        ],
    ],
];
