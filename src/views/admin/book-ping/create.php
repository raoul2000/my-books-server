<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BookPing */

$this->title = 'Create Book Ping';
$this->params['breadcrumbs'][] = ['label' => 'Book Ping', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-ping-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <hr/>
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
