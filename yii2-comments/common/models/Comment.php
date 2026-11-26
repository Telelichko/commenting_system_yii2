<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Comment extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%comment}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    public function rules()
    {
        return [
            [['author_name', 'text'], 'required'],
            [['author_name'], 'string', 'max' => 100],
            [['text'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'author_name' => 'Автор',
            'text'        => 'Комментарий',
            'created_at'  => 'Создан',
            'updated_at'  => 'Обновлён',
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->broadcast($insert ? 'created' : 'updated');
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $this->broadcast('deleted');
    }

    private function broadcast(string $action): void
    {
        if (!Yii::$app->has('redis')) return;

        $payload = json_encode([
            'action' => $action,
            'id'     => $this->id,
            'author' => $this->author_name,
            'text'   => $this->text,
            'time'   => date('H:i:s'),
        ], JSON_UNESCAPED_UNICODE);

        Yii::$app->redis->executeCommand('PUBLISH', ['comments_channel', $payload]);
    }
}