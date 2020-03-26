<?php

namespace common\models;

use backend\models\ErrorCode;
use backend\models\MyException;
use common\models\game\paman\Pam;
use Yii;


class GlobalConfig extends \backend\models\BaseModel
{
    //JP奖默认配置
    const JP_TYPE = 1;

    //分成配置
    const PART_TYPE = 2;

    //老玩家JP配置
    const Old_PLAYER_JP_TYPE = 3;

    //老玩家JP押注分配置
    const Old_PLAYER_JP_BET_SCORE = 4;

    //雪豹默认几率 简单
    const XB_DEFAULT_ODDS_RATE = 5;

    //雪豹默认几率 困难
    const XB_DEFAULT_ODDS_RATE_DIFFICULTY = 6;

    //小洛默认参数
    const AI_LUO = 7;

    //小洛自增数
    const AI_AUTO_NUM = 8;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_global_config';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['type', 'updated_at'], 'required'],
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
        $obj = self::find()->where('type = :type', array(':type' => $type))->one();
        if (empty($obj)) {
            throw new MyException(ErrorCode::ERROR_ACCOUNT_FUN_NOT_EXIST);
        }
        $data = $obj->add(['value' => $value]);
        return $data;
    }

    /**
     * 获取默认值
     * @return mixed
     */
    public function getJPDefault()
    {
        return array(
            'jp_accumulate_count'     => 0,
            'jp_play_count'           => 0,
            'jp_play_count_rate'      => '{"1":25,"2":25,"3":25,"4":25}',
            'jp_pre_win_type_rate'    => '{"50":20,"200":20,"120":20,"500":20}',
            'jp_accumulate_add_buff'  => 0,
            'jp_award_interval_count' => 0,
            'jp_open_limit_count'     => 0,
        );
    }

    /**
     * 获取JP奖内容
     * @return mixed
     */
    public function getJPValue($roomId)
    {

        try {
            $type = self::JP_TYPE;
            $val  = $this->getDataInType($type, 'type,id,value');

            if (!isset($val['value']) || empty($val['value'])) {
                return $this->getJPDefault();
            }
            $roomId = ltrim($roomId, '10_');
            $value  = json_decode($val['value'], true);
            if (!isset($value[$roomId])) {
                return $this->getJPDefault();
            }
            return $value[$roomId];
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改JP奖内容
     * @return mixed
     */
    public function updateJPValue($roomId, $data, $loginId)
    {

        $obj            = new Pam();
        $FivepkSeoPaman = $obj->getModelMachine();
        $vData          = $FivepkSeoPaman->validateJP($data);
        if (
            !isset($vData['jp_pre_win_type_rate'])
            || !isset($vData['jp_play_count_rate'])
        ) {
            return '';
        }

        $type       = self::JP_TYPE;
        $originData = $this->find()->where('type = :type', array(':type' => $type))->one();


        $originData->value;

        $jsonData = [];
        if (!empty($originData->value)) {
            $jsonData = json_decode($originData->value, true);
        }
        $roomId            = ltrim($roomId, '10_');
        $jsonData[$roomId] = [
            'jp_accumulate_count'     => $data['jp_accumulate_count'],
            'jp_play_count'           => $data['jp_play_count'],
            'jp_play_count_rate'      => json_encode($vData['jp_play_count_rate']),
            'jp_pre_win_type_rate'    => json_encode($vData['jp_pre_win_type_rate']),
            'jp_accumulate_add_buff'  => $data['jp_accumulate_add_buff'],
            'jp_award_interval_count' => $data['jp_award_interval_count'],
            'jp_open_limit_count'     => $data['jp_open_limit_count'],

        ];
        $value             = [
            'type'       => 1,
            'value'      => json_encode(
                $jsonData
            ),
            'updated_at' => time(),
            'admin_id'   => $loginId,
        ];

        $val = $originData->add($value);

        return $val;
    }


}
