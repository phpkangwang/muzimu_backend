<?php

namespace common\models\game\att2;

use common\models\DataRoomInfoList;
use Yii;


class AttDefaultOdds extends Att
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_default_odds_att2';
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
            'room_info_list_id' => 'Room Info List ID',
            'create_date' => 'Create Date',
            'four_of_kind_random_base_count' => 'Four Of Kind Random Base Count',
            'straight_flush_random_base_count' => 'Straight Flush Random Base Count',
            'royal_flush_random_base_count' => 'Royal Flush Random Base Count',
            'five_of_kind_random_base_count' => 'Five Of Kind Random Base Count',
            'prefab_royal_flush' => 'Prefab Royal Flush',
            'prefab_royal_flush_random' => 'Prefab Royal Flush Random',
            'prefab_royal_flush_fake' => 'Prefab Royal Flush Fake',
            'prefab_five_of_a_kind' => 'Prefab Five Of A Kind',
            'prefab_five_of_a_kind_random' => 'Prefab Five Of A Kind Random',
            'prefab_straight_flush' => 'Prefab Straight Flush',
            'prefab_straight_flush_random' => 'Prefab Straight Flush Random',
            'prefab_straight_flush_fake' => 'Prefab Straight Flush Fake',
            'prefab_four_of_a_kind_T_T' => 'Prefab Four Of A Kind  T  T',
            'prefab_four_of_a_kind_random_level' => 'Prefab Four Of A Kind Random Level',
            'prefab_full_house' => 'Prefab Full House',
            'prefab_flush' => 'Prefab Flush',
            'prefab_straight' => 'Prefab Straight',
            'prefab_three_of_a_kind' => 'Prefab Three Of A Kind',
            'prefab_two_pairs' => 'Prefab Two Pairs',
            'prefab_seven_better' => 'Prefab Seven Better',
            'prefab_four_flush' => 'Prefab Four Flush',
            'prefab_four_straight' => 'Prefab Four Straight',
            'prefab_seven_better_keep' => 'Prefab Seven Better Keep',
            'prefab_joker' => 'Prefab Joker',
            'prefab_force_seven_better' => 'Prefab Force Seven Better',
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
