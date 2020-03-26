<?php

namespace common\models\game\star97;

use Yii;


class RoomRewardPoolThreeStar97 extends RoomRewardPoolStar97
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'room_reward_pool_three_star97';
    }

    public static function getDb()
    {
        return Yii::$app->get('core_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['play_add_buff_count', 'current_buff_count', 'eight_seven_total_count', 'seven_seven_total_count', 'six_seven_total_count', 'five_seven_total_count'], 'number'],
//            [['current_reward_type', 'eight_seven_percent', 'seven_seven_percent', 'six_seven_percent', 'five_seven_percent'], 'integer'],
//            [['current_reward_type', 'eight_seven_percent', 'seven_seven_percent', 'six_seven_percent', 'five_seven_percent','play_add_buff_count', 'current_buff_count', 'eight_seven_total_count', 'seven_seven_total_count', 'six_seven_total_count', 'five_seven_total_count'], 'number'],
//            [['room_info_list_id'], 'string', 'max' => 25]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                      => 'ID',
            'room_info_list_id'       => '所属游戏的房间类型 6_4这种',
            'play_add_buff_count'     => '每局累积的buff值',
            'current_reward_type'     => '当前奖池累积出奖类型',
            'current_buff_count'      => '当前奖池累积buff值',
            'eight_seven_total_count' => '8个7触顶值',
            'eight_seven_percent'     => '8个7份额占比',
            'seven_seven_total_count' => '7个7触顶值',
            'seven_seven_percent'     => '7个7份额占比',
            'six_seven_total_count'   => '6个7触顶值',
            'six_seven_percent'       => '6个7份额占比',
            'five_seven_total_count'  => '5个7触顶值',
            'five_seven_percent'      => '5个7份额占比',
        ];
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->rewardId = 3;
    }
}
