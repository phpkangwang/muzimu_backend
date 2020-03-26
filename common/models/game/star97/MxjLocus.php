<?php

namespace common\models\game\star97;

use common\models\game\FivepkPlayerInfo;
use common\models\RecordDiamond;
use Yii;

class MxjLocus extends Mxj
{

    /**
     * @return \yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_locus_mxj_day';
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
            'id'                 => 'ID',
            'account_id'         => '用户id',
            'pop_code'           => '推广码',
            'machine_auto_id'    => '机台id',
            'coin'               => '钻石',
            'credit'             => '分数',
            'bet'                => '押注分',
            'win'                => '得分',
            'bonus_score'        => '彩金分数',
            'win_diamond'        => '送分',
            'elements'           => '9个元素',
            'line'               => '线形',
            'star_time'          => '明星奖奖励关-倍数',
            'star_count'         => '明星奖奖励关-剩余次数',
            'machine_play_count' => '中奖时机台的局数',
            'prize_out_id'       => '出奖类型',
            'prize_id'           => '中奖id',
            'update_time'        => '修改时间(秒)',
            'create_time'        => '创建时间(秒)'
        ];
    }

    /**
     *  游戏轨迹
     * @param $params
     * @return mixed
     */
    public function LocusPage($params)
    {
        $gameType       = $this->gameType;
        $newMachineList = $params['newMachineList'];

        $pageSize = $params['pageSize'];
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $lastId   = $params['lastId'];//上次请求最后一个id，防止分页数据重复

        $inStr = "'" . implode("','", $params['popCodeArr']) . "'";
        $where = " loc.pop_code in ({$inStr}) ";
        if ($lastId != "") {
            $where .= " and loc.id < '{$params['lastId']}'";
        }

        if ($params['accountId'] != "") {
            $where .= " and loc.account_id = '{$params['accountId']}'";
        }

        if ($params['machineId'] != "") {
            $where .= " and loc.machine_auto_id = '{$params['machineId']}'";
        }

        if ($params['prizeId'] != "") {
            $where .= " and loc.prize_id = '{$params['prizeId']}'";
        }

        if ($params['bigAward'] == 2) {
            $where .= " and prize.game_type = {$gameType} and prize.big_award = 2 ";
        }

        if ($params['prizeItem'] != "") {
            $where .= " and loc.prize_item = '{$params['prizeItem']}'";
        }

        if ($params['prizeOutId'] != "") {
            $where .= " and loc.prize_out_id = '{$params['prizeOutId']}'";
        }

        if ($params['roomIndex'] != "") {
            $where .= " and loc.room_index = '{$params['roomIndex']}'";
        }

        if ($params['stime'] != "" && $params['etime'] != "") {
            $stime = strtotime($params['stime']);
            $etime = strtotime($params['etime']);
            $where .= " and loc.create_time >= '{$stime}' and loc.create_time < '{$etime}'";
        }

        $tableName = $this->unionTable($stime);
        $sql       = " select loc.*,prize.prize_name
                 from  {$tableName}  loc
                 left join data_prize_type prize on prize.id = loc.prize_id
                 where {$where}
                 order by loc.create_time desc,loc.id desc limit {$limit}
        ";
        $data      = Yii::$app->game_db->createCommand($sql)->queryAll();

        //获取所有用户的名字
        $accountIds              = array_column($data, "account_id");
        $FivepkPlayerInfoModel   = new FivepkPlayerInfo();
        $FivepkPlayerInfoObjs    = $FivepkPlayerInfoModel->finds($accountIds);
        $newFivepkPlayerInfoObjs = array();
        foreach ($FivepkPlayerInfoObjs as $val) {
            $newFivepkPlayerInfoObjs[$val['account_id']] = $val['nick_name'];
        }
        foreach ($data as $key => $val) {
            $data[$key]['create_time']       = date("Y-m-d H:i:s", $val['create_time']);
            $data[$key]['prizeItemName']     = isset(Yii::$app->params['mxjPrizeItem'][$val['prize_item']]) ? Yii::$app->params['mxjPrizeItem'][$val['prize_item']] : "";
            $data[$key]['nick_name']         = isset($newFivepkPlayerInfoObjs[$val['account_id']]) ? $newFivepkPlayerInfoObjs[$val['account_id']] : "";
            $data[$key]['machine_auto_name'] = isset($newMachineList[$val['machine_auto_id']]['seo_machine_id']) ? $newMachineList[$val['machine_auto_id']]['seo_machine_id'] : "";
            unset($data[$key]['update_time'],$data[$key]['unique_str'],$data[$key]['prize_append_json'],$data[$key]['line']);
        }
        return $data;
    }


