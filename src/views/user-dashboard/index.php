<?php


use yii\helpers\Html;
/* @var $this yii\web\View */

$this->title = 'Mes Livres';
if ($totalBookCount === 0) {
    $subtitle = "Pour l'instant aucun livre n'a été enregsitré";
} elseif ($totalBookCount === 1) {
    $subtitle = "Un seul livre";
}
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Mes Livres</h1>
        <p class="lead">
            <?php if ($totalBookCount === 0) : ?>
                Vous n'avez pour l'instant aucun livre dans votre collection
            <?php elseif ($totalBookCount === 1) : ?>
                Vous n'avez qu'un seul livre dans votre collection
            <?php else : ?>
                Vous avez actuellement <strong><?= $totalBookCount ?></strong> livres dans votre collection
            <?php endif; ?>
        </p>
    </div>

    <?php if(!empty($apiKey)) : ?>
        <div style="display: flex;
            flex-direction: column;
            align-items: center;">

            <div>
                <?= Html::a(
                    'ouvrir l\'application <span class="glyphicon glyphicon-new-window" aria-hidden="true"></span>', 
                    Yii::$app->params['bookAppUrl'] . '/' . $apiKey, 
                    [
                        'class'  => 'btn btn-primary btn-lg', 
                        'target' => 'blank',
                        'title'  => 'ouvrir dans un nouvel onglet'
                    ]
                ) ?>
            </div>
            <div style="margin-top: 2em; width:50%">
                <div class="or"> ou </div>
            </div>
            <?php if(!empty($qrCode)): ?>                    
                <div style="margin-top: 2em;">
                    <div style="text-align:center;">
                        <p style="font-size:0.8em;">Scannez le QR Code pour accéder à l'application<br/>depuis un autre appareil</p>
                        <img src="<?=  $qrCode->writeDataUri()?>" title=" QR code" alt="qr code"/>
                    </div>
                </div>
            <?php endif ?> 

        </div>
    <?php endif ?>
</div>