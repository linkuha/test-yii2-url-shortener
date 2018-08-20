<?php

namespace app\modules\shorten\models;

use app\modules\shorten\models\db\LinkActiveRecord;
use app\modules\shorten\Module;
use Yii;

class Link extends LinkActiveRecord
{
	public function generateAlias($outputLen = 6)
	{
		// 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'
		$array = array_merge(range('a','z'), range('0','9'), range('A', 'Z'));
		shuffle($array);
		$max = count($array) - 1;
		
		for($i = 0; $i < $outputLen; $i++){
					$result .= $array[mt_rand(0, $max)];
		}
		
		$this->alias = $result;
	}

}