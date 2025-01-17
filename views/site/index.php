<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */

$this->title = 'Music';
?>
<div class="site-index">

    <div class="jumbotron text-center bg-transparent mt-5 mb-5">
        <h1 class="display-4">Listen to music with us!</h1>

        <p class="lead">You have successfully created your Yii-powered application.</p>

        <p>
        <div class="tags">
            <p>Filter by tags:</p>
            <?= Html::a('All musics', ['site/index'], ['class' => 'btn btn-primary']) ?>
            <?php foreach ($tags as $tag): ?>
                <?= Html::a($tag->tag_type, ['site/index', 'tag_id' => $tag->id], ['class' => 'btn btn-primary']) ?>
            <?php endforeach; ?>
        </div>
        <div class="sort-links">
            <p>Sorted by:</p>
            <?= Html::a('Title (Ascending)',$sort->createUrl('titlePost',SORT_ASC));?>
            <?= Html::a('Title (Descending)',$sort->createUrl('titlePost', SORT_DESC));?>
            <?= Html::a('Date create (Ascending)',$sort->createUrl('createAt',SORT_ASC));?>
            <?= Html::a('Date create (Descending)',$sort->createUrl('createAt',SORT_DESC));?>

        </div>
        </p>
    </div>

    <div class="body-content">

        <div class="row">
            <?php foreach ($post_music as $music): ?>
                <div class="col-lg-4 mb-3">
                    <h2><?php $music->titlePost ?></h2>
                    <h2><?= Html::encode($music->titlePost) ?></h2>
                    <audio controls>
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
]) ?>