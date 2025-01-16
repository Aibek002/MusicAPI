<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin([
    'options' => [
        'enctype' => 'multipart/form-data',
        'class' => 'form-input'
    ]
]);
?>

<?= $form->field($post, 'titlePost')->textInput(['name' => 'titlePost']); ?>
<?= $form->field($post, 'descriptionPost')->textarea(['name' => 'descriptionPost']); ?>
<?= $form->field($post, 'nameAudioFile')->fileInput(['name' => 'nameAudioFile']); ?>

<p class="tags_title"><?= Yii::t('app', 'Choose tags for music:') ?></p>
<div class="checkbox">

    <?php foreach ($genres as $genre): ?>
        <?= Html::checkbox('genres[]', false, [
            'value' => $genre->id,
            'label' => $genre->genre_type,  // Добавляем отображаемое название жанра
            'id' => 'genre-' . $genre->id, // Уникальный ID для каждой кнопки
        ]); ?>
    <?php endforeach; ?>
</div>

<?= Html::submitButton('отправить', ['class' => 'submitButton']); ?>

<?php ActiveForm::end(); ?>