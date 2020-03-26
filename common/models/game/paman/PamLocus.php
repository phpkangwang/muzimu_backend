<?php

namespace common\models\game\paman;

use backend\models\Tool;
use common\models\RecordDiamond;
use Yii;

class PamLocus extends Pam
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
        return 'backend_locus_pam_day';
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
            'first_hand_cards'   => '第一手牌',
            'keep_cards'         => '保牌',
            'first_prize_id'     => '第一手得奖类型',
            'second_hand_cards'  => '第二手牌',
            'second_prize_id'    => '第二手得奖类型',
            'plus_cards'         => '加买牌',
            'plus_cards_type'    => '加买牌类型',
            'machine_play_count' => '中奖时机台的局数',
            'prize_out_id'       => '出奖类型',
            'prize_id'           => '中奖id',
            'prize_two_id'       => '中的第二个奖',//用来区别四梅连庄x2 算四梅
            'prize_award_type'   => '喜从天降，红包来袭等活动的类型，机台1，房间1',
            'prize_award_num'    => '活动奖励的数量',
            'compare_id'         => '比备表id',
            'created_time'       => '创建时间(毫秒)'
        ];
    }

    /**
     *  游戏轨迹
     * @param $params
     * @return mixed
     */
    public function LocusPage($params)
    {
        $newPrizeList = $params['newPrizeList'];
        $bigPrizeList = array();
        foreach ($newPrizeList as $val) {
            if ($val['big_award'] == 2) {
                array_push($bigPrizeList, $val['id']);
            }
        }
        $newMachineList = $params['newMachineList'];

        $pageSize = $params['pageSize'];
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $lastId   = $params['lastId'];//上次请求最后一个id，防止分页数据重复

        $inStr = "'" . implode("','", $params['popCodeArr']) . "'";
        $where = "  loc.pop_code in ({$inStr}) ";
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
            $where .= " and loc.prize_id like '%{$params['prizeId']}%'";
        }

        if ($params['jpPrizeId'] != "") {
            $where .= " and loc.prize_jp_id = '{$params['jpPrizeId']}'";
        }

        if ($params['bigAward'] == 2) {
            $inStr = "'" . implode("','", $bigPrizeList) . "'";
            $where .= " and  ( loc.prize_two_id in ({$inStr}) or loc.prize_jp_id in ({$inStr})) ";
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
        $sql       = " select loc.*
                 from  {$tableName}  loc 
                 where {$where}
                 order by loc.create_time desc,loc.id desc limit {$limit}
        ";
        $data      = Yii::$app->game_db->createCommand($sql)->queryAll();

        //获取所有用户的名字
        $accountIds              = array_column($data, "account_id");
        $FivepkPlayerInfoObjs    = $this->FivepkPlayerInfo->finds($accountIds);
        $newFivepkPlayerInfoObjs = array();
        foreach ($FivepkPlayerInfoObjs as $val) {
            $newFivepkPlayerInfoObjs[$val['account_id']] = $val['nick_name'];
        }
        foreach ($data as $key => $val) {
            $data[$key]['create_time']       = date("Y-m-d H:i:s", $val['create_time']);
            $data[$key]['nick_name']         = isset($newFivepkPlayerInfoObjs[$val['account_id']]) ? $newFivepkPlayerInfoObjs[$val['account_id']] : "";
            $data[$key]['prize_name']        = $newPrizeList[$val['prize_id']]['prize_name'];
            $data[$key]['machine_auto_name'] = isset($newMachineList[$val['machine_auto_id']]['seo_machine_id']) ? $newMachineList[$val['machine_auto_id']]['seo_machine_id'] : "";
            $data[$key]['prizeJpName']       = isset($newPrizeList[$val['prize_jp_id']]['prize_name']) ? $newPrizeList[$val['prize_jp_id']]['prize_name'] : "";
            //判断是否掉线
            $data[$key]['lostConnHide'] = $this->lostConn($val['bet_count'], $val['all_open']);
        }
        return $data;
    }

    /**
     * 判断是否掉线
     * @param $betCount
     * @param $allOpen
     * @return array
     */
    private function lostConn($betCount, $allOpen)
    {
        $arr = array();
        if ($allOpen != 1) {
            switch ($betCount) {
                case 1:
                    $arr = [2, 4, 5, 6, 7];
                    break;
                case 2:
                    $arr = [2, 4, 6, 7];
                    break;
                case 3:
                    $arr = [2, 4, 7];
                    break;
            }
        }
        return $arr;
    }

    /**
     * 大奖记录统计
     * @param $params
     * @return mixed
     */
    public function PrizeCount($params)
    {
        $newPrizeList = $params['newPrizeList'];
        $bigPrizeList = array();
        $bigPrizeArr  = array();
        foreach ($newPrizeList as $val) {
            if ($val['big_award'] == 2) {
                array_push($bigPrizeList, $val['id']);
                $bigPrizeArr[$val['id']] = $val['prize_name'];
            }
        }

        $inStr = "'" . implode("','", $params['popCodeArr']) . "'";
        $where = " loc.pop_code in ({$inStr}) ";

        if ($params['accountId'] != "") {
            $where .= " and loc.account_id = '{$params['accountId']}'";
        }

        if ($params['machineId'] != "") {
            $where .= " and loc.machine_auto_id = '{$params['machineId']}'";
        }

        if ($params['prizeId'] != "") {
            $where .= " and loc.prize_id like '%{$params['prizeId']}%'";
        }

        if ($params['bigAward'] == 2) {
            $inStr = "'" . implode("','", $bigPrizeList) . "'";
            $where .= " and  ( loc.prize_two_id in ({$inStr}) or loc.prize_jp_id in ({$inStr})) ";
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
        $sql       = " select loc.id,loc.prize_two_id,loc.prize_jp_id
                 from  {$tableName}  loc 
                 left join data_prize_type prize on prize.id = loc.prize_id
                 where {$where}
        ";
        $data      = Yii::$app->game_db->createCommand($sql)->queryAll();
        $rs        = array();

        foreach ($data as $key => $val) {
            if ( isset($bigPrizeArr[$val['prize_two_id']]) ) {
                $prizeName      = $bigPrizeArr[$val['prize_two_id']];
                $rs[$prizeName] = isset($rs[$prizeName]) ? $rs[$prizeName] : 0;
                $rs[$prizeName]++;
            }
            if ( isset($bigPrizeArr[$val['prize_jp_id']]) ) {
                $prizeName      = $bigPrizeArr[$val['prize_jp_id']];
                $rs[$prizeName] = isset($rs[$prizeName]) ? $rs[$prizeName] : 0;
                $rs[$prizeName]++;
            }
        }
        $newRs = Tool::MySort($rs, $bigPrizeArr);
        return $newRs;
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
            $sql   = "update backend_locus_pam_day set update_time = '{$time}' where id in ($idStr)";
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
