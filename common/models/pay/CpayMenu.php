<?php
namespace common\models\pay;

use backend\models\MyException;
use Yii;

/**
 * This is the model class for table "cpay_menu".
 *
 */
class CpayMenu extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cpay_menu';
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
        return [
            [['type', 'coin', 'cost'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '1-微信2-支付宝',
            'coin' => '钻石数',
            'cost' => '消耗',
        ];
    }


    /**
     * 添加修改
     * @param $data
     * @return array
     */
    public function add($data)
    {
        try {

            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            if ($this->validate() && $this->save()) {
                $attributes = $this->attributes;
                return $attributes;
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 查找基本数据
     * @param $id
     * @param $isObj
     * @return mixed
     */
    public function findBase($id, $isObj = true)
    {
        $obj = self::find()->where('id = :id', array(':id' => $id));

        if (!$isObj) {
            $obj->asArray();
        }

        return $obj->one();
    }


}
