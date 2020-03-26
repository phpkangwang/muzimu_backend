<?php

namespace common\models\game\ghr;

use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\game\FivepkPlayerInfo;
use Yii;

class GhrMachine extends Ghr
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'gold_horse_race_room_config';
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
        return [
            'pond_buff'                => '奖池分数(奖池buff):每局均抽成分数存入奖池;(本局抽成分数=本次押注分 * 奖池抽成比例)',
            'pond_buff_multiple_value' => '大奖奖池',
            'jp_rate'                  => 'jp奖中奖概率:X/10000;然后jp_multiple_rate_range生效生成JP倍数;',

            'profit_extraction_rate_range'    => '盈利抽取押注分比例:抽成分数发往后台存储',
            'pond_buff_extraction_rate_range' => '奖池抽成比例:用于放大奖',
            'pond_buff_accun_rate_range'      => '放奖标准:赔分÷大奖奖池BUFF = 80%~120%（后台可以设定上下限）',
            'pond_buff_multiple_rate_range'   => '大奖出奖比例',
            'jp_multiple_rate_range'          => 'JP奖独立倍率控制',

            'last_surplus_score' => '上一局结余分数:每局结束放奖差值累加到此值中;下局继续放;',

            'auto_id'                   => '自增id',
            'order_id'                  => '顺序',
            'room_info_list_id'         => '房间配置id',
            'room_play_count'           => '房间总玩局房间总玩局数',
            'horse_group_win_record'    => '该房间的冠军组合记录:前入后出',
            'create_time'               => '创建时间',
            'update_time'               => '更新时间',
            'player_win_and_play_ratio' => '[(总赢/总玩分)标准值 判断此局出奖倍率奖的标准]',
            'horse_rate_range'          => '各个倍率的奖出奖概率',
            'bet_score_gap_rate_range'  => '修正正常放奖可用分数增减区间'
        ];
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
        return $status;
    }

    /**
     * 根据auto_id查询多条数据
     * @param $autoIds array
     * @return array
     */
    public function findsByAutoIds($autoIds)
    {
        $data = self::find()->where(['in', 'auto_id', $autoIds])->asArray()->all();
        foreach ($data as $key => $val) {
            $data[$key]['seo_machine_id'] = $this->gameName . $val['auto_id'];
        }
        return $data;
    }

    /**
     * 根据auto_id修改多条数据
     * @param $autoIds  auto_id
     * @param $data     表键值对
     * @return int
     */
    public function updateByAutoIds($autoIds, $data)
    {

        try {
            $arr = [
                'pond_buff'                => $data['pond_buff'],
                'pond_buff_multiple_value' => $data['pond_buff_multiple_value'],
                'jp_rate'                  => $data['jp_rate'],

                'profit_extraction_rate_range'    => Tool::json_encode($data['profit_extraction_rate_range']),
                'pond_buff_extraction_rate_range' => Tool::json_encode($data['pond_buff_extraction_rate_range']),
                'pond_buff_accun_rate_range'      => Tool::json_encode($data['pond_buff_accun_rate_range']),
                'pond_buff_multiple_rate_range'   => Tool::json_encode($data['pond_buff_multiple_rate_range']),
                'jp_multiple_rate_range'          => Tool::json_encode($data['jp_multiple_rate_range']),
                'bet_score_gap_rate_range'        => Tool::json_encode($data['bet_score_gap_rate_range']),
            ];
            if (isset($data['last_surplus_score'])) {
                $arr['last_surplus_score'] = $data['last_surplus_score'];
            }

            if (isset($data['player_win_and_play_ratio'])) {
                $arr['player_win_and_play_ratio'] = $data['player_win_and_play_ratio'];
            }

            if (isset($data['horse_rate_range'])) {
                foreach ($data['horse_rate_range'] as $key => $value) {
                    $value = intval($value);
                    if (empty($value)) {
                        unset($data['horse_rate_range'][$key]);
                    }
                }
                if (empty($data['horse_rate_range'])) {
                    throw new MyException(ErrorCode::ERROR_HORSE_RATE_RANGE_PARAM);
                }
                $arr['horse_rate_range'] = Tool::json_encode($data['horse_rate_range']);
            }

        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

        parent::updateByAutoIds($autoIds, $arr);
        //return self::updateAll($arr, ['in', 'auto_id', $autoIds]);
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
    public function findsByRoomId($roomId)
    {
        return self::find()->where('room_info_list_id = :roomId and status = 1', array(':roomId' => $roomId))->orderBy('order_id desc')->asArray()->all();
    }

    /**
     * 获取房间机台最大的序列号
     * @param $roomId
     * @return array
     */
    public function findRoomMaxOrderId($roomId)
    {
        $obj = self::find()->where('room_info_list_id = :roomId and status = 2', array(':roomId' => $roomId))->orderBy('order_id asc')->asArray()->one();
        if (!empty($obj)) {
            if ($obj['order_id'] == 1) {
                return "";
            }
            $order_id = $obj['order_id'] - 1;
            $obj      = self::find()->where('room_info_list_id = :roomId  and order_id = :order_id', array(':roomId' => $roomId, ':order_id' => $order_id))->asArray()->one();
            return $obj;
        }
        return self::find()->where(['room_info_list_id' => $roomId])->orderBy('order_id desc')->asArray()->one();
    }

    /**
     * 删除机台
     * @param $autoId  主键id
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function del($autoId)
    {
        try {
            $obj = self::findOne($autoId);
            if (empty($obj)) {
                throw new MyException(ErrorCode::ERROR_GAME_MACHINE_NOT_EXIST);
            }
            $RoomMachineObjs = $this->findsByRoomId($obj->room_info_list_id);
            if (count($RoomMachineObjs) == 1) {
                throw new MyException(ErrorCode::ERROR_GAME_MACHINE_HAS_ONE);
            }
            $obj->delete();
            return true;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


}
