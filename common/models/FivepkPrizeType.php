<?php

namespace common\models;

use backend\models\redis\MyRedis;
use Yii;

/**
 * This is the model class for table "fivepk_prize_type".
 *
 * @property integer $id
 * @property integer $game_type
 * @property integer $prize_type
 * @property string $prize_name
 * @property integer $level
 * @property integer $rate
 * @property integer $status
 */
class FivepkPrizeType extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_prize_type';
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
            [['game_type', 'prize_type', 'level', 'rate', 'status'], 'integer'],
            [['prize_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'game_type'  => '游戏类型1-火凤凰',
            'prize_type' => '奖型',
            'prize_name' => '名字',
            'level'      => '档位',
            'rate'       => '倍率',
            'status'     => '状态',
        ];
    }

    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try {
            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            if ($this->validate() && $this->save()) {
                $this->MyRedis->clear("game:FivepkPrizeType*");
                return $this->attributes;
            } else {
                throw new MyException(json_encode($this->getErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 根据条件查询数据
     * @param $gameType
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findByGameType($gameType)
    {
        return self::find()->where('game_type = :game_type', array(':game_type' => $gameType))->orderBy('sort asc')->asArray()->all();
    }

    static $findByGameTypeIndex;

    /**
     * 根据条件查询数据
     * @param $gameType
     * @param string $indexBy
     * @param bool $isJp
     * @return mixed
     */
    public static function findByGameTypeIndex($gameType, $indexBy = '', $isJp = false)
    {
        $key = $gameType . '_' . $indexBy . '_' . ($isJp ? '1' : '2');

        if (isset(self::$findByGameTypeIndex[$key])) {
            return self::$findByGameTypeIndex[$key];
        }

        $redisName = 'findPrizeId';
        $myRedis   = new MyRedis();

        self::$findByGameTypeIndex[$key] = $myRedis->readCacheHash($redisName, $key);
        if (empty(self::$findByGameTypeIndex[$key])) {
            $obj = self::find();
            if ($indexBy) {
                $obj->indexBy($indexBy);
            }
            $obj->where('game_type = :game_type', array(':game_type' => $gameType));
            if ($isJp) {
                $obj->andWhere(['is_jp' => 1]);
            } else {
                $obj->andWhere(['is_jp' => 2]);
            }
            self::$findByGameTypeIndex[$key] = $obj->orderBy('sort asc')->asArray()->all();
            $myRedis->writeCacheHash($redisName, $key, self::$findByGameTypeIndex[$key]);
        }

        return self::$findByGameTypeIndex[$key];
    }

    /**
     * 获取所有的父级奖   一般用来显示下拉框
     * @param $game_type
     * @param int $bigAward
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public function getPrizeTypeParentList($game_type, $bigAward = 1)
    {
        $redisKey  = "game:FivepkPrizeType:FivepkPrizeParentType:" . $game_type . ":" . $bigAward;
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {

            if ($bigAward == 2) {
                //是大奖
                $obj = self::find()->where(['parent' => 0, 'game_type' => $game_type, 'status' => 10, 'big_award' => 2])->orderBy('sort ASC')->asArray()->all();
            } else {
                $obj = self::find()->where(['parent' => 0, 'game_type' => $game_type, 'status' => 10])->orderBy('sort ASC')->asArray()->all();
            }
            $this->MyRedis->set($redisKey, json_encode($obj));
            return $obj;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
     * 获取所有的奖 包括父级奖和子级奖
     * @param $game_type
     * @param int $bigAward
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public function getPrizeTypeList($game_type, $bigAward = 1)
    {
        $redisKey  = "game:FivepkPrizeType:" . $game_type . ":" . $bigAward;
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            if ($bigAward == 2) {
                //是大奖
                $obj = self::find()->where(['game_type' => $game_type, 'status' => 10, 'big_award' => 2])->orderBy('sort ASC')->asArray()->all();
            } else {
                $obj = self::find()->where(['game_type' => $game_type, 'status' => 10])->orderBy('sort ASC')->asArray()->all();
            }
            $this->MyRedis->set($redisKey, json_encode($obj));
            return $obj;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
     * 根据奖项id获取奖项名称
     * @param $game_type
     * @param $id
     * @return array
     */
    public function getPrizeTypeNameById($game_type, $id)
    {
        $obj = self::find()->where(['game_type' => $game_type, 'id' => $id, 'status' => 10])->asArray()->one();
        return $obj['prize_name'];
    }

    /**
     *  返回将型id
     * @param $id
     * @return string
     */
    public function getNameById($id)
    {
        $obj = self::findOne($id);
        return empty($obj) ? "" : $obj->prize_name;
    }

    public function del($id)
    {
        $this->MyRedis->clear("game:FivepkPrizeType*");
        return self::deleteAll("id=:id", [':id' => $id]);
    }

    //获取所有的子类奖项包括自己
    public function getAllSon($parentId)
    {
        $data  = self::find()->where('parent = :parent', array(':parent' => $parentId))->asArray()->all();
        $idArr = array_column($data, 'id');
        array_push($idArr, $parentId);
        return $idArr;
    }


}
