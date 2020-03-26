<?php

namespace common\models;

use backend\models\ErrorCode;
use backend\models\MyException;
use Yii;


class BackendGlobalConfig extends \backend\models\BaseModel
{
    //赛马抽水
    const PROFIT_SUM = 1;

    //赛马抽水
    const TEST_PROFIT_SUM = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_global_config';
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
            [['type', 'updated_at'], 'required'],
            [['type'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'type'       => '类型',
            'value'      => '值',
            'remark'     => '备注',
            'status'     => '状态 1可用 2不可用',
            'updated_at' => '修改时间',
            'created_at' => '创建时间',
            'admin_id'   => '修改人ID',
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
     * @param $type
     * @param string $select
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getDataInType($type, $select = '')
    {
        $obj = self::find()->where('type = :type', array(':type' => $type));
        if (!empty($select)) {
            $obj->select($select);
        }
        return $obj->asArray()->one();
    }


    /**
     * @param $type
     * @return mixed
     * @throws MyException
     */
    public static function getValue($type)
    {
        $value = self::find()->where('type = :type', array(':type' => $type))->select('value')->asArray()->one();
        if (!isset($value['value'])) {
            throw new MyException(ErrorCode::ERROR_ACCOUNT_FUN_NOT_EXIST);
        }
        return $value['value'];
    }

    /**
     * @param $type
     * @param $value
     * @return mixed
     * @throws MyException
     */
    public static function setValue($type, $value)
    {
        $obj = self::find()->where('type = :type', array(':type' => $type))->select('value')->one();
        if (empty($obj)) {
            throw new MyException(ErrorCode::ERROR_ACCOUNT_FUN_NOT_EXIST);
        }
        $data = $obj->add(['value' => $value]);
        return $data;
    }

}
