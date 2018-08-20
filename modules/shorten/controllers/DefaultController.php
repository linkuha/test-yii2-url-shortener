<?php

namespace app\modules\shorten\controllers;

use app\modules\shorten\Module;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


class DefaultController extends Controller
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [

				],
			],
		];
	}

	public function actionIndex()
	{
		return $this->render('index', []);
	}


}
