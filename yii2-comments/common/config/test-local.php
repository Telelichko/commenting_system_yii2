<?php

return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/main.php',
    require __DIR__ . '/main-local.php',
    require __DIR__ . '/test.php',
    [
        'components' => [
            'db' => [
                'class' => 'yii\db\Connection',
                'dsn' => 'mysql:host=db;dbname=yii2_comments;charset=utf8mb4',
                'username' => 'yii2',
                'password' => 'yii2_password_123',
                'charset' => 'utf8mb4',
                'enableSchemaCache' => false,
            ],
            'mailer' => [
                'useFileTransport' => true,
            ],
        ],
    ]
);