    /**
     * 大奖记录统计
     * @param $params
     * @return mixed
     */
    public function PrizeCount($params)
    {
        $gameType       = $this->gameType;

        $inStr = "'" . implode("','", $params['popCodeArr']) . "'";
        $where = " loc.pop_code in ({$inStr}) ";

        if ($params['accountId'] != "") {
            $where .= " and loc.account_id = '{$params['accountId']}'";
        }

        if ($params['machineId'] != "") {
            $where .= " and loc.machine_auto_id = '{$params['machineId']}'";
        }

        if ($params['prizeId'] != "") {
            $where .= " and ( prize.id = {$params['prizeId']} or prize.parent = {$params['prizeId']} ) ";
        }

        if ($params['bigAward'] == 2) {
            $where .= " and prize.game_type = {$gameType} and prize.big_award = 2 ";
        }

        if ($params['prizeOutId'] != "") {
            $where .= " and loc.prize_out_id = '{$params['prizeOutId']}'";
        }

        if ($params['roomIndex'] != "") {
            $where .= " and loc.room_index = '{$params['roomIndex']}'";
        }


        if ($params['stime'] != "" && $params['etime'] != "") {
            $stime = strtotime($params['stime']);
            $etime = strtotime($params['etime']);
            $where .= " and loc.create_time >= '{$stime}' and loc.create_time < '{$etime}'";
        }

        $tableName = $this->unionTable($stime);
        $sql       = " select prize.prize_name
                 from  {$tableName}  loc 
                 left join data_prize_type prize on prize.id = loc.prize_id
                 where {$where}
        ";
        $data      = Yii::$app->game_db->createCommand($sql)->queryAll();

        $rs = array();
        foreach ($data as $key => $val) {
            $rs[$val['prize_name']] = isset($rs[$val['prize_name']]) ? $rs[$val['prize_name']] : 0;
            $rs[$val['prize_name']]++;
        }
        return $rs;
    }


    /**
     *  查找这段时间所有的中奖纪录 （除乌龙以外的中奖纪录）
     * @param $param  查询条件参数
     * @return mixed
     */
    public function findPrizeLocus($param)
    {
        $stime = $param['stime'];
        $etime = $param['etime'];
        //path 和 locus  因为轨迹用的时间是update_time，上级轨迹用的是create_time
        $type      = $param['type'];
        $accountId = isset($param['accountId']) ? $param['accountId'] : "";
        $tableName = $this->unionTable($stime);
        $where     = " 1";
        if ($accountId != "") {
            $where .= " and account_id = '{$accountId}'";
        }

        if ($type == "path") {
            $where .= " and create_time >= '{$stime}' and create_time < '{$etime}'";
        } else {
            $where .= " and update_time = 0";
        }
        $sql = "select * from  {$tableName} where {$where} ";
        return Yii::$app->game_db->createCommand($sql)->queryAll();
    }


    public function updateLocusUpdateTime($idArr)
    {
        $time = time();
        if (!empty($idArr)) {
            $idStr = "'" . implode("','", $idArr) . "'";
            $sql   = "update {$this->tableLocusDay} set update_time = '{$time}' where id in ($idStr)";
            Yii::$app->game_db->createCommand($sql)->query();
        }
        return true;
    }

    /**
     *  查找这段时间所有的中奖纪录 （除乌龙以外的中奖纪录）
     * @param $stime
     * @param $etime
     * @param $where
     * @return mixed
     */
    public function findPrizeLocusWhere($stime, $etime, $where)
    {
        $tableName = $this->unionTable($stime);
        $sql       = " select *
                 from  {$tableName} 
                 {$where} and create_time >= '{$stime}' and create_time < '{$etime}' 
        ";
        return Yii::$app->game_db->createCommand($sql)->queryAll();
    }

    /*
     *  查一天时间范围内的所有场次的玩家的个数
     */
    public function getRoomLevelPlayerNum($stime, $etime)
    {
        //不要体验场的统计
        $tableName = $this->unionTable($stime);
        $sql       = "
             select account_id,room_index from {$tableName} 
             where create_time between '{$stime}' and '{$etime}'
             and room_index <> 1
             group by room_index,account_id
        ";
        $objs      = Yii::$app->game_db->createCommand($sql)->queryAll();
        $rs        = array();
        foreach ($objs as $val) {
            $rs[$val['room_index']] = isset($rs[$val['room_index']]) ? $rs[$val['room_index']] : array();
            array_push($rs[$val['room_index']], $val['account_id']);
        }
        return $rs;
    }

}
