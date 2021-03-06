<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;

$this->title = 'Contact';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('contactFormSubmitted')): ?>

        <div class="alert alert-success">
            Merci pour votre message, nous ferons notre possible pour y répondre au plus vite (promis).
        </div>

    <?php else: ?>
        <p>
            Si vous rencontrez un problème sur le site ou bien si vous avez une question, utilisez le formulaire suivant pour nous soumettre votre demande.
        </p>

        <div class="row">
            <div class="col-lg-5">

                <?php $form = ActiveForm::begin(['id' => 'contact-form']); ?>

                    <?php if($minimalContactForm === false): ?>
                        <?= $form->field($model, 'name' )->textInput(['autofocus' => true, 'autocomplete' => 'off']) ?>
                        <?= $form->field($model, 'email')->textInput(['autocomplete' => 'off']) ?>
                    <?php endif; ?>

                    <?= $form->field($model, 'body')->textarea(['rows' => 6]) ?>

                    <?php if ( $model->applyCaptcha ): ?>
                        <?= $form->field($model, 'verifyCode')->widget(Captcha::class, [
                            'template' => '<div class="row"><div class="col-lg-3">{image}</div><div class="col-lg-6">{input}</div></div>',
                        ]) ?>
                    <?php endif; ?>

                    <div class="form-group">
                        <?= Html::submitButton('Envoyer', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                    </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>

    <?php endif; ?>
</div>
