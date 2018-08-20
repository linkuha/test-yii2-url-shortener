<?php

return [
    'components' => [
        'request' => [
	    //'enableCookieValidation' => true,
	    //'enableCsrfValidation' => true,
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'xOrzFgODt-oR7JW0gi17jT9mRLQ5qmeP',
        ],
        'assetManager' => [
            'linkAssets' => true, // symlinks
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                    'logFile' => '@app/runtime/logs/web-error.log'
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['warning'],
                    'logFile' => '@app/runtime/logs/web-warning.log'
                ],
            ],
        ],
    ],
];