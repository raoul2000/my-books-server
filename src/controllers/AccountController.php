<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use app\models\User;
use app\models\UserToken;
use app\models\forms\UserRegistrationForm;

class AccountController extends \yii\web\Controller
{
    /**
     * Activate user account related to the provided token
     */
    public function actionActivate($token)
    {
        $model = UserToken::findByToken($token, UserToken::TYPE_EMAIL_ACTIVATE);
        
        if ($model) {
            $user = $model->user;
            $user->status = User::STATUS_ACTIVE;
            $user->update(false, ['status']);
            $model->delete();
            // create API Key token
            UserToken::generate($user->id,UserToken::TYPE_API_KEY);
            $success = true;
        } else {
            $success = false;
        }

        return $this->render('activate', [
            'success' => $success
        ]);
    }

    /**
     * Create a user Account
     */
    public function actionCreate()
    {       
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new UserRegistrationForm();
        if ($model->load(Yii::$app->request->post()) && $model->register()) {

            $userEmail = '';
            if (Yii::$app->params['enableAccountActivation']) {
                $user =  User::findOne($model->getUserId());
                $userEmail = $user->email;

                Yii::$app->mailer->compose(
                    [
                        'html' => 'account/activation-html',
                        'text' => 'account/activation-text',
                    ],
                    [
                        'activationUrl' => Url::to(['account/activate', 'token' => $model->getAccountActivationToken()], true),
                        'username'      => $user->username
                    ]
                )
                    ->setTo($userEmail)
                    ->setFrom([$this->getSenderEmail() => $this->getSenderName()])
                    ->setReplyTo([$this->getSenderEmail() => $this->getSenderName()])
                    ->setSubject('Mes Livres: activer mon compte')
                    ->send();

            }

            return $this->render('create-success', [
                'activationRequired' => Yii::$app->params['enableAccountActivation'],
                'email'              => $userEmail
            ]);
        }

        $model->password = '';
        $model->password_confirm = '';
        return $this->render('create', [
            'model' => $model,
            'activationRequired' => Yii::$app->params['enableAccountActivation']
        ]);
    }

    public function getSenderName()
    {
        return Yii::$app->params['senderName'];
    }
    public function getSenderEmail()
    {
        return Yii::$app->params['senderEmail'];
    }

    /**
     * Returns the QR code image for the given book id.
     */
    public function actionBookQrCode($id) {
        $filePath = Yii::getAlias('@qrcodePath/' . $id . '.png');
        $this->downloadFile($filePath);
    }

    public function downloadFile($fullpath){
      if(!empty($fullpath)){ 
          header("Content-type:image/png");
          // uncomment to download
          //header('Content-Disposition: attachment; filename="'.basename($fullpath).'"'); 
          header('Content-Length: ' . filesize($fullpath));
          readfile($fullpath);
          Yii::$app->end();
      }
    }    
}
