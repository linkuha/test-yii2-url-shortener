<?php

use yii\db\Migration;
//use yii\db\Schema;

/**
 * Handles the creation for table `link`.
 */
class m180817_113951_create_link_table extends Migration
{
	protected $tableOptions;
	
	private $_linkTableName;

	public function init()
	{
		parent::init();
		
		$this->tableOptions = null;
		switch (Yii::$app->db->driverName)
		{
			case 'mysql':
				$this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
				break;
			case 'pgsql':
				$this->tableOptions = null;
				break;
			default:
				throw new \RuntimeException('Your database is not supported!');
		}
		
		$this->_linkTableName = \app\modules\shorten\models\Link::tableName();
	}

	public function up()
	{
		$this->createTable($this->_linkTableName, [
			'id' => $this->primaryKey(),
			'full' => $this->string()->notNull()->comment('Ссылка'),
			'alias' => $this->string(6)->notNull()->unique()->comment('Короткий алиас'),
			'desc' => $this->string()->comment('Описание'),
			'stat' => $this->integer()->defaultValue(0)->comment('Переходов'),
			'created_at' => $this->integer(11)->notNull()->comment('Дата создания'),
			'updated_at' => $this->integer(11)->notNull()->comment('Последнее посещение'),
		], $this->tableOptions);

		$this->createIndex('idx-alias', $this->_linkTableName, ['alias'], true);

		$this->insert($this->_linkTableName, [
			'id' => 0,
			'full' => 'https://habr.com/post/208328/',
			'alias' => 'jA31xd',
			'desc' => 'test link',
			'created_at' => '0000000000',
			'updated_at' => '0000000000',
		]);
	}

	public function down()
	{
		$this->dropTable($this->_linkTableName);
	}
}
