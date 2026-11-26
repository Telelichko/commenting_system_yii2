<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=db;dbname=yii2_comments;charset=utf8mb4',
            'username' => 'yii2',
            'password' => 'yii2_password_123',
            'charset' => 'utf8mb4',
        ],
        'mailer' => [
            'class' => '\yii\symfonymailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => true,
        ],
    ],
];