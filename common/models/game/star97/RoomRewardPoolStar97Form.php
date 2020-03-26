<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-11-15
 * Time: 13:50
 */

namespace common\models\game\star97;

/**
 * This is the model class for table "room_reward_pool_two_star97".
 *
 * @property integer $id
 * @property string $room_info_list_id
 * @property double $play_add_buff_count
 * @property integer $current_reward_type
 * @property double $current_buff_count
 * @property double $all_watermelon_total_count
 * @property integer $all_watermelon_percent
 * @property double $all_bell_total_count
 * @property integer $all_bell_percent
 * @property double $all_orange_total_count
 * @property integer $all_orange_percent
 * @property double $all_mango_total_count
 * @property integer $all_mango_percent
 * @property double $all_seven_total_count
 * @property integer $all_seven_percent
 * @property double $all_cherry_total_count
 * @property integer $all_cherry_percent
 * @property double $all_red_total_count
 * @property integer $all_red_percent
 * @property double $all_yellow_total_count
 * @property integer $all_yellow_percent
 * @property double $all_blue_total_count
 * @property integer $all_blue_percent
 * @property double $eight_seven_total_count
 * @property integer $eight_seven_percent
 * @property double $seven_seven_total_count
 * @property integer $seven_seven_percent
 * @property double $six_seven_total_count
 * @property integer $six_seven_percent
 * @property double $five_seven_total_count
 * @property integer $five_seven_percent
 */
class RoomRewardPoolStar97Form extends \backend\models\BaseModel
{


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_info_list_id' => '所属游戏的房间类型',
            'play_add_buff_count' => '每局累积的buff值',
            'current_reward_type' => '当前奖池累积出奖类型',
            'current_buff_count' => '当前奖池累积buff值',
            'eight_seven_total_count' => '8个7触顶值',
            'eight_seven_percent' => '8个7份额占比',
            'seven_seven_total_count' => '7个7触顶值',
            'seven_seven_percent' => '7个7份额占比',
            'six_seven_total_count' => '6个7触顶值',
            'six_seven_percent' => '6个7份额占比',
            'five_seven_total_count' => '5个7触顶值',
            'five_seven_percent' => '5个7份额占比',
            'all_watermelon_total_count' => '全盘西瓜触顶值',
            'all_watermelon_percent' => '全盘西瓜份额占比',
            'all_bell_total_count' => '全盘铃铛触顶值',
            'all_bell_percent' => '全盘铃铛份额占比',
            'all_orange_total_count' => '全盘橘子触顶值',
            'all_orange_percent' => '全盘橘子份额占比',
            'all_mango_total_count' => '全盘芒果触顶值',
            'all_mango_percent' => '全盘芒果份额占比',
            'all_seven_total_count' => '9个7触顶值',
            'all_seven_percent' => '9个7份额占比',
            'all_cherry_total_count' => '全盘樱桃触顶值',
            'all_cherry_percent' => '全盘樱桃份额占比',
            'all_red_total_count' => '全盘红BAR触顶值',
            'all_red_percent' => '全盘红BAR份额占比',
            'all_yellow_total_count' => '全盘黄BAR触顶值',
            'all_yellow_percent' => '全盘黄BAR份额占比',
            'all_blue_total_count' => '全盘蓝BAR触顶值',
            'all_blue_percent' => '全盘蓝BAR份额占比',
        ];
    }

    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try{
            foreach ( $data as $key => $val )
            {
                $this->$key = $val;
            }
            if( $this->save() )
            {
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 根据房间id获取数据
     * @return array
     */
    public function findByRoomId($roomId)
    {
        return self::find()->where("room_info_list_id=:room_info_list_id",[':room_info_list_id'=>$roomId])->asArray()->one();
    }
}