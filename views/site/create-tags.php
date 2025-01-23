<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>

<div class="form-container">
    <?php $form = ActiveForm::begin() ?>
    <?= $form->field($tags, 'tag_type')->textInput(['name' => 'tags']); ?>
    <?= Html::submitButton('Create', ['class' => 'btn btn-primary']) ?>
    <?php ActiveForm::end(); ?>
</div>