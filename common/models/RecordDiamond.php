<?php

namespace common\models;

use backend\models\BaseModel;
use backend\models\Tool;
use common\models\game\base\GameBase;
use Yii;


class RecordDiamond extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_record_diamond';
    }

    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'game_type'        => '游戏类型',
            'room_index'       => '房间等级',
            'prize_award_type' => '喜从天降，红包来袭等活动的类型，机台1，房间1',
            'prize_award_num'  => '总奖励数量',
            'award_sum'        => '一共中奖数量',
            'people_sum'       => '一共中奖人数数量',
            'create_time'      => '创建时间',
        ];
    }

    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        $obj = new self();
        try {
            foreach ($data as $key => $val) {
                $obj->$key = $val;
            }
            if ($obj->validate() && $obj->save()) {
                return $obj->attributes;
            } else {
                throw new MyException(json_encode($obj->getErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function deleteByDay($day, $gameType)
    {
        self::deleteAll(['create_time' => $day, 'game_type' => $gameType]);
    }


    /**
     *  查找某个游戏的热度报表
     * @param $gameType
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findByGameType($gameType)
    {
        return self::find()->where('game_type = :game_type', array(':game_type' => $gameType))->orderBy('create_time desc')->asArray()->all();
    }

    /**
     * 获取
     * @return array
     */
    public function tableList()
    {
        return self::find()->asArray()->all();
    }

    /**
     * 获取某一个游戏的送钻报表
     * @param $gameType
     * @param string $day
     * @return array
     */
    public function getByGameType($gameType, $day = '')
    {
        $newRs = array();
        $obj   = self::find()->filterWhere(['game_type' => $gameType])->orderBy('create_time DESC');
        if ($day) {
            $obj->andFilterWhere(['create_time' => $day]);
        }
        $allForGameType = $obj->asArray()->all();
        $serializeList  = [];

        //序列化
        foreach ($allForGameType as $value) {
            $serializeList[$value['create_time']][$value['room_index']][$value['prize_award_type']] = $value;
            if (!isset($newRs[$gameType][$value['create_time']])) {
                $newRs[$gameType][$value['create_time']] =
                    array(
                        '时间'   => $value['create_time'],
                        '总送钻'  => $value['award_sum'],
                        '得钻人数' => $value['people_sum'],
                        '人均送钻' => $value['people_sum'] == 0 ? 0 : round($value['award_sum'] / $value['people_sum'], 2)
                    );
            }
        }

        foreach ($serializeList as $time => $valueOfTime) {
            foreach ($valueOfTime as $level => $value) {
                $newRs[$gameType][$time][$level]['机台奖1钻石'] = Tool::examineEmpty($value['1']['prize_award_num'], 0);
                $newRs[$gameType][$time][$level]['机台奖2钻石'] = Tool::examineEmpty($value['2']['prize_award_num'], 0);
                $newRs[$gameType][$time][$level]['机台奖3钻石'] = Tool::examineEmpty($value['3']['prize_award_num'], 0);
                $newRs[$gameType][$time][$level]['房间奖1钻石'] = Tool::examineEmpty($value['4']['prize_award_num'], 0);
                $newRs[$gameType][$time][$level]['房间奖2钻石'] = Tool::examineEmpty($value['5']['prize_award_num'], 0);

                $newRs[$gameType][$time][$level]['机台奖1'] = Tool::examineEmpty($value['1']['prize_award_times'], 0);
                $newRs[$gameType][$time][$level]['机台奖2'] = Tool::examineEmpty($value['2']['prize_award_times'], 0);
                $newRs[$gameType][$time][$level]['机台奖3'] = Tool::examineEmpty($value['3']['prize_award_times'], 0);
                $newRs[$gameType][$time][$level]['房间奖1'] = Tool::examineEmpty($value['4']['prize_award_times'], 0);
                $newRs[$gameType][$time][$level]['房间奖2'] = Tool::examineEmpty($value['5']['prize_award_times'], 0);

            }
        }
        return $newRs;
    }


    /**
     *  初始化某一天的所有游戏的红包来袭
     * @param $day
     * @return bool
     */
    public function iniReportDiamond($day)
    {
        $DataGameListInfoModel = new DataGameListInfo();
        $openGame              = $DataGameListInfoModel->getOpenGame();
        $GameBaseObj = new GameBase();
        foreach ($openGame as $val) {
            $GameObj = $GameBaseObj->initGameObj($val['shortGame']);
            $locusModel = $GameObj->getModelLocusDay();
            //初始化这个游戏今天的送钻报表数据
            $locusModel->reportDiamond($day);
        }
        return true;
    }


    //初始化并且获取红包来袭
    public static function getRedPacketDiamondReport($day)
    {
        //初始化这一天的红包来袭数据
        $RecordDiamondModel = new  RecordDiamond();
        $RecordDiamondModel->iniReportDiamond($day);

        $rs = array();
        //获取所有开启的游戏
        $DataGameListInfoModel = new DataGameListInfo();
        $openGame              = $DataGameListInfoModel->getOpenGame();
        $RecordDiamondModel    = new RecordDiamond();
        foreach ($openGame as $val) {
            $data = $RecordDiamondModel->getByGameType($val['game_number']);
            foreach ($data as $key => $val2) {
                $rs[$key] = $val2;
            }
        }
        return $rs;
    }

}
