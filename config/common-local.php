<?php

return [
    'components' => [
        'db' => [
			'class' => 'yii\db\Connection',
			'dsn' => 'mysql:host=localhost;dbname=________',
			'username' => '________',
			'password' => '________',
			'charset' => 'utf8',
			'tablePrefix' => 'yii2_',

			// Schema cache options (for production environment)
			//'enableSchemaCache' => true,
			//'schemaCacheDuration' => 60,
			//'schemaCache' => 'cache',
		],
        'mailer' => [
            'useFileTransport' => true,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];