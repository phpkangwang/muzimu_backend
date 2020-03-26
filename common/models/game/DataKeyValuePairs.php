<?php

namespace common\models\game;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\redis\MyRedis;
use phpDocumentor\Reflection\Types\Boolean;
use Yii;

/**
 * This is the model class for table "data_key_value_pairs".
 *
 * @property integer $id
 * @property integer $value_int
 * @property string $value_varchar
 * @property string $value_comment
 * @property string $value_name
 */
class DataKeyValuePairs extends BaseModel
{

    const ROBOT_OPEN = 23;//小洛开关

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_key_value_pairs';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
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
            //[['id'], 'required'],
            [['id', 'value_int'], 'integer'],
            [['value_varchar', 'value_comment', 'value_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'id',
            'value_int'     => 'Value Int',
            'value_varchar' => 'Value Varchar',
            'value_comment' => 'Value Comment',
            'value_name'    => 'Value Name',
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
                $MyRedisObj = new MyRedis();
                $MyRedisObj->clear("game:DataKeyValuePairs*");
                return $this->attributes;
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
     * @return DataGameListInfo|mixed|null
     */
    public function findBase($id)
    {
        $redisKey  = "game:DataKeyValuePairs:" . $id;
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            $obj = self::find()->where(['id' => $id])->asArray()->one();
            $this->MyRedis->set($redisKey, json_encode($obj));
            return $obj;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
     *  明星97查找房间配置
     * @param $gameType
     * @param $valueInt
     * @param $roomIndex
     * @return array
     */
    public function findByTypeIntIndex($gameType, $valueInt, $roomIndex)
    {
        return self::find()->where('game_type = :game_type and value_int = :value_int and room_index = :room_index', array(
            ':game_type' => $gameType, ':value_int' => $valueInt, ':room_index' => $roomIndex
        ))->asArray()->all();
    }

    public function findByValueName($valueName)
    {
        return self::find()->where('value_name = :value_name', array(':value_name' => $valueName))->asArray()->one();
    }

    /**
     *  列表
     * @return array
     */
    public function tableList($gameType)
    {
        $redisKey  = "game:DataKeyValuePairs:tableList:" . $gameType;
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            $data = self::find()->andFilterWhere(['game_type' => $gameType])->asArray()->all();
            $this->MyRedis->set($redisKey, json_encode($data));
            return $data;
        } else {
            return json_decode($redisData, true);
        }
    }


    /**
     *   判断是否是新人--暂时只用到paman  id号是 10000~~~10003
     * @param $playerInfo
     * @param $keyValuePairs
     *  total_play 和 总玩局数门槛(value_varchar);  小于新玩家
     *  total_contribution 和 PAMAN新玩家补偿门槛:总贡献度门槛(value_int);  小于新玩家
     *  总贡献度门槛/总玩局数门槛(&)  total_contribution/ total_play 小于 0.5 新玩家
     * @return bool
     */
    public function IsNewPlayerPaman($playerInfo, $keyValuePairs)
    {
        try {
            if ($playerInfo['total_play'] == 0) {
                return true;
            }

            foreach ($keyValuePairs as $val) {
                if ($val['id'] == 10000) {
                    $总玩局数门槛 = $val['value_int'];
                }
                if ($val['id'] == 10001) {
                    $补偿门槛 = $val['value_int'];
                }
                if ($val['id'] == 10002) {
                    $除后门槛 = $val['value_varchar'];
                }
            }

            if (!isset($总玩局数门槛) || !isset($补偿门槛) || !isset($除后门槛)) {
                throw new MyException(ErrorCode::ERROR_DATABASE_CONFIG);
            }

            return $playerInfo['total_play'] < $总玩局数门槛;

//            if( $playerInfo['total_play'] < $总玩局数门槛 && $playerInfo['total_contribution'] < $补偿门槛 && ($playerInfo['total_contribution'] / $playerInfo['total_play']) < $除后门槛){
//                return true;
//            }else{
//                return false;
//            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }
}
