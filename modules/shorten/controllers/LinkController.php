<?php

namespace app\modules\shorten\controllers;

use app\modules\shorten\Module;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use app\modules\shorten\models\Link;
use app\modules\shorten\helpers\UriHelper;

class LinkController extends Controller
{
	public function behaviors()
	{
		return [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [ 
					'generate' => [ 'post' ] 
				],
			],
		];
	}

	public function actionIndex()
	{
		return $this->render('index', []);
	}
	
	public function actionGet($alias)
	{
		$model = Link::find()->where(['alias' => $alias])->one();
		
		if (!$model) {
			\Yii::$app->session->setFlash('error', 'Такой ссылки нет :(');
			return $this->redirect(['link/index']);
		}
		return $this->redirect($model->full);
	}

	public function actionGenerate()
	{
		$linkText  = Yii::$app->request->post('full_url');
		Yii::$app->response->format = Response::FORMAT_JSON;
		
		 
		if (false === $testedLink = UriHelper::isValidUrl($linkText, true, false))
		{
			\Yii::$app->session->setFlash('error', 'Некорректная ссылка, попробуйте заново...');
			return $this->redirect(['link/index']);
		}
		
		$correctLink = UriHelper::buildUrl($testedLink);
		
		try {
			$exists = Link::find()->where(['full' => $correctLink])->one();
			if (null !== $exists) 
			{
				return [ 'alias' => $exists->alias, 'test' => $correctLink  ];
			}
		
			$model = new Link();
			$model->full = $correctLink;
			$model->generateAlias(); 
			do {
				$model->generateAlias(); 
			} 
			while (null !== $this->findModel($model->alias));
			
			if ($model->save() === false && !$model->hasErrors()) {
				\Yii::$app->session->setFlash('error', 'Ошибка сохранения, попробуйте заново...');
				return null;
			}
		} catch (\Exception $exc) {
			// create log file in webroot folder
			$f = fopen('link_controller_error_log.txt', 'a+');
			fwrite($f, $exc->getMessage() . PHP_EOL . $exc->getCode());
			fwrite($f, $exc->getLine() . PHP_EOL);
			fwrite($f, $exc->getTraceAsString());
			fclose($f);
			
			return [ 'error' => $exc->message, 'error_file_line' => $exc->line . " : " . $exc->file ];
		}
		return [ 'alias' => $model->alias, 'test' => $correctLink ];
	}
	

	protected function findModel($alias)
	{
		$model = Link::find()->where([
			'alias' => $alias
		])->one();

		return $model;
	}





}
