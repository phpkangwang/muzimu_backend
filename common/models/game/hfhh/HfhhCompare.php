<?php
namespace common\models\game\hfhh;

use Yii;

class HfhhCompare extends Hfhh
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_compare_hfhh';
    }

    /**
     * @return \yii\db\Connection
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
            'locus_id'         => '轨迹id',
            'compare_bet_win'  => '半比平比双比win分数',
            'compare_bet'      => '1-半比2-平比3-双比',
            'big_small'        => '0-大 1-小',
            'compare_card'     => '大小牌型2-A && 历史牌型',
            'win_number'       => '总赢次数',
            'play_number'      => '总玩次数',
            'win_score'        => '总赢分数',
            'play_score'       => '总玩分数',
            'play_three'       => '三倍鬼',
            'play_four'        => '四倍鬼',
            'play_five'        => '五倍鬼',
            'guoguan_count'    => '过关次数',
            'guoguan_score'    => '过关分数',
            'baoji_count'      => '暴击次数',
            'baoji_score'      => '暴击分数',
            'created_time'     => '创建时间(天)'
        ];
    }

    /**
     * 根据轨迹查找所有的比备记录
     * @param $locusIds
     * @return array
     */
    public function findByLocusIds($locusIds)
    {
        if(empty($locusIds)){
            return '';
        }
        $tableName = self::tableName();
        $inStr = "'".implode("','", $locusIds)."'";
        $sql = "select * from {$tableName} where locus_id in ({$inStr})";
        return Yii::$app->game_db->createCommand($sql)->queryAll();
    }

    public function getCompare_bet($compare_bet){
        switch ($compare_bet){
            case 0:
                return "续玩";
            case 1:
                return "半比";
            case 2:
                return "平比";
            case 3:
                return "双比";
            case 4:
                return "得分";
            default:
                return "";
        }
    }

    public function getbig_small($big_small){
        switch ($big_small){
            case 0:
                return "大";
            case 1:
                return "小";
            default:
                return "";
        }
    }
}
