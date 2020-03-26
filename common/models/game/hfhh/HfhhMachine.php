<?php
namespace common\models\game\hfhh;

use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\game\FivepkPlayerInfo;
use common\models\OddsChangePath;
use Yii;


class HfhhMachine extends Hfhh
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_seo_firephoenixh';
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
            'auto_id' => 'Auto ID',
            'order_id' => '排序',
            'seo_machine_id' => 'Seo Machine ID',
            'room_info_list_id' => '房间配置id',
            'seo_machine_type' => 'Seo Machine Type',
            'account_id' => 'Account ID',
            'create_date' => '创建时间',
            'prefab_five_bars' => '五鬼',
            'prefab_five_bars_count' => '五鬼Buffer',
            'prefab_royal_flush' => '大顺',
            'prefab_royal_flush_fake' => '假大顺',
            'prefab_royal_flush_count' => '同花大顺Buffer',
            'prefab_five_of_a_kind' => '五梅',
            'prefab_five_of_a_kind_count' => '五梅Buffer',
            'prefab_five_of_a_kind_compare' => 'Prefab Five Of A Kind Compare',
            'prefab_five_of_a_kind_compare_record' => 'Prefab Five Of A Kind Compare Record',
            'prefab_straight_flush' => '小顺',
            'prefab_straight_flush_fake' => '假小顺',
            'prefab_straight_flush_count' => '小顺Buffer',
            'prefab_four_of_a_kind_joker_two' => '正宗大四梅',
            'prefab_four_of_a_kind_Joker_count_two' => '正宗大四梅累积Buffer',
            'prefab_four_of_a_kind_Joker_two_fourteen_two' => 'Prefab Four Of A Kind  Joker Two Fourteen Two',
            'prefab_four_of_a_kind_Joker_two_fourteen_record' => 'Prefab Four Of A Kind  Joker Two Fourteen Record',
            'prefab_four_of_a_kind_J_A' => '大四梅累积值',
            'prefab_four_of_a_kind_J_A_count' => '大四梅累积值Buffer',
            'prefab_four_of_a_kind_ja' => '大四梅',
            'prefab_four_of_a_kind_T_T' => '小四梅累积值',
            'prefab_four_of_a_kind_T_T_count' => '小四梅累积值Buffer',
            'prefab_four_of_a_kind_two_ten' => '小四梅',
            'prefab_four_of_a_kind_two_ten_two' => '连庄开关',
            'prefab_four_of_a_kind_two_ten_continue' => 'Prefab Four Of A Kind Two Ten Continue',
            'prefab_four_of_a_kind_two_ten_continue_count' => '连庄Buffer',
            'prefab_four_of_a_kind_two_ten_continue_record' => 'Prefab Four Of A Kind Two Ten Continue Record',
            'prefab_four_of_a_kind_two_ten_continue_rate' => 'Prefab Four Of A Kind Two Ten Continue Rate',
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
            'seo_machine_play_count' => 'Seo Machine Play Count',
            'machine_auto' => 'Machine Auto',
            'compare_history_cards' => 'Compare History Cards',
            'prefab_force_seven_better' => '强制一对',
            'prefab_force_seven_better_count' => 'Prefab Force Seven Better Count',
            'prefab_compare_buff' => '比倍Buffer',
            'prefab_compare_cut_down' => '比倍砍牌',
            'prefab_compare_cut_down_count' => 'Prefab Compare Cut Down Count',
            'prefab_compare_seven_joker' => '比倍7鬼翻倍',
            'reservation_date' => 'Reservation Date',
        ];
    }

    /**
     * 关联房间信息
     * @return \yii\db\ActiveQuery
     */
    public function getRoomList()
    {
        return $this->hasOne(DataRoomInfoList::className(),['id'=>'room_info_list_id']);
    }

    /**
     * 关联玩家信息
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(),['account_id'=>'account_id']);
    }

    public function add($data)
    {
        try {
            //修改这个值必须 记录 修改的值
            $arr = Tool::distinctArr($data, $this->attributes, self::attributeLabels()  );
            if (!empty($arr)) {
                $OddsChangePathModel = new OddsChangePath();
                $postData            = array(
                    'game_type' => $this->gameType,
                    'type'      => $OddsChangePathModel->typeMachine,
                    'type_id'   => $this->seo_machine_id,
                    'content'   => json_encode($arr, JSON_UNESCAPED_UNICODE),
                );
                $OddsChangePathModel->add($postData);

                foreach ($data as $key => $val) {
                    $this->$key = $val;
                }
                if ($this->save()) {
                    return $this->attributes;
                } else {
                    throw new MyException(implode(",", $this->getFirstErrors()));
                }
            }
            return true;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 获得状态
     * @return null|string
     */
    public function findStatus($data)
    {
        $status = null;
        if($data['seo_machine_type'] == 0){
            $status = '空闲';
        }elseif ($data['seo_machine_type'] == 1){
            if($data['machine_auto'] == 1){
                $status = '自动';
            }else {
                $status = '在线';
            }
        }elseif ($data['seo_machine_type'] == 2){
            $status = '留机';
        }
        return $status;
    }

    /**
     * 根据auto_id查询多条数据
     * @param $autoIds array
     * @return array
     */
    public function findsByAutoIds($autoIds)
    {
        return self::find()->joinWith('playerInfo')->where(['in','auto_id',$autoIds])->asArray()->all();
    }

    /**
     * 获取所有的机台列表
     * @return array|\yii\db\ActiveRecord[]
     */
    public function tableList()
    {
        return self::find()->orderBy('room_info_list_id,order_id')->asArray()->all();
    }

    /**
     *  根据房间id获取机台列表
     * @param $roomId
     * @return array
     */
    public function findsByRoomId($roomId){
        return self::find()->where('room_info_list_id = :roomId and status = 1',array(':roomId'=>$roomId))->orderBy('order_id desc')->asArray()->all();
    }

    /**
     * 获取房间机台最大的序列号
     * @param $roomId
     * @return array
     */
    public function findRoomMaxOrderId($roomId){
        $obj = self::find()->where('room_info_list_id = :roomId and status = 2',array(':roomId'=>$roomId))->orderBy('order_id asc')->asArray()->one();
        if( !empty($obj) ){
            if($obj['order_id'] == 1){
                return "";
            }
            $order_id = $obj['order_id'] - 1;
            $obj = self::find()->where('room_info_list_id = :roomId  and order_id = :order_id',array(':roomId'=>$roomId, ':order_id'=>$order_id))->asArray()->one();
            return $obj;
        }
        return self::find()->where(['room_info_list_id'=>$roomId])->orderBy('order_id desc')->asArray()->one();
    }

    /**
     * 删除机台
     * @param $autoId  主键id
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function del($autoId){
        try{
            $obj = self::findOne($autoId);
            if(empty($obj)){
                throw new MyException( ErrorCode::ERROR_GAME_MACHINE_NOT_EXIST );
            }
            $RoomMachineObjs = $this->findsByRoomId($obj->room_info_list_id);
            if( count($RoomMachineObjs) == 1){
                throw new MyException( ErrorCode::ERROR_GAME_MACHINE_HAS_ONE );
            }
            $obj->delete();
            return true;
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   初始化默认机率
     */
    public function initDefault($param)
    {
        try {
            $level            = $param['level'];
            $ids              = $param['ids'];
            $DefaultOddsModel = $this->getModelDefaultOdds();
            $obj              = $DefaultOddsModel->findByLevel($level);
            if (empty($obj)) {
                throw new MyException(ErrorCode::ERROR_NOT_DEFAULT_ODDS);
            }
            foreach ($ids as $id) {
                //循环修改每一个机台的id，并且记录日志
                unset($obj['id']);
                unset($obj['room_info_list_id']);
                unset($obj['create_date']);
                //获取这个机台的数据
                $machineObj = self::findOne($id);
                $machineObj->add($obj);
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

}
