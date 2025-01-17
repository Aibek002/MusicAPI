<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin() ?>
<?= $form->field($tags, 'tag_type')->textInput(['name' => 'tags']); ?>
<?= Html::submitButton('create', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>