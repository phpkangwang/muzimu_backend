<?php

namespace common\models\game\byu;

use Yii;

class ByuFish extends Byu
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fish_config';
    }

    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
        ];
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        $data = self::find()->orderBy('id asc')->asArray()->all();
        return $data;
    }

    /**
     *  根据鱼id获取鱼的名字
     * @param $id
     */
    public function findNameById($id)
    {
        $obj = self::findOne($id);
        return $obj->desc;
    }
}
