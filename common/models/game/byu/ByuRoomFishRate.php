<?php

namespace common\models\game\byu;

use backend\models\Tool;
use common\models\OddsChangePath;
use Yii;

class ByuRoomFishRate extends Byu
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'room_fish_rate_config';
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'room_config_id' => '房间ID',
            'fish_config_id' => '捕鱼ID',
            'bet'            => '押注分',
            'pool'           => '奖池杯',
            'pool_add_rate'  => '奖池增加概率',
            'add_count'      => '累积值',
            'cur_count'      => '押注奖池',
            'max_count'      => '触顶值',
            'discount'       => '抽水',
        ];
    }

    /**
     *  根据房间获取所有雨的配置
     * @param $roomId
     * @return array
     */
    public function findByRoom($roomId)
    {
        return self::find()
            ->where('room_config_id = :room_config_id', array(":room_config_id" => $roomId))
            ->orderBy('fish_config_id asc')
            ->asArray()
            ->all();
    }

    /**
     * 获取奖项buff值
     * @param $level
     * @return array
     */
    public function poolAddRate($level)
    {
        return self::find()->select('pool,pool_add_rate')
            ->where('room_config_id = :room_config_id', array(":room_config_id" => $level))
            ->limit(8)
            ->asArray()
            ->all();
    }

    /**
     * 获取奖项buff值
     * @param $level
     * @param $fishId
     * @return array
     */
    public function poolAddRateAll($level, $fishId)
    {
        return self::find()
            ->where('room_config_id = :room_config_id and fish_config_id = :fish_config_id',
                array(":room_config_id" => $level, ":fish_config_id" => $fishId))
            ->asArray()
            ->all();
    }

    /**
     * 获取奖项buff值
     * @param $level
     * @param $postData
     * @throws \yii\db\Exception
     */
    public function poolAddRateUpdate($level, $postData)
    {
        foreach ($postData as $key => $val) {
            //这里要记录每次修改的值
            $obj = self::find()->where('room_config_id = :room_config_id and pool = :pool',
                array(":room_config_id" => $level, ":pool" => $key))->asArray()->one();
            $arr = Tool::distinctArr(['pool_add_rate' => $val], $obj, self::attributeLabels(),' 奖池杯是'.$key);
            if (!empty($arr)) {
                $OddsChangePathModel = new OddsChangePath();
                $postData            = array(
                    'game_type' => $this->gameType,
                    'type'      => $OddsChangePathModel->typeRoom,
                    'type_id'   => $this->getModelRoom()->findName($level),
                    'content'   => json_encode($arr, JSON_UNESCAPED_UNICODE),
                );
                $OddsChangePathModel->add($postData);
            }

            $sql = "update {$this->tableRoomFishRate} set pool_add_rate = {$val} where room_config_id = {$level} and pool = {$key}";
            self::getDb()->createCommand($sql)->query();
        }
    }

    /**
     * 修改奖池的基本信息
     * @param $level
     * @param $fishId
     * @param $postData 100_1=>2
     * @throws \yii\db\Exception
     */
    public function fishRateUpdate($level, $fishId, $postData)
    {
        foreach ($postData as $key => $val) {
            $keyArr = explode("_", $key);
            $key0   = $keyArr[0];
            $key1   = $keyArr[1];
            $query = self::find()->where(['=', 'room_config_id', $level]);
            $query->andWhere(['=', 'fish_config_id', $fishId]);
            $query->andWhere(['=', 'bet', $key0]);
            if ($key1 == "discount") {
                $obj = $query->asArray()->one();
                //修改这个值必须 记录 修改的值
                $append = " 修改的鱼: ".$this->getModelFish()->findNameById($fishId)." 押注分:".$key0;
                $arr = Tool::distinctArr(['discount'=>$val], $obj, self::attributeLabels(), $append);
                if (!empty($arr)) {
                    $OddsChangePathModel = new OddsChangePath();
                    $postData            = array(
                        'game_type' => $this->gameType,
                        'type'      => $OddsChangePathModel->typeRoom,
                        'type_id'   => $this->getModelRoom()->findName($level),
                        'content'   => json_encode($arr, JSON_UNESCAPED_UNICODE),
                    );
                    $OddsChangePathModel->add($postData);
                }

                $sql = "update {$this->tableRoomFishRate}
                        set discount = {$val}
                        where room_config_id = '{$level}' and fish_config_id = '{$fishId}'
                        and bet = '{$key0}'
                        ";
            } else {
                $query->andWhere(['=', 'pool', $key1]);
                $obj = $query->asArray()->one();
                //修改这个值必须 记录 修改的值
                $append = " 修改的鱼: ".$this->getModelFish()->findNameById($fishId)." 押注分:".$key0." 奖池".$key1;
                $arr = Tool::distinctArr(['cur_count'=>$val], $obj, self::attributeLabels(), $append);
                if (!empty($arr)) {
                    $OddsChangePathModel = new OddsChangePath();
                    $postData            = array(
                        'game_type' => $this->gameType,
                        'type'      => $OddsChangePathModel->typeRoom,
                        'type_id'   => $this->getModelRoom()->findName($level),
                        'content'   => json_encode($arr, JSON_UNESCAPED_UNICODE),
                    );
                    $OddsChangePathModel->add($postData);
                }

                $sql = "update {$this->tableRoomFishRate}
                        set cur_count = {$val}
                        where room_config_id = '{$level}' and fish_config_id = '{$fishId}'
                        and bet = '{$key0}' and pool = '{$key1}'
                        ";
            }
            self::getDb()->createCommand($sql)->query();
        }
    }
}
