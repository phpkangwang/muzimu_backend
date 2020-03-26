<?php

namespace common\models;

use backend\models\BaseModel;
use backend\models\Tool;
use common\models\game\base\GameBase;
use Yii;


class RecordHits extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_record_hits';
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
            'people'           => '新增人数',
            'profit_json'      => '盈利json',
            'people_game_json' => '游戏人数',
            'play_num_json'    => '总玩局数',
            'odds_json'        => '游戏几率',
            'award_json'       => '中奖率',
            'create_time'      => '创建时间',
        ];
    }

    public function deleteByDay($day, $gameType)
    {
        self::deleteAll(['create_time' => $day, 'game_type' => $gameType]);
    }

    /**
     *  生成这一天的数据
     * @param $time 2019-01-01
     * @param $gameName HFH
     * @return bool
     */
    public function RecordToday($time, $gameName)
    {
        $stime = $time;
        $etime = $time . " 23:59:59";
        $GameBaseObj = new GameBase();
        $GameObj = $GameBaseObj->initGameObj($gameName);
        $prizeModelObj = $GameObj->getModelPrizeDay();
        $prizeModelObj->reportHits($stime, $etime);
        return true;
    }

    /**
     *  查找某个游戏的人气报表
     * @param $gameType
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findByGameType($gameType, $oddsType)
    {
        return self::find()->where('game_type = :game_type and odds_type = :odds_type',
            array(':game_type' => $gameType, ':odds_type' => $oddsType))
            ->orderBy('create_time desc')
            ->asArray()
            ->all();
    }
}
