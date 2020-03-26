<?php

namespace common\models\game\star97;

use Yii;


class RoomRewardPoolTwoStar97 extends RoomRewardPoolStar97
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'room_reward_pool_two_star97';
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
//            [['play_add_buff_count', 'current_buff_count', 'all_watermelon_total_count', 'all_bell_total_count', 'all_orange_total_count', 'all_mango_total_count'], 'number'],
//            [['current_reward_type', 'all_watermelon_percent', 'all_bell_percent', 'all_orange_percent', 'all_mango_percent'], 'integer'],
//            [['current_reward_type', 'all_watermelon_percent', 'all_bell_percent', 'all_orange_percent', 'all_mango_percent','play_add_buff_count', 'current_buff_count', 'all_watermelon_total_count', 'all_bell_total_count', 'all_orange_total_count', 'all_mango_total_count'], 'number'],
//            [['room_info_list_id'], 'string', 'max' => 25]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                         => 'ID',
            'room_info_list_id'          => '所属游戏的房间类型 6_4这种',
            'play_add_buff_count'        => '每局累积的buff值',
            'current_reward_type'        => '当前奖池累积出奖类型',
            'current_buff_count'         => '当前奖池累积buff值',
            'all_watermelon_total_count' => '全盘西瓜触顶值',
            'all_watermelon_percent'     => '全盘西瓜份额占比',
            'all_bell_total_count'       => '全盘铃铛触顶值',
            'all_bell_percent'           => '全盘铃铛份额占比',
            'all_orange_total_count'     => '全盘橘子触顶值',
            'all_orange_percent'         => '全盘橘子份额占比',
            'all_mango_total_count'      => '全盘芒果触顶值',
            'all_mango_percent'          => '全盘芒果份额占比',
        ];
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->rewardId = 2;
    }
}
