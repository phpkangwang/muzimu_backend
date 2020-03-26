<?php

namespace common\models\game\star97;

use Yii;


class RoomRewardPoolOneStar97 extends RoomRewardPoolStar97
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'room_reward_pool_star97';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
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
//            [['play_add_buff_count', 'current_buff_count', 'all_seven_total_count', 'all_cherry_total_count', 'all_red_total_count', 'all_yellow_total_count', 'all_blue_total_count'], 'number'],
//            [['current_reward_type', 'all_seven_percent', 'all_cherry_percent', 'all_red_percent', 'all_yellow_percent', 'all_blue_percent'], 'integer'],
//            [['current_reward_type', 'all_seven_percent', 'all_cherry_percent', 'all_red_percent', 'all_yellow_percent', 'all_blue_percent','play_add_buff_count', 'current_buff_count', 'all_seven_total_count', 'all_cherry_total_count', 'all_red_total_count', 'all_yellow_total_count', 'all_blue_total_count'], 'required'],
//            [['room_info_list_id'], 'string', 'max' => 25]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                     => 'ID',
            'room_info_list_id'      => '所属游戏的房间类型 6_4这种',
            'play_add_buff_count'    => '每局累积的buff值',
            'current_reward_type'    => '当前奖池累积出奖类型',
            'current_buff_count'     => '当前奖池累积buff值',
            'all_seven_total_count'  => '9个7触顶值',
            'all_seven_percent'      => '9个7份额占比',
            'all_cherry_total_count' => '全盘樱桃触顶值',
            'all_cherry_percent'     => '全盘樱桃份额占比',
            'all_red_total_count'    => '全盘红BAR触顶值',
            'all_red_percent'        => '全盘红BAR份额占比',
            'all_yellow_total_count' => '全盘黄BAR触顶值',
            'all_yellow_percent'     => '全盘黄BAR份额占比',
            'all_blue_total_count'   => '全盘蓝BAR触顶值',
            'all_blue_percent'       => '全盘蓝BAR份额占比',
        ];
    }

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->rewardId = 1;
    }
}
