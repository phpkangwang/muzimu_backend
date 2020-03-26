<?php
namespace common\models\game\sbb;

use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\game\FivepkPlayerInfo;
use common\models\OddsChangePath;
use Yii;


class SbbMachine extends Sbb
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_seo_super_big_boss';
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
            'order_id' => '顺序',
            'seo_machine_id' => 'Seo Machine ID',
            'room_info_list_id' => '房间配置id',
            'seo_machine_type' => 'Seo Machine Type',
            'account_id' => 'Account ID',
            'create_date' => 'Create Date',
            'prefab_five_bars_add_count' => '五鬼累积值',
            'prefab_five_bars_count' => '五鬼buff值',
            'prefab_royal_flush_six_add_count' => '六大顺累积值',
            'prefab_royal_flush_six_count' => '六大顺buff值',
            'prefab_royal_flush_add_count' => '大顺累积值',
            'prefab_royal_flush_fake' => '假大顺',
            'prefab_royal_flush_count' => '大顺buff值',
            'prefab_five_of_a_kind_add_count' => '五梅累积值',
            'prefab_five_of_a_kind_count' => '五梅buff值',
            'prefab_straight_flush_six_add_count' => '六小顺累积值',
            'prefab_straight_flush_six_count' => '六小顺buff值',
            'prefab_straight_flush_add_count' => '小顺累积值',
            'prefab_straight_flush_fake' => '假小顺',
            'prefab_straight_flush_count' => '小顺buff值',
            'prefab_four_of_a_kind_add_count' => '四梅累积值',
            'prefab_four_of_a_kind_count' => '四梅buff值',
            'prefab_four_of_a_kind_two_ten' => '四梅随机档位',
            'prefab_four_of_a_kind_seven_better_add_count' => '四梅加一对累积值',
            'prefab_four_of_a_kind_seven_better_count' => '四梅加一对buff值',
            'prefab_full_house_aaakk_add_count' => 'aaakk累积值',
            'prefab_full_house_aaakk_count' => 'aaakkbuff值',
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
            'seo_machine_play_count' => '机台总玩局数',
            'ty_machine_play_count' => '体验场总玩局数',
            'machine_auto' => '机台id',
            'prefab_force_seven_better' => '强制一对',
            'prefab_force_seven_better_count' => 'Prefab Force Seven Better Count',
            'reservation_date' => 'Reservation Date',
            'prefab_huangjin_card'=>'黄金牌',
            'prefab_baijin_card'=>'白金牌',
            'prefab_two_three_of_a_kind' => '两个三条',
            'prefab_six_flush' => '6同花',
            'prefab_six_straight' => '6顺子',
            'prefab_three_pairs' => '三对',
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
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        $data = self::find()->orderBy('room_info_list_id,order_id')->asArray()->all();
        return $data;
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
