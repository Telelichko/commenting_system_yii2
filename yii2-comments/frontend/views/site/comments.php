<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'Лента комментариев';
?>
<div class="site-comments">
    <h1><?= Html::encode($this->title) ?></h1>

    <div id="comments-feed">
        <?php foreach ($comments as $comment): ?>
            <div class="comment-item" data-id="<?= $comment->id ?>"
                 style="border:1px solid #ddd;padding:10px;margin-bottom:10px;border-radius:4px;">
                <strong><?= Html::encode($comment->author_name) ?></strong>
                <small style="float:right;"><?= Yii::$app->formatter->asDatetime($comment->created_at) ?></small>
                <p><?= Html::encode($comment->text) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <hr>
    <h3>Оставить комментарий</h3>

    <?php $form = ActiveForm::begin(['id' => 'comment-form']); ?>
        <?= $form->field($model, 'author_name')->textInput(['placeholder' => 'Ваше имя']) ?>
        <?= $form->field($model, 'text')->textarea(['rows' => 3, 'placeholder' => 'Текст...']) ?>
        <div class="form-group">
            <?= Html::submitButton('Отправить', ['class' => 'btn btn-primary']) ?>
        </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$createUrl = Url::to(['site/comments']);
$this->registerJs(<<<JS
$('#comment-form').on('beforeSubmit', function(e) {
    e.preventDefault();
    var \$form = $(this);
    var \$btn = \$form.find('button[type=submit]');
    \$btn.prop('disabled', true).text('Отправка...');

    $.post('{$createUrl}', \$form.serialize())
        .done(function(res) {
            if (res.success) {
                var html = '<div class="comment-item" data-id="' + res.comment.id + '" ' +
                    'style="border:1px solid #ddd;padding:10px;margin-bottom:10px;border-radius:4px;background:#eaffea;">' +
                    '<strong>' + res.comment.author_name + '</strong>' +
                    '<p>' + res.comment.text + '</p></div>';
                $('#comments-feed').prepend(html);
                \$form[0].reset();
            } else {
                alert('Ошибка: ' + JSON.stringify(res.errors));
            }
        })
        .fail(function() { alert('Ошибка сети'); })
        .always(function() {
            \$btn.prop('disabled', false).text('Отправить');
        });
    return false;
});
JS
);
?>