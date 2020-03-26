<?php

namespace common\models\game\paman;

use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\game\FivepkPlayerInfo;
use common\models\MachinePath;
use common\models\OddsChangePath;
use Yii;


class PamMachine extends Pam
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_seo_paman';
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
            'auto_id'                  => 'Auto ID',
            'order_id'                 => '排序',
            'seo_machine_id'           => '机台ID',
            'room_info_list_id'        => '房间ID',
            'seo_machine_type'         => 'Seo Machine Type',
            'account_id'               => '用户ID',
            'aaabb_lucky'              => '葫芦幸运分',
            'bonus_rs'                 => '大顺彩金',
            'bonus_fivek'              => '五梅彩金',
            'bonus_sf'                 => '小顺彩金',
            'bonus_fourk'              => '四梅彩金',
            'seo_machine_play_count'   => '机台总玩局数',
            'ty_machine_play_count'    => '体验场总玩局数',
            'machine_auto'             => 'Machine Auto',
            'prefab_compare_buff'      => '比倍buff值',
            'compare_history_cards'    => '比倍记录六张比倍牌',
            'jp_accumulate_count'      => '距离上次JP放奖次数',
            'jp_award_interval_count'  => 'JP奖间隔局数',
            'jp_play_count'            => 'JP奖放奖条件',
            'jp_play_count_rate'       => 'JP奖放奖倍率',
            'jp_open_limit_count'      => 'JP奖最迟放奖延迟次数',
            'jp_pre_win_type'          => '随机选中的JP奖奖型',
            'jp_pre_win_type_rate'     => '随机选中的JP奖奖型',
            'jp_accumulate_add_buff'   => '对应奖型每玩一次累加多少累计值',
            'jp_accumulate_total_buff' => '每种奖型对应JP奖的累计总的BUFF值',
            'create_time'              => '创建时间',
            'update_time'              => '更新时间',
            'reservation_date'         => 'Reservation Date',
        ];
    }

    public function add($data)
    {
        try {
            //修改这个值必须 记录 修改的值
            $arr = Tool::distinctArr($data, $this->attributes, self::attributeLabels());
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
                $this->update_time = time();
                $this->operator    = $GLOBALS['user']['id'];
                if ($this->save()) {
                    return $this->attributes;
                } else {
                    throw new MyException(implode(",", $this->getFirstErrors()));
                }
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
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
     * 关联WinType
     * @return \yii\db\ActiveQuery
     */
    public function getWinType()
    {
        return $this->hasMany(FivepkSeoPamanWintype::className(), ['seo_machine_id' => 'seo_machine_id']);
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
            if ($data['machine_auto'] == 1) {
                $status = '自动';
            } else {
                $status = '在线';
            }
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
     * 根据auto_id修改多条数据
     * @param $autoIds  auto_id
     * @param $data     表键值对
     * @return int
     */
    public function updateByAutoIds($autoIds, $data)
    {
        $models = self::find()->joinWith(['winType'])->filterWhere(['in', 'auto_id', $autoIds])->all();
        foreach ($models as $model) {
            $win_types = $model->winType;
            foreach ($win_types as $win_type) {
                $win_type->add($data['data'][$win_type->prize_name]['FivepkSeoPamanWintype']);
            }
        }
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
            FivepkSeoPamanWintype::deleteAll(['seo_machine_id' => $obj->seo_machine_id]);
            return true;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 彩金复位
     * @return  array
     */
    public function setRecover($ids, $gameName)
    {
        /*
                   体验场        初级场        中级场
        大顺         2000         10000         20000
        五梅         800          4000          8000
        小顺         400          2000          4000
        四梅         10              50            100
         */

        if (!isset(Yii::$app->params['game'][$gameName])) {
            return [];
        }
        $objs = self::findAll(['auto_id' => $ids]);

        $chineseGameName = Yii::$app->params['game'][$gameName];
        $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
        $arr             = [];
        $config          = Yii::$app->params['pamanSetGift'];

        if (Yii::$app->params['platForm'] == 'bmyl') {
            $config[2] = [
                'bonus_rs'    => 20000,
                'bonus_fivek' => 8000,
                'bonus_sf'    => 4000,
                'bonus_fourk' => 100
            ];
        }

        foreach ($objs as $obj) {

            //1在线 2留级
            if ($obj->seo_machine_type == 1 || $obj->seo_machine_type == 2) {
                continue;
            }

            $key              = explode($gameType . '_', $obj->room_info_list_id);
            $obj->bonus_rs    = $config[$key[1]]['bonus_rs'];
            $obj->bonus_fivek = $config[$key[1]]['bonus_fivek'];
            $obj->bonus_sf    = $config[$key[1]]['bonus_sf'];
            $obj->bonus_fourk = $config[$key[1]]['bonus_fourk'];
//            switch ($obj->room_info_list_id) {
//                case $gameType . '_1':
//                    //体验场
//                    $obj->bonus_rs = $config[1]['bonus_rs'];
//                    $obj->bonus_fivek = $config[1]['bonus_fivek'];
//                    $obj->bonus_sf = $config[1]['bonus_sf'];
//                    $obj->bonus_fourk = $config[1]['bonus_fourk'];
//                    break;
//                case $gameType . '_2':
//                    //初级场
//                    $obj->bonus_rs = $config[2]['bonus_rs'];
//                    $obj->bonus_fivek = $config[2]['bonus_fivek'];
//                    $obj->bonus_sf = $config[2]['bonus_sf'];
//                    $obj->bonus_fourk = $config[3]['bonus_fourk'];
//
//                    break;
//                case $gameType . '_3':
//                    //中级场
//                    $obj->bonus_rs = $config[3]['bonus_rs'];
//                    $obj->bonus_fivek = $config[3]['bonus_fivek'];
//                    $obj->bonus_sf = $config[3]['bonus_sf'];
//                    $obj->bonus_fourk = $config[3]['bonus_fourk'];
//                    break;
//                default:
//                    ;
//
//            }
            if ($obj->validate() && $obj->save()) {
                $arr[$obj->auto_id] = $obj->attributes;
            }

        }

        return $arr;

    }

    /**
     * 根据id修改一条数据
     * @return  array
     */
    public function updateOneForId($id, $data)
    {
        $obj = self::find()->where(['auto_id' => $id])->one();
        foreach ($data as $key => $value) {
            $obj->$key = $value;
        }
        $return = [];
        if ($obj->validate() && $obj->save()) {
            $return = $obj->attributes;
        }
        return $return;
    }

    /**
     * 验证jp参数
     * @return  mixed
     */
    public function validateJP(&$data)
    {

        $jp_play_count_rate = json_decode(stripslashes($data['jp_play_count_rate']), true);

        $inKeys    = [1, 2, 3, 4];
        $rateCount = count($jp_play_count_rate);
        if (!is_array($jp_play_count_rate) || $rateCount < 0 || $rateCount > 4) {
            return false;
        }
        foreach ($jp_play_count_rate as $key => &$value) {
            if (in_array($key, $inKeys)) {
                $value = intval($value);
            } else {
                return false;
            }

        }
        $jp_pre_win_type_rate = json_decode(stripslashes($data['jp_pre_win_type_rate']), true);
        $inKeys               = [50, 120, 200, 500];
        $rateCount            = count($jp_pre_win_type_rate);
        if (!is_array($jp_pre_win_type_rate) || $rateCount < 0 || $rateCount > 4) {
            $this->setMessage('jp_pre_win_type_rate错误');
            $this->sendJson();
        }
        foreach ($jp_pre_win_type_rate as $key => &$value) {
            if (in_array($key, $inKeys)) {
                $value = intval($value);
            } else {
                return false;
            }
        }

        return array('jp_pre_win_type_rate' => $jp_pre_win_type_rate, 'jp_play_count_rate' => $jp_play_count_rate);
    }


    /**
     * 根据auto_id修改多条数据
     * @param $autoIds  auto_id
     * @param $data     表键值对
     * @return array
     */
    public function updateListByAutoIds($autoIds, $data)
    {

        $arr  = [];
        $objs = self::findAll(['auto_id' => $autoIds]);
        foreach ($objs as $obj) {
            foreach ($data as $key => $value) {
                $obj->$key = $value;
            }
            if ($obj->validate() && $obj->save()) {
                $arr[$obj->auto_id] = $obj->attributes;
            }
        }

        return $arr;
    }

    /**
     * 通过默认参数修改列表
     * @return  mixed
     */
    public function setJPListFromDefault($roomId)
    {
        $GlobalConfig = new \common\models\GlobalConfig();
        $data         = $GlobalConfig->getJPValue($roomId);

        $data = array(
            'jp_accumulate_count'     => $data['jp_accumulate_count'],
            'jp_play_count'           => $data['jp_play_count'],
            'jp_play_count_rate'      => $data['jp_play_count_rate'],
//            'jp_pre_win_type' => $data['jp_pre_win_type'],
            'jp_pre_win_type_rate'    => $data['jp_pre_win_type_rate'],
            'jp_accumulate_add_buff'  => $data['jp_accumulate_add_buff'],
            'jp_award_interval_count' => $data['jp_award_interval_count'],
            'jp_open_limit_count'     => $data['jp_open_limit_count'],
//            'jp_accumulate_total_buff' => $data['jp_accumulate_buff'],
        );
        return $this->add($data);
    }

    /**
     *   初始化默认机率
     */
    public function initDefault($param)
    {
        set_time_limit('3600');
        $level               = $param['level'];
        $ids                 = $param['ids'];
        $DefaultOddsModel    = $this->getModelDefaultOdds();
        $roomObjs            = DataRoomInfoList::find()->filterWhere(['game' => $this->gameType, 'room_index' => $level])->asArray()->one();
        $MachineList         = $this->getModelMachine()->tableList();
        $MachineWintypeModel = $this->getModelMachineWintype();
        $newMachineList      = array();
        foreach ($MachineList as $val) {
            $newMachineList[$val['auto_id']] = $val;
        }
        //获取默认机率
        $DefaultOddsObj    = $DefaultOddsModel->findByRoom($roomObjs['id']);
        $newDefaultOddsObj = array();
        foreach ($DefaultOddsObj as $dVal) {
            unset($dVal['id']);
            unset($dVal['room_info_list_id']);
            $newDefaultOddsObj[$dVal['prize_name']] = $dVal;
        }
        foreach ($ids as $val) {
            if (!isset($newMachineList[$val])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            //获取这个机台的所有的机率，paman是多条机率
            $MachineWintypeObjs = $MachineWintypeModel::findAll(['room_list_info_id' => $roomObjs['id'], 'seo_machine_id' => $newMachineList[$val]['seo_machine_id']]);
            foreach ($MachineWintypeObjs as $val) {
                if ($val['prize_name'] == $newDefaultOddsObj[$val['prize_name']]['prize_name']) {
                    $this->Tool->myLog("接口" . Yii::$app->requestedRoute . "开始请求:#######  447  #########" . microtime());
                    $val->add($newDefaultOddsObj[$val['prize_name']]);
                    $this->Tool->myLog("接口" . Yii::$app->requestedRoute . "开始请求:#######  449  #########" . microtime());
                }
            }
        }
        return true;
    }
}
