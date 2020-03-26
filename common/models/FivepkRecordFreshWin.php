<?php
namespace common\models;

use backend\models\BaseModel;
use Yii;


class FivepkRecordFreshWin extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_record_fresh_win';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['game_type'], 'required'],
        ];
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * 获取所有
     * @return array|\yii\db\ActiveRecord[]
     */
    public function tableList()
    {
        return self::find()->asArray()->all();
    }

    /**
     * 获取所有
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findList($where)
    {
        return self::find()->where($where)->orderBy('created_at desc')->asArray()->all();
    }

    public function deleteByDay($day,$gameType){
        self::deleteAll(['created_at'=>$day,'game_type'=>$gameType]);
    }

    public function initDay($day){
        $stime = strtotime($day);
        $etime = strtotime($day." 23:59:59");
        $gameNameArr = ['HFH','DZB'];
        $tableName   = [
            'HFH' => "backend_locus_hfh_month",
            'DZB' => "backend_locus_dzb_month",
        ];
        foreach ($gameNameArr as $gameName){
            $chineseGameName   = Yii::$app->params['game'][$gameName];
            $gameType          = Yii::$app->params[$chineseGameName]['gameType'];

            //获取这个游戏下面所有的奖项
            $FivepkPrizeType = new FivepkPrizeType();
            $prizeList = $FivepkPrizeType->getPrizeTypeList($gameType);
            $newPrizeList = array();
            foreach ($prizeList as $val) {
                $newPrizeList[$val['id']] = $val;
            }

            //获取这个游戏的所有场次
            $DataRoomInfoList = new DataRoomInfoList();
            $roomList = $DataRoomInfoList->findByGame($gameType);
            $sql  = "select room_index,prize_two_id from {$tableName[$gameName]} where prize_out_id = 3 and create_time >= '{$stime}' and create_time <= '{$etime}'";
            $data = Yii::$app->game_db->createCommand($sql)->queryAll();

            $rs = array();
            foreach ($roomList as $room)
            {
                //把所有的轨迹放到对应的机台下面
                foreach ($newPrizeList as $prize){
                    $rs[$room['name']] = isset($rs[$room['name']]) ? $rs[$room['name']] : array();
                    foreach ($data as $val){
                        if($room['room_index'] == $val['room_index'] && $prize['id'] == $val['prize_two_id']){
                            $rs[$room['name']][$prize['prize_name']] = isset($rs[$room['name']][$prize['prize_name']]) ? $rs[$room['name']][$prize['prize_name']] : 0;
                            $rs[$room['name']][$prize['prize_name']] ++;
                        }
                    }
                }
            }

            //插入数据库
            $insertData = array(
                'game_type'  => $gameType,
                'content'    => json_encode($rs,JSON_UNESCAPED_UNICODE),
                'created_at' => $day
            );
            $FivepkRecordFreshWin = new FivepkRecordFreshWin();
            $FivepkRecordFreshWin->deleteByDay($day,$gameType);
            $FivepkRecordFreshWin->add($insertData);
        }
        return;
    }
}
