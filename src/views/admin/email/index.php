<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Email';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?> <small>Test</small></h1>
    <hr/>
    
    <?php if (Yii::$app->session->hasFlash('emailSendSuccess')): ?>
        <div class="alert alert-success">
            Test Email has been sent.
        </div>
    <?php elseif (Yii::$app->session->hasFlash('emailSendError')): ?>
        <div class="alert alert-danger">
            Email could not be sent
        </div>
    <?php else: ?>

        <p>
            Send a test email using configured email settings. Sender is <strong>
                <?= Html::encode($model->getSenderName())  .' - ' . $model->getSenderEmail() ?></strong>
        </p>

        <div class="row">
            <div class="col-lg-5">

                <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>

                    <?= $form->field($model, 'name')->textInput(['autofocus' => true, 'autocomplete' => 'off']) ?>

                    <?= $form->field($model, 'email') ?>

                    <?= $form->field($model, 'subject') ?>

                    <?= $form->field($model, 'body')->textarea(['rows' => 6]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                    </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>

    <?php endif; ?>
</div>
