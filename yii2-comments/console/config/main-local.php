<?php

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:' . dirname(__DIR__) . '/data/db.sqlite',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@common/mail',
            'useFileTransport' => true, 
        ],
    ],
];