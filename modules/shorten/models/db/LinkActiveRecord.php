<?php

namespace app\modules\shorten\models\db;

use app\modules\shorten\models\Page;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Project model
 *
 * @property integer $id
 * @property integer $full
 * @property integer $alias
 */
class LinkActiveRecord extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%link}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['full', 'alias', ], 'required'],
			[['alias'], 'string', 'max' => 6],
			['full', 'string']
		];
	}
	
	public function behaviors()
	{
		return [
			'timestamp' => [
				  'class' => TimestampBehavior::className(),
				  'attributes' => [
					ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
					ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'], // last show
				  ],
				  'value' => new Expression('UNIX_TIMESTAMP(NOW())'),
			]	
		];
	}
}
