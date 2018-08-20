<?php

use yii\helpers\ArrayHelper;
 
$params = ArrayHelper::merge(
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);
 
return [
	'name' => 'PUDICH',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
    	'log',
		'app\modules\shorten\Bootstrap',
	],
	'language' => 'ru',
	'modules' => [],
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'charset' => 'utf8',
        ],
        'urlManager' => [
			'class' => 'yii\web\UrlManager',

            'enablePrettyUrl' => true,
            'showScriptName' => false,

			'rules' => [
				'' => 'shorten/link/index',
				'<_a:error>' => 'shorten/default/<_a>',

				'<alias:[\w-]+>' => 'shorten/link/get',
/*
				'<_m:[\w\-]+>' => '<_m>/default/index',
				'<_m:[\w\-]+>/<_c:[\w\-]+>' => '<_m>/<_c>/index',
				'<_m:[\w\-]+>/<_c:[\w\-]+>/<_a:[\w-]+>' => '<_m>/<_c>/<_a>',
				'<_m:[\w\-]+>/<_c:[\w\-]+>/<id:\d+>' => '<_m>/<_c>/view',
				'<_m:[\w\-]+>/<_c:[\w\-]+>/<id:\d+>/<_a:[\w\-]+>' => '<_m>/<_c>/<_a>',
*/
			],
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
        'cache' => [
            'class' => 'yii\caching\DummyCache',
        ],
        'log' => [
            'class' => 'yii\log\Dispatcher',
        ],
		'i18n' => [
			'translations' => [
				'*' => [
					'class' => 'yii\i18n\PhpMessageSource',
					'forceTranslation' => true,
					'fileMap' => [
						'app'     => 'app.php',
						'buttons' => 'buttons.php',
					],
					'on missingTranslation' => ['app\components\TranslationEventHandler', 'handleMissingTranslation']
				],
			],
		],

    ],
    'params' => $params,
];