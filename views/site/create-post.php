<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

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

    <?php foreach ($tags as $tag): ?>
        <?= Html::checkbox('tags[]', false, [
            'value' => $tag->id,
            'label' => $tag->tag_type,  // Добавляем отображаемое название жанра
            'id' => 'tag-' . $tag->id, // Уникальный ID для каждой кнопки
        ]); ?>
    <?php endforeach; ?>
</div>
<?= Html::a('Create tags',['site/create-tags'],['class'=>'btn btn-primary']) ?>
<?= Html::submitButton('отправить', ['class' => 'submitButton']); ?>

<?php ActiveForm::end(); ?>