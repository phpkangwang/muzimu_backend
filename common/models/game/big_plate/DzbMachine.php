<?php
namespace common\models\game\big_plate;

use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\game\FivepkPlayerInfo;
use common\models\odds\OddsDzb;
use common\models\OddsChangePath;
use Yii;

class DzbMachine extends Dzb
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_seo_big_plate';
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
            'seo_machine_stay_time' => 'Seo Machine Stay Time',
            'create_date' => '创建时间',
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
        $data = self::find()->joinWith('playerInfo')->where(['in','auto_id',$autoIds])->asArray()->all();
        $OddsDzbModel = $this->getModelOdds();
        $oddsObjs = $OddsDzbModel->findByTypeIds(2,$autoIds);
        foreach ($data as $key => $val){
            foreach ($oddsObjs as $odds){
                if($val['auto_id'] == $odds['odds_type_id']){
                    $data[$key] = array_merge($val,$odds);
                }
            }
        }
        return $data;
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
        $OddsDzbModel = $this->getModelOdds();
        return $OddsDzbModel->initMachineInfo($param['ids']);
//        try {
//            $level            = $param['level'];
//            $ids              = $param['ids'];
//            $DefaultOddsModel = $this->getModelDefaultOdds();
//            $obj              = $DefaultOddsModel->findByLevel($level);
//            if (empty($obj)) {
//                throw new MyException(ErrorCode::ERROR_NOT_DEFAULT_ODDS);
//            }
//            foreach ($ids as $id) {
//                //循环修改每一个机台的id，并且记录日志
//                unset($obj['id']);
//                unset($obj['room_info_list_id']);
//                unset($obj['create_date']);
//                //获取这个机台的数据
//                $machineObj = self::findOne($id);
//                $machineObj->add($obj);
//            }
//        } catch (MyException $e) {
//            echo $e->toJson($e->getMessage());
//        }
    }


    public function updateByAutoIds($autoIds, $data)
    {
        $OddsDzbModel = $this->getModelOdds();
        return $OddsDzbModel->updateMore(2,$autoIds,$data);//获取所有的机台信息
//        $objs = $this->findsByAutoIds($autoIds);
//        foreach ($objs as $val) {
//            $arr = Tool::distinctArr($data, $val, $this::attributeLabels());
//            if (!empty($arr)) {
//                $OddsChangePathModel = new OddsChangePath();
//                $postData            = array(
//                    'game_type' => $this->gameType,
//                    'type'      => $OddsChangePathModel->typeMachine,
//                    'type_id'   => $val['seo_machine_id'],
//                    'content'   => json_encode($arr, JSON_UNESCAPED_UNICODE),
//                );
//                $OddsChangePathModel->add($postData);
//            }
//        }
//
//        return self::updateAll($data, ['in', 'auto_id', $autoIds]);
//
    }
}
