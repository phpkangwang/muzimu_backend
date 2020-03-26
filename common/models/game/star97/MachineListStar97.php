<?php
namespace common\models\game\star97;

use common\models\DataRoomInfoList;
use common\models\game\FivepkPlayerInfo;
use common\models\game\star97\core\MachineRewardPoolStar97;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "machine_list_star97".
 *
 * @property string $auto_id
 * @property integer $order_id
 * @property string $seo_machine_id
 * @property string $room_info_list_id
 * @property integer $grid_prefab_id
 * @property integer $seo_machine_type
 * @property string $account_id
 * @property integer $seo_machine_play_count
 * @property integer $gift
 * @property string $reservation_date
 * @property integer $machine_auto
 * @property string $create_date
 * @property integer $all_fruits_base_count
 * @property integer $mixed_bars_base_count
 * @property integer $star_reward_appearance_base_count
 * @property integer $double_time_base_count
 * @property integer $three_time_base_count
 * @property integer $four_time_base_count
 */
class MachineListStar97 extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'machine_list_star97';
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
            [['order_id', 'grid_prefab_id', 'seo_machine_type', 'account_id', 'seo_machine_play_count', 'gift', 'machine_auto','all_fruits_base_count','mixed_bars_base_count','star_reward_appearance_base_count','double_time_base_count','three_time_base_count','four_time_base_count'], 'integer'],
            [['reservation_date'], 'safe'],
            [['seo_machine_id'], 'string', 'max' => 50],
            [['room_info_list_id'], 'string', 'max' => 20],
            [['create_date'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'auto_id' => '自增id',
            'order_id' => '顺序',
            'seo_machine_id' => '机台编号',
            'room_info_list_id' => 'Room Info List ID',
            'grid_prefab_id' => 'Grid Prefab ID',
            'seo_machine_type' => '机台状态',
            'account_id' => '玩家id',
            'seo_machine_play_count' => '机台的总局数',
            'gift' => '机器的实时彩金，因房间不同累积速度和底值有较大差异',
            'reservation_date' => '留机到期时间',
            'machine_auto' => '自动状态:0-非自动 1-自动',
            'create_date' => '创建时间',
            'all_fruits_base_count' => '全盘水果-随机基数',
            'mixed_bars_base_count' => '全盘BAR-随机基数',
            'star_reward_appearance_base_count' => '明星奖出奖几率',
            'double_time_base_count' => '两倍占比数',
            'three_time_base_count' => '三倍占比数',
            'four_time_base_count' => '四倍占比数',
            'all_reward_base_count' => '四个全盘奖的出现率',
            'all_orange' => '全盘橘子的出现率',
            'all_mango' => '全盘芒果的出现率',
            'all_bell' => '全盘铃铛的出现率',
            'all_watermelon' => '全盘西瓜的出现率',
            'seven_reward_base_count' => '七奖的出现率',
            'five_seven' => '五个七',
            'six_seven' => '六个七',
            'seven_seven' => '七个七',
            'eight_seven' => '八个七',
            'current_reward_type' => '当前奖池累积出奖类型',
        ];
    }

    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(),['account_id'=>'account_id']);
    }

    public function getRoomList()
    {
        return $this->hasOne(DataRoomInfoList::className(),['id'=>'room_info_list_id']);
    }

    public function getJiangchi1()
    {
        $jiangchi1 = MachineRewardPoolStar97::find()->filterWhere(['like','seo_machine_id',$this->seo_machine_id])->andFilterWhere(['pool_id'=>1])->one();
        return $jiangchi1;
    }

    public function getJiangchi2()
    {
        $jiangchi2 = MachineRewardPoolStar97::find()->filterWhere(['like','seo_machine_id',$this->seo_machine_id])->andFilterWhere(['pool_id'=>2])->one();
        return $jiangchi2;
    }

    public function getJiangchi3()
    {
        $jiangchi3 = TyMachinePrefabBigReward::find()->filterWhere(['like','seo_machine_id',$this->seo_machine_id])->one();
        return $jiangchi3;
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
     * 根据auto_id修改多条数据
     * @param $autoIds  auto_id
     * @param $data     表键值对
     * @return int
     */
    public function updateByAutoIds($autoIds, $data,$loginId){
        return  self::updateAll($data,['in','auto_id',$autoIds]);
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
     * 删除机台
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function deleteMachine($params = array()){
        $seo_machine_id = ArrayHelper::map(MachineListStar97::find()->filterWhere($params)->all(),'seo_machine_id','seo_machine_id');
        $model = new MachineListStar97();

        $data = $model->findOne($params);

        if(empty($data)){
            throw new Exception('查询无机台号');
        }
        TyMachinePrefabBigReward::deleteAll(['in','seo_machine_id',$seo_machine_id]);
        $row = $model->deleteAll($params);

        return $row;
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
     * 彩金复位
     * @return  mixed
     */
    public function setGift($gameName,$room)
    {
        /*
                   体验场        初级场        中级场         高级场
                    50000         100000         248800        518800
       不能大于这些数值
         */

        if(!isset(Yii::$app->params['game'][$gameName])){
            return [];
        }

        $chineseGameName = Yii::$app->params['game'][$gameName];
        $gameType = Yii::$app->params[$chineseGameName]['gameType'];
        $roomId = $gameType . '_' . $room;

        $config = Yii::$app->params['star97setGift'];
        $giftNum = $config[$room];

//        switch ($roomId) {
//            case $gameType . '_1':
//                //体验场
//                $giftNum = 50000;
//                break;
//            case $gameType . '_2':
//                //初级场
//                $giftNum = 100000;
//                break;
//            case $gameType . '_3':
//                //中级场
//                $giftNum = 248800;
//                break;
//
//            case $gameType . '_4':
//                //高级场
//                $giftNum = 518800;
//                break;
//            default:
//                ;
//        }

        $data = self::updateAll(array('gift' => $giftNum), "gift<$giftNum and room_info_list_id='$roomId'" );

        return $data;
    }


    /**
     * 修改彩金
     * @return  mixed
     */
    public function updateGift($id,$gameName,$gift)
    {
        /*
                   体验场        初级场        中级场         高级场
                    50000         100000         248800        518800

       不能大于这些数值
         */

        if(!isset(Yii::$app->params['game'][$gameName])){
            return [];
        }

        $obj = self::findOne(['auto_id' => $id]);

        $chineseGameName = Yii::$app->params['game'][$gameName];
        $gameType = Yii::$app->params[$chineseGameName]['gameType'];
        $config = Yii::$app->params['star97setGift'];

        $key = explode($gameType . '_', $obj->room_info_list_id);

        $giftNum = $config[$key[1]];


//        switch ($obj->room_info_list_id) {
//            case $gameType . '_1':
//                //体验场
//                $giftNum = $config[1];
//                break;
//            case $gameType . '_2':
//                //初级场
//                $giftNum = $config[2];
//                break;
//            case $gameType . '_3':
//                //中级场
//                $giftNum = $config[3];
//                break;
//            case $gameType . '_4':
//                //高级场
//                $giftNum = $config[4];
//                break;
//            default:
//                $giftNum = $config[1];
//        }

        $data = [];
        if ($gift <= $giftNum && $obj->gift <= $giftNum) {
            $obj->gift = $gift;
            if ($obj->validate() && $obj->save()) {
                $data = $obj->attributes;
            }
        }


        return $data;

    }



}
