<?php

namespace common\models\game\snow_leopard;

use common\models\DataRoomInfoList;
use common\models\game\FivepkPlayerInfo;
use Yii;


class BaoDefaultOdds extends Bao
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'snow_leopard_seo_machine_default';
    }

    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [];
    }

    /**
     * 关联房间信息
     * @return \yii\db\ActiveQuery
     */
    public function getRoomList()
    {
        return $this->hasOne(DataRoomInfoList::className(), ['id' => 'room_info_list_id']);
    }

    /**
     * 关联玩家信息
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(), ['account_id' => 'account_id']);
    }

    /**
     * 获得状态
     * @return null|string
     */
    public function findStatus($data)
    {
        $status = null;
        if ($data['seo_machine_type'] == 0) {
            $status = '空闲';
        } elseif ($data['seo_machine_type'] == 1) {
            $status = '在线';
//            if ($data['machine_auto'] == 1) {
//                $status = '自动';
//            } else {
//                $status = '在线';
//            }
        } elseif ($data['seo_machine_type'] == 2) {
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
        $data = self::find()->joinWith(['playerInfo', 'winType'])->where(['in', 'auto_id', $autoIds])->asArray()->all();
        foreach ($data as $key => $val) {
            $data[$key] = $this->Tool->clearFloatZero($val);
            foreach ($val['winType'] as $k => $v) {
                $data[$key]['winType'][$k] = $this->Tool->clearFloatZero($v);
            }
        }
        return $data;
    }

    /**
     *  根据房间id获取机台列表
     * @param $roomId
     * @return array
     */
    public function findsByRoomId($roomId)
    {
        return self::find()->where('room_info_list_id = :roomId and status = 1', array(':roomId' => $roomId))->orderBy('order_id desc')->asArray()->all();
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


}
