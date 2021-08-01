<?php

namespace app\controllers\admin;

use Yii;
use app\models\EmailForm;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class EmailController extends \yii\web\Controller
{
        /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],     
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['POST', 'GET'],
                ],
            ],                   
        ];
    }
    public function actionIndex()
    {
        $model = new EmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('index', [
            'model' => $model
        ]);
    }

    public function actionTestEmail()
    {
        return $this->render('test-email');
    }

}
