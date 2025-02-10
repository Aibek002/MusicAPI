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
   

 <div class="input-group mb-3">
    <?= $form->field($model, 'query')->textInput(['placeholder' => 'Введите название песни' , 'class'=> 'form-control','aria-describedby'=>'button-submit'])->label(false) ?>
    <?= Html::submitButton('Поиск', ['class' => 'btn btn-outline-secondary','id'=>'button-submit']) ?>
</div>
    <?php ActiveForm::end(); ?>

 
        <div class="flex-content">

            <div class="playlist-detail">
                <?php foreach ($tracks as $track): ?>

                    <iframe src='https://open.spotify.com/embed/track/<?php echo $track['id'] ?>' width="300" height="80"
                        frameborder="0" allowtransparency="true" allow="encrypted-media"></iframe>
                        
                <?php endforeach; ?>
            </div>
           
        </div> <div class="flex-menu-right"></div>

</body>

</html>