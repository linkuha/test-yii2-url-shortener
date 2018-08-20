<?php

namespace app\modules\shorten;

use Yii;

class Module extends \yii\base\Module
{
	protected $_isBackend;
	

	public static function t($category, $message, $params = [], $language = null)
	{
		return Yii::t('modules/shorten/' . $category, $message, $params, $language);
	}

	/**
	 * Check if module is used for backend application.
	 *
	 * @return boolean true if it's used for backend application
	 */
	public function getIsBackend()
	{
		if ($this->_isBackend === null) {
			$this->_isBackend = strpos($this->controllerNamespace, 'backend') === false ? false : true;
		}

		return $this->_isBackend;
	}
}
