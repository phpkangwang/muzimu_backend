<?php
namespace common\models\game\big_plate;

use common\models\DataRoomInfoList;
use Yii;

class DzbDefaultOdds extends Dzb
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'odds_dzb';
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_info_list_id' => '房间配置ID',
            'prefab_royal_flush' => '同花大顺',
            'prefab_royal_flush_random' => '大顺随机',
            'prefab_royal_flush_fake' => '假大顺',
            'prefab_five_of_a_kind' => '五梅',
            'prefab_five_of_a_kind_random' => '五梅随机',
            'prefab_straight_flush' => '同花小顺',
            'prefab_straight_flush_random' => '小顺随机',
            'prefab_straight_flush_fake' => '假小顺',
            'prefab_four_of_a_kind_T_T' => '四梅累积值',
            'prefab_four_of_a_kind_random_level' => '四梅出现率',
            'prefab_full_house' => '葫芦',
            'prefab_full_house_jp' => '葫芦2倍',
            'prefab_flush' => '同花',
            'prefab_flush_jp' => '同花2倍',
            'prefab_straight' => '顺子',
            'prefab_straight_jp' => '顺子2倍',
            'prefab_three_of_a_kind' => '三条',
            'prefab_three_of_a_kind_jp' => '三条2倍',
            'prefab_two_pairs' => '两对',
            'prefab_two_pairs_jp' => '两对2倍',
            'prefab_seven_better' => '一对',
            'prefab_seven_better_jp' => '一对2倍',
            'prefab_four_flush' => '四张同花',
            'prefab_four_straight' => '四张顺',
            'prefab_seven_better_keep' => '小一对',
            'prefab_joker' => '鬼牌',
            'prefab_force_seven_better' => '强制一对',
            'prefab_compare_cut_down' => '比倍砍牌',
            'prefab_five_of_a_kind_double' => '四梅2倍',
            'prefab_four_of_a_kind_double' => 'Prefab Four Of A Kind Double',
        ];
    }



    public function getRoomList()
    {
        return $this->hasOne(DataRoomInfoList::className(),['id'=>'room_info_list_id']);
    }


    /**
     * 根据房间级别获取 默认机率 配置
     * @param $level
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findByLevel($level){
        $room_info_list_id = $this->gameType."_".$level;
        return self::find()->where('room_info_list_id = :room_info_list_id',array(':room_info_list_id'=>$room_info_list_id))->asArray()->one();
    }

}
