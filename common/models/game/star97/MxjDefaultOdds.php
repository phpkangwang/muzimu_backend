<?php
namespace common\models\game\star97;

use common\models\DataRoomInfoList;
use Yii;

class MxjDefaultOdds extends Mxj
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'machine_list_star97_default';
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
//            [['room_info_list_id'], 'required'],
//            [['create_date'], 'safe'],
//            [['all_fruits_base_count', 'mixed_bars_base_count', 'star_reward_appearance_base_count', 'double_time_base_count', 'three_time_base_count', 'four_time_base_count', 'all_reward_base_count', 'all_orange', 'all_mango', 'all_bell', 'all_watermelon', 'seven_reward_base_count', 'five_seven', 'six_seven', 'seven_seven', 'eight_seven', 'grid_prefab_id'], 'integer'],
//            [['room_info_list_id'], 'string', 'max' => 255]
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
            'create_date' => '创建时间',
            'all_fruits_base_count' => '全盘水果-随机基数',
            'mixed_bars_base_count' => '全盘BAR-随机基数',
            'star_reward_appearance_base_count' => '明星奖出奖几率基数（填100代表百分之一）',
            'double_time_base_count' => '两倍占比数',
            'three_time_base_count' => '三倍占比数',
            'four_time_base_count' => '四倍占比数',
            'all_reward_base_count' => '全盘奖',
            'all_orange' => '全盘橘子',
            'all_mango' => '全盘芒果',
            'all_bell' => '全盘铃铛',
            'all_watermelon' => '全盘西瓜',
            'seven_reward_base_count' => '七奖',
            'five_seven' => '五个七',
            'six_seven' => '六个七',
            'seven_seven' => '七个七',
            'eight_seven' => '八个七',
            'grid_prefab_id' => '格子随机档位',
        ];
    }
    /**
     * 添加修改
     * @param $data
     * @return array
     */
    public function add($data)
    {
        try{
            foreach ( $data as $key => $val )
            {
                $this->$key = $val;
            }
            if( $this->validate() && $this->save() )
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
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        $data = self::find()->asArray()->all();
        return $data;
    }

    public function getRoomList(){
        return $this->hasOne(DataRoomInfoList::className(),['id'=>'room_info_list_id']);
    }

    /**
     * 查找基本数据
     * @param $id
     * @return DataGameListInfo|mixed|null
     */
    public function findBase($id)
    {
        $obj = self::find()->where(['id' => $id])->asArray()->one();
        return $obj;
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
