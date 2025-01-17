<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */

$this->title = 'Music';
?>
<div class="site-index">

<div class="jumbotron text-center">
    <h1 class="display-4">Listen to music with us!</h1>
    <p class="lead">You have successfully created your Yii-powered application.</p>
    
    <div class="tags">
        <h5>Filter by tags:</h5>
        <?= Html::a('All musics', ['site/index'], ['class' => 'btn btn-primary']) ?>
        <?php foreach ($tags as $tag): ?>
            <?= Html::a($tag->tag_type, ['site/index', 'tag_id' => $tag->id], ['class' => 'btn btn-primary']) ?>
        <?php endforeach; ?>
    </div>

    <div class="sort-links">
        <h5>Sorted by:</h5>
        <?= Html::a('Title (Ascending)', $sort->createUrl('titlePost', SORT_ASC), ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Title (Descending)', $sort->createUrl('titlePost', SORT_DESC), ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Date create (Ascending)', $sort->createUrl('createAt', SORT_ASC), ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Date create (Descending)', $sort->createUrl('createAt', SORT_DESC), ['class' => 'btn btn-primary']) ?>
    </div>
</div>

<div class="body-content">
    <div class="row">
        <?php foreach ($post_music as $music): ?>
            <div class="col-lg-4">
                <img src="https://daily.jstor.org/wp-content/uploads/2021/10/how_to_hear_images_and_see_sounds_1050x700.jpg" alt="" class="sounds-image">
                <h2><?= Html::encode($music->titlePost) ?></h2>
                <audio controls class="custom-audio">
                    <source src="<?= Yii::getAlias('@web') . '/musicsPost/' . Html::encode($music->nameAudioFile) ?>" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            </div>
        <?php endforeach ?>
    </div>
</div>
</div>

<?= LinkPager::widget([
'pagination' => $pagination,
'options' => [
    'class' => 'pagination',
],
'linkOptions' => [
    'class' => 'btn btn-primary mx-1',
],
'activePageCssClass' => 'active',
'disabledPageCssClass' => 'disabled',
]) ?>
