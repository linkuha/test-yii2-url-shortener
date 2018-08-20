<?php

namespace app\modules\shorten;

use yii\base\BootstrapInterface;
use Yii;

class Bootstrap implements BootstrapInterface
{
	public function bootstrap($app)
	{
		$app->i18n->translations['modules/shorten/*'] = [
			'class' => 'yii\i18n\PhpMessageSource',
			'forceTranslation' => true,
			'basePath' => '@app/modules/shorten/messages',
			'fileMap' => [
				'modules/shorten/default' => 'default.php',
			],
			'on missingTranslation' => ['app\components\TranslationEventHandler', 'handleMissingTranslation']
		];
	}
}