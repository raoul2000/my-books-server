<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\BookReview */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="book-review-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'book_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'text')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'location_name') ?>
    <?= $form->field($model, 'email') ?>
    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>