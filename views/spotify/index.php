<?php
/* @var this yii */
/* @varalbums array */

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
    <div class="flex">
        <div class="flex-menu-left"></div>
        <div class="flex-content">
            <div class="album-list">
                <div class="playlist-list">
                    <div class="playlist-detail">
                        <h1><?= Html::encode($playlist['name']) ?></h1>

                        <p><strong>Описание:</strong>
                            <?= Html::encode($playlist['description'] ?: 'Описание отсутствует') ?></p>

                        <!– Изображение плейлиста –>
                            <?php if (!empty($playlist['images'])): ?>
                                <img src="<?= Html::encode($playlist['images'][0]['url']) ?>"
                                    alt="<?= Html::encode($playlist['name']) ?>" width="200">
                            <?php endif; ?>

                            <p><a href="<?= Html::encode($playlist['external_urls']['spotify']) ?>"
                                    target="_blank">Послушать на Spotify</a></p>
                    </div>

                    <h2>Треки в плейлисте</h2>
                    <ul>
                        <?php foreach ($playlist['tracks']['items'] as $track): ?>
                            <li>
                                <strong><?= Html::encode($track['track']['name']) ?></strong>
                                — <?= Html::encode($track['track']['artists'][0]['name']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="menu-right"></div>
            </div>

</body>

</html>