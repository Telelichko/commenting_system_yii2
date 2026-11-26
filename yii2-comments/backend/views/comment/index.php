<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = 'Управление комментариями';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="comment-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(['id' => 'comments-pjax']); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel'  => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'id',
                'author_name',
                'text:ntext',
                [
                    'attribute' => 'created_at',
                    'format'    => 'datetime',
                ],
                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
    <?php Pjax::end(); ?>
</div>

<?php
$this->registerJsFile(
    'https://cdn.socket.io/4.7.2/socket.io.min.js',
    ['position' => \yii\web\View::POS_HEAD]
);

$this->registerJs(<<<JS
(function() {
    var socket = io('http://localhost:3000');
    socket.on('comment_event', function(data) {
        console.log('[Admin] Событие:', data);
        if (typeof $.pjax !== 'undefined') {
            $.pjax.reload({container: '#comments-pjax', timeout: 5000});
        }
    });
    socket.on('connect', () => console.log('[Admin] WS подключен'));
    socket.on('connect_error', (err) => console.warn('[Admin] WS error:', err.message));
})();
JS
);
?>