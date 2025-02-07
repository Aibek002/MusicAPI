<?php
/* @var this yii */
/* @varalbums array */

use yii\widgets\ActiveForm;
use yii\helpers\Html;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>


<body>
    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['spotify/index']
    ]); ?>
    <?= $form->field($model, 'query')->textInput(['placeholder' => 'Введите название песни'])->label(false) ?>
    <?= Html::submitButton('Поиск', ['class' => 'btn btn-primary']) ?>

    <?php ActiveForm::end(); ?>

    <div class="flex">
        <div class="flex-menu-left"></div>
        <div class="flex-content">
            <div class="album-list">
                <div class="playlist-list">
                    <div class="playlist-detail">
                        <?php foreach ($tracks as $track): ?>
                            <!-- <audio src="https://open.spotify.com/embed/track/<?php echo $track['id'] ?>"></audio> -->
                            <iframe src='https://open.spotify.com/embed/track/<?php echo $track['id'] ?>' width="300"
                                height="80" frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>
                        <?php endforeach; ?>
                    </div>
                    <div class="menu-right"></div>
                </div>

</body>

</html>