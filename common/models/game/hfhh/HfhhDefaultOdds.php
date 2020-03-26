<?php
namespace common\models\game\hfhh;

use common\models\DataRoomInfoList;
use Yii;

class HfhhDefaultOdds extends Hfhh
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_default_odds_firephoenixh';
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
            'room_info_list_id' => '房间配置id',
            'create_date' => '创建时间',
            'prefab_five_bars' => '五鬼',
            'prefab_royal_flush' => '同花大顺',
            'prefab_royal_flush_fake' => '假大顺',
            'prefab_five_of_a_kind' => '五梅',
            'prefab_straight_flush' => '同花小顺',
            'prefab_straight_flush_fake' => '假小顺',
            'prefab_four_of_a_kind_joker_two' => '正宗大四梅',
            'prefab_four_of_a_kind_J_A' => '大四梅累积值',
            'prefab_four_of_a_kind_ja' => '大四梅出现率',
            'prefab_four_of_a_kind_T_T' => '小四梅累积值',
            'prefab_four_of_a_kind_two_ten' => '小四梅出现率',
            'prefab_four_of_a_kind_two_ten_two' => '连庄开关',
            'prefab_full_house' => '葫芦',
            'prefab_flush' => '同花',
            'prefab_straight' => '顺子',
            'prefab_three_of_a_kind' => '三条',
            'prefab_two_pairs' => '两对',
            'prefab_seven_better' => '一对',
            'prefab_four_flush' => '四张同花',
            'prefab_four_straight' => '四张顺',
            'prefab_seven_better_keep' => '小一对',
            'prefab_joker' => '鬼牌',
            'prefab_force_seven_better' => '强制一对',
            'prefab_compare_cut_down' => '比倍砍牌',
            'prefab_compare_seven_joker' => '比倍7鬼翻倍',
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

    /**
     * 关联房间信息
     * @return \yii\db\ActiveQuery
     */
    public function getRoomList()
    {
        $result = DataRoomInfoList::find()->filterWhere(['id'=>$this->room_info_list_id])->one();
        return $result;
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
     * 当老玩家没有数据的时候，首先得初始化数据
     * 每次初始化数据的时候首先得删除掉这个玩家的所有的旧数据
     * @param $accountId
     * @return bool
     */
    public function initUserOdds($accountId)
    {
        $data = self::find()->asArray()->orderBy('account_id asc')->one();
        self::deleteAll("account_id=:account_id", [':account_id' => $accountId]);
        $data['account_id'] = $accountId;
        $return = $this->add($data);
        return $return;
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
