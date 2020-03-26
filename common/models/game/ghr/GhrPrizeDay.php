<?php

namespace common\models\game\ghr;

use backend\models\BaseModel;
use backend\models\Tool;
use common\models\FivepkPrizeType;
use common\models\game\base\PrizeBase;
use common\models\game\FivepkAccount;
use common\models\RecordDiamond;
use common\models\RecordHits;
use Yii;

use common\models\game\ghr\GhrLocus as locus;

class GhrPrizeDay extends Ghr
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function getTableName()
    {
        return self::tableName();
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_prize_ghr_day';
    }

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
            'id'              => 'ID',
            'account_id'      => '用户id',
            'machine_auto_id' => '机台id',
            'room_index'      => '房间类型',
            'play_score'      => '总玩分数',
            'win_score'       => '总赢分数',
            'play_number'     => '总玩局数',
            'win_number'      => '总赢局数',
            'room_prize_json' => '所有奖集合',
            'created_time'    => '创建时间(天)'
        ];
    }

    /**
     * 计算一段时间游戏轨迹里面的数据到  prize表
     * @param $param
     */
    public function LocusToPrize($param)
    {
        return true;

        $stime = $param['stime'];
        $etime = $param['etime'];
        $type  = $param['type'];

        $day = date("Y-m-d", $stime);
        //查找这段时间的所有的轨迹
        $locusClass = new locus();
        $locusObjs  = $locusClass->findPrizeLocus($param);
        $locusIdArr = array_column($locusObjs, 'id');

//        $locusClass->updateLocusUpdateTime($locusIdArr);


        $accountPrize = array(); //用来存储用户中奖统计
        $machinePrize = array(); //用来存储机台中奖统计

        $prizeArrIndexByPrizeType = FivepkPrizeType::findByGameTypeIndex($this->gameType, 'prize_type');
        $prizeArrIndexById        = FivepkPrizeType::findByGameTypeIndex($this->gameType, 'id');

        $userKey = 'account_id';

        //获取所有的奖
        foreach ($locusObjs as $locus) {

            {
                //用户奖
                Tool::issetInitValue($accountPrize[$locus[$userKey]]['play_score'], 0);//总玩分数
                Tool::issetInitValue($accountPrize[$locus[$userKey]]['win'], 0);//总赢分数
                Tool::issetInitValue($accountPrize[$locus[$userKey]]['play_number'], 0);//总玩局数
                Tool::issetInitValue($accountPrize[$locus[$userKey]]['win_number'], 0);//总赢局数
//                Tool::issetInitValue($accountPrize[$locus['account_id']]['rate'][$locus['rate']], 0);//倍率次数
//                Tool::issetInitValue($accountPrize[$locus['account_id']]['jp_number'], 0);//jp奖次数

                $accountPrize[$locus[$userKey]]['machine_auto_id'] = 0;//机台编号
                $accountPrize[$locus[$userKey]]['account_id']      = $locus['account_id'];
                $accountPrize[$locus[$userKey]]['room_index']      = $locus['room_index'];
                $accountPrize[$locus[$userKey]]['play_score']      += $locus['bet_sum'];//押注分
                $accountPrize[$locus[$userKey]]['win']             += $locus['win'];//赢分
//                $accountPrize[$locus['account_id']]['rate'][$locus['rate']] += 1;//倍率次数
                $accountPrize[$locus[$userKey]]['play_number'] += 1;//总玩局数

            }


            {
                //房间局数
                $roomKey = $locus['room_index'] . '_' . $locus['machine_play_count'];
                Tool::issetInitValue($machinePrize[$roomKey]['play_score'], 0);//总玩分数
                Tool::issetInitValue($machinePrize[$roomKey]['win'], 0);//总赢分数
                Tool::issetInitValue($machinePrize[$roomKey]['play_number'], 0);//总玩局数
                Tool::issetInitValue($machinePrize[$roomKey]['win_number'], 0);//总赢局数
//                Tool::issetInitValue($machinePrize[$locus['machine_auto_id']]['rate'][$locus['rate']], 0);//倍率次数
//                Tool::issetInitValue($machinePrize[$locus['machine_auto_id']]['jp_number'], 0);//jp奖次数

                $machinePrize[$roomKey]['account_id'] = 0;
                $machinePrize[$roomKey]['room_index'] = $locus['room_index'];

                $machinePrize[$roomKey]['play_score'] += $locus['bet_sum'];//押注分
                $machinePrize[$roomKey]['win']        += $locus['win'];//赢分
//                $machinePrize[$locus['machine_auto_id']]['rate'][$locus['rate']] += 1;//倍率次数
                $machinePrize[$roomKey]['play_number'] += 1;//总玩局数
            }

//            if ($locus['jp'] == 2) {
//                $accountPrize[$locus['account_id']]['jp_number']      += 1;
//                $machinePrize[$locus['machine_auto_id']]['jp_number'] += 1;
//            }


            if ($locus['win'] > 0)//代表玩家这局赢了
            {
                $accountPrize[$locus[$userKey]]['win_number'] += 1;
                $machinePrize[$roomKey]['win_number']         += 1;
            }
        }


        $playSum = count($machinePrize);

        varDump($machinePrize);

        //存入数据库
        $PostData   = array();
        $mergePrize = array_merge($accountPrize, $machinePrize);


        foreach ($mergePrize as $key => $val) {
            $account_id      = $val['account_id'];
            $machine_auto_id = $val['machine_auto_id'];
            $room_index      = $val['room_index'];
            $play_score      = $val['play_score'];//总玩分数
            $win             = $val['win'];//总赢分数
            $play_number     = $val['play_number'];//总玩局数
            $win_number      = $val['win_number'];//总赢局数
            $rate            = $val['rate'];
            $jp_number       = $val['jp_number'];

            unset($val['rate']);
            unset($val['account_id']);
            unset($val['machine_auto_id']);
            unset($val['room_index']);
            unset($val['play_score']);
            unset($val['win']);
            unset($val['play_number']);
            unset($val['win_number']);
            unset($val['jp_number']);

            $prize_json = json_encode($val);
            $PostData[] = array(
                'account_id'      => $account_id,
                'machine_auto_id' => $machine_auto_id,
                'room_index'      => $room_index,
                'play_score'      => $play_score,
                'win_score'       => $win,
                'play_number'     => $play_number,
                'win_number'      => $win_number,
                'room_prize_json' => $prize_json,
                'rate_json'       => json_encode($rate),
                'compare_json'    => '',
                'update_time'     => $stime,
                'create_time'     => $day,
                'jp_number'       => $jp_number,
                'paly_sum'        => $playSum
            );
        }

        varDump($PostData);

        if (!empty($PostData)) {
            $insertKey = " (" . implode(",", array_keys($PostData[0])) . ") ";

            foreach ($PostData as $val) {
                $insertArr[] = " ('" . implode("','", array_values($val)) . "') ";
            }

            $insertStr = implode(" , ", $insertArr);
            $columnStr = $insertKey;
            $sql       = "insert into backend_prize_ghr_day {$columnStr} values {$insertStr}";
            Yii::$app->game_db->createCommand($sql)->query();
        }
    }

    /**
     *  计算各种结果
     * @param $results
     * @return mixed
     */
    public function dataCount($results)
    {
        foreach ($results as $key => $result) {
            if (empty($result)) {
                continue;
            }
            $results[$key]['盈利']   = round((($result['总玩分数']) - ($result['总赢分数'])) / 10, 2);
            $results[$key]['游戏机率'] = $result['总玩分数'] == 0 ? "0%" : round($result['总赢分数'] / $result['总玩分数'] * 100, 2) . "%";
            $results[$key]['中奖率']  = $result['总玩局数'] == 0 ? "0%" : round($result['总赢局数'] / $result['总玩局数'] * 100, 2) . "%";
        }
        return $results;
    }


    /**
     *  计算轨迹所需要的数据  定时运行轨迹到path表
     * @param $accountId
     * @param $stime
     * @param $etime
     * @return array
     */
    public function LocusToPath($accountId, $stime, $etime)
    {
        $day      = date("Y-m-d", $stime);
        $gameType = $this->gameType;
        //查找这段时间的所有的轨迹
        $param     = array(
            'stime'     => $stime,
            'etime'     => $etime,
            'day'       => $day,
            'accountId' => $accountId,
            'type'      => "path",
        );
        $LocusObj  = $this->getModelLocusDay();
        $locusObjs = $LocusObj->findPrizeLocus($param);

        $accountPrize = array(); //用来存储用户中奖统计

        //获取所有的奖
        foreach ($locusObjs as $locus) {
            $accountPrize[$locus['account_id']]['account_id']      = $locus['account_id'];
            $accountPrize[$locus['account_id']]['machine_auto_id'] = 0;
            $this->LocusToPrizeMerge($locus, $accountPrize[$locus['account_id']]);
        }

        //存入数据库
        $PostData = $this->LocusToPrizeDay($accountPrize, $param);

        //获取所有的奖
        $prizeList    = $this->getPrizeTypeList($gameType);
        $newPrizeList = array_column($prizeList, 'prize_name', 'id');

        foreach ($PostData as $key => $val) {
            $this->prizeObjToChinese($PostData[$key], $val, $newPrizeList);
        }

        $PostData = $this->dataCount($PostData);
        Tool::issetInitValue($PostData[0], array());

        return $PostData[0];
    }


    /**
     * locus轨迹的数据转换成 prizeday格式的数据
     * @param $locus
     * @param $result
     */
    public function LocusToPrizeMerge($locus, &$result)
    {
        $result['room_index'] = $locus['room_index'];
        Tool::issetInitValue($result['play_score'], 0);//总玩分数
        Tool::issetInitValue($result['win'], 0);//总赢分数
        Tool::issetInitValue($result['play_number'], 0);//总玩局数
        Tool::issetInitValue($result['win_number'], 0);//总赢局数

        //这里只记录不是红包来袭的奖
        Tool::issetInitValue($result[$locus['prize_id']], 0);
        $result[$locus['prize_id']] += 1;

        $result['play_score']  += $locus['bet_sum'];
        $result['win']         += $locus['win'];
        $result['play_number'] += 1;

        if ($locus['win'] > 0)//代表玩家这局赢了
        {
            $result['win_number'] += 1;
        }

    }

    /**
     * locus表的数据迁移到prizeday表
     * @param $locus
     * @param $param
     * @return array
     */
    public function LocusToPrizeDay($locus, $param)
    {
        $PostData = array();
        foreach ($locus as $key => $val) {
            $account_id      = $val['account_id'];
            $machine_auto_id = $val['machine_auto_id'];
            $room_index      = $val['room_index'];
            $play_score      = $val['play_score'];//总玩分数
            $win_score       = $val['win'];//总赢分数
            $play_number     = $val['play_number'];//总玩局数
            $win_number      = $val['win_number'];//总赢局数


            unset($val['account_id'], $val['machine_auto_id'], $val['room_index'], $val['win']
                , $val['play_score'], $val['win_score'], $val['play_number'], $val['win_number']
                , $val['compareBetScore'], $val['compareWinScore'], $val['comparePlayNumber'], $val['compareWinNumber']
                , $val['compareGuoguanScore'], $val['compareGuoguanNumber'], $val['compareBaojiScore'], $val['compareBaojiNumber']);


            $prize_json = json_encode($val);
            $PostData[] = array(
                'account_id'        => $account_id,
                'machine_auto_id'   => $machine_auto_id,
                'room_index'        => $room_index,
                'play_score'        => $play_score,
                'win_score'         => $win_score,
                'play_number'       => $play_number,
                'win_number'        => $win_number,
                'player_prize_json' => $prize_json,
                'compare_json'      => '',
                'update_time'       => $param['stime'],
                'create_time'       => $param['day'],
            );
        }
        return $PostData;
    }


    /**
     * prizeday表的英文奖转换成中文奖，因为机率计算都是用中文的
     * @param $result
     * @param $obj
     * @param $newPrizeList
     * @return mixed
     */
    public function prizeObjToChinese(&$result, $obj, $newPrizeList)
    {
        //统计每个用户所中的奖的个数
        $prizeJsonArr    = json_decode($obj['player_prize_json'], true);
        $result['prize'] = array();
        foreach ($prizeJsonArr as $prizeJsonId => $prizeJsonObj) {
            //假如没有这个奖就跳过这个奖的统计
            if (!isset($newPrizeList[$prizeJsonId])) {
                continue;
            }
            Tool::issetInitValue($result['prize'][$newPrizeList[$prizeJsonId]], 0);
            $result['prize'][$newPrizeList[$prizeJsonId]] += $prizeJsonObj;
        }

        $result['总玩分数'] = $obj['play_score'];
        $result['总赢分数'] = $obj['win_score'];
        $result['总玩局数'] = $obj['play_number'];
        $result['总赢局数'] = $obj['win_number'];
    }


    /**
     * 机台记录
     * @param $params
     * @return mixed
     */
    public function MachineRecord($params)
    {

        $gameType = $params['gameType'];
        $stime    = $params['stime'];
        $etime    = $params['etime'];
        $sort     = $params['sort'];
        $sortType = $params['sortType'];
        $isTy     = $params['isTy'];
        //获取所有的奖
        $prizeList       = $this->getPrizeTypeList($gameType);
        $newPrizeList    = array();
        $prizeSort       = array();
        foreach ($prizeList as $val) {
            //只有存在这个奖的时候才做统计
            if (isset($val['id'])) {
                $newPrizeList[$val['id']] = $val;
            }
            array_push($prizeSort, $val['prize_name']);
        }

        $where = " account_id=0 ";

        if ($isTy) {
            $where .= " and prize.room_index = 1";
        } else {
            $where .= " and prize.room_index <> 1";
        }


        if (!empty($stime) && !empty($etime)) {
            $where .= " AND prize.create_time >= '{$stime}' AND  prize.create_time <= '{$etime}'";
        }

        $sql = "
                SELECT
                    prize.*
                FROM
                    backend_prize_ghr_day AS prize
                WHERE
                    {$where}
        ";

        $objs = Yii::$app->game_db->createCommand($sql)->queryAll();

        if (empty($objs)) {
            return [];
        }

        $rs = array();


        //原始机台数据计算
        foreach ($objs as $val) {
            $rs[$val['room_index']]['room_index']        = $val['room_index'];
            $rs[$val['room_index']]['play_score']        = isset($rs[$val['room_index']]['play_score']) ? $rs[$val['room_index']]['play_score'] : 0;
            $rs[$val['room_index']]['win_score']         = isset($rs[$val['room_index']]['win_score']) ? $rs[$val['room_index']]['win_score'] : 0;
            $rs[$val['room_index']]['play_number']       = isset($rs[$val['room_index']]['play_number']) ? $rs[$val['room_index']]['play_number'] : 0;
            $rs[$val['room_index']]['win_number']        = isset($rs[$val['room_index']]['win_number']) ? $rs[$val['room_index']]['win_number'] : 0;
            $rs[$val['room_index']]['room_prize_json']   = Tool::examineEmpty($rs[$val['room_index']]['room_prize_json'], []);
            $rs[$val['room_index']]['player_prize_json'] = Tool::examineEmpty($rs[$val['room_index']]['player_prize_json'], []);

            $rs[$val['room_index']]['player_count'] = Tool::examineEmpty($rs[$val['room_index']]['player_count'], 0);
            $rs[$val['room_index']]['play_sum']     = Tool::examineEmpty($rs[$val['room_index']]['play_sum'], 0);

            $rs[$val['room_index']]['play_score']   += $val['play_score'];
            $rs[$val['room_index']]['win_score']    += $val['win_score'];
            $rs[$val['room_index']]['play_number']  += $val['play_number'];
            $rs[$val['room_index']]['win_number']   += $val['win_number'];
            $rs[$val['room_index']]['player_count'] += $val['player_count'];
            $rs[$val['room_index']]['play_sum']     = $val['play_sum'];

            array_push($rs[$val['room_index']]['room_prize_json'], $val['room_prize_json']);
            array_push($rs[$val['room_index']]['player_prize_json'], $val['player_prize_json']);

        }


        $Tool = new Tool();

        //计算每个玩家的各种几率
        $result    = array(); //求每个机台的机率
        $resultSum = array(
            '总玩分数'              => 0,
            '总赢分数'              => 0,
            '总玩局数'              => 0,
            '总赢局数'              => 0,
            'room_prize_json'   => '',
            'player_prize_json' => '',
        );//求所有机台的总机率


        foreach ($rs as $key => $val) {

            $filed1 = 'room_prize_json';
            self::prizeCount($val, $result, $newPrizeList, $resultSum, $filed1);
            $filed2 = 'player_prize_json';
            self::prizeCount($val, $result, $newPrizeList, $resultSum, $filed2);

            $resultSum[$filed1] = $Tool->Mysort($resultSum[$filed1], $prizeSort);
            $resultSum[$filed2] = $Tool->Mysort($resultSum[$filed2], $prizeSort);

            $result[$val['room_index']]['总玩分数'] = $val['play_score'];
            $result[$val['room_index']]['总赢分数'] = $val['win_score'];
            $result[$val['room_index']]['总玩局数'] = $val['play_number'];
            $result[$val['room_index']]['总赢局数'] = $val['win_number'];
            $result[$val['room_index']]['游戏人次'] = $val['player_count'];
            $result[$val['room_index']]['开奖局数'] = $val['play_sum'];

            $resultSum['总玩分数'] += $val['play_score'];
            $resultSum['总赢分数'] += $val['win_score'];
            $resultSum['总玩局数'] += $val['play_number'];
            $resultSum['总赢局数'] += $val['win_number'];
        }
        foreach ($result[2]['room_prize_json'] as $key => $value) {
            $result[2]['prize_json'][$key] = $Tool::examineEmpty($result[2]['player_prize_json'][$key], 0) . '/' . $value;
        }
        $resultSum['prize_json'] = $resultSum['room_prize_json'];
        unset($result[2]['room_prize_json'], $result[2]['player_prize_json'], $resultSum['room_prize_json'], $resultSum['player_prize_json']);

        $result          = $this->dataCount($result);
        $newResultSum[0] = $resultSum;
        $resultSum       = $this->dataCount($newResultSum);

        $sortInArr = ["盈利", "总玩局数"];
        if (in_array($sort, $sortInArr)) {
            $result = $Tool->ArrSort($result, $sort, $sortType);
        }

        $result = array_values($result);


        return array('result' => $result, 'resultSum' => $resultSum[0]);
    }

    public static function prizeCount(&$val, &$result, &$newPrizeList, &$resultSum, $field)
    {
        //统计每个用户所中的奖的个数
        foreach ($val[$field] as $v) {
            $prizeJsonArr = json_decode($v, true);
            foreach ($prizeJsonArr as $prizeJsonId => $prizeJsonObj) {
                //假如没有这个奖就跳过这个奖的统计
                if (!isset($newPrizeList[$prizeJsonId])) {
                    continue;
                }
                Tool::issetInitValue($result[$val['room_index']][$field][$newPrizeList[$prizeJsonId]['prize_name']]);
                Tool::issetInitValue($resultSum[$field][$newPrizeList[$prizeJsonId]['prize_name']]);
                $result[$val['room_index']][$field][$newPrizeList[$prizeJsonId]['prize_name']] += $prizeJsonObj;
                $resultSum[$field][$newPrizeList[$prizeJsonId]['prize_name']]                  += $prizeJsonObj;
            }
        }
    }


    /**
     *   人气报表 不要体验场
     */
    public function reportHits($day)
    {
        $gameType = $this->gameType;
        $stime = strtotime($day);
        $etime = strtotime($day . " 23:59:59");
        //首先查找这个游戏每个场次的机台
        $machineSql  = "select seo.auto_id, room.room_index,room.name
                        from {$this->tableMachine} as seo
                        LEFT JOIN data_room_info_list room  on seo.room_info_list_id = room.id
                        ";
        $machineObjs = Yii::$app->game_db->createCommand($machineSql)->queryAll();

        $sql  = "
              select prize.* from 
              {$this->tablePrizeDay} as prize
              where prize.account_id = 0
              and room_index <> 1
              AND prize.create_time = '{$day}'
        ";
        $objs = Yii::$app->game_db->createCommand($sql)->queryAll();

        $listKey = 'room_index';

        $rs = array();
        foreach ($objs as $val) {
            $rs[$val[$listKey]]['room_index']        = $val['room_index'];
            $rs[$val[$listKey]]['play_score']        = Tool::examineEmpty($rs[$val[$listKey]]['play_score'], 0);
            $rs[$val[$listKey]]['win_score']         = Tool::examineEmpty($rs[$val[$listKey]]['win_score'], 0);
            $rs[$val[$listKey]]['play_number']       = Tool::examineEmpty($rs[$val[$listKey]]['play_number'], 0);
            $rs[$val[$listKey]]['win_number']        = Tool::examineEmpty($rs[$val[$listKey]]['win_number'], 0);
            $rs[$val[$listKey]]['player_prize_json'] = Tool::examineEmpty($rs[$val[$listKey]]['player_prize_json'], []);
            $rs[$val[$listKey]]['play_score']        += $val['play_score'];
            $rs[$val[$listKey]]['win_score']         += $val['win_score'];
            $rs[$val[$listKey]]['play_number']       += $val['play_number'];
            $rs[$val[$listKey]]['win_number']        += $val['win_number'];
            array_push($rs[$val[$listKey]]['player_prize_json'], $val['player_prize_json']);
        }

        //计算每个玩家的各种几率
        $result    = array(); //求每个机台的机率
        $resultSum = array();//求所有机台的总机率
        foreach ($rs as $key => $val) {
            $result[$val[$listKey]][$listKey] = $val[$listKey];

            $result[$val[$listKey]]['总玩分数'] = $val['play_score'];
            $result[$val[$listKey]]['总赢分数'] = $val['win_score'];
            $result[$val[$listKey]]['总玩局数'] = $val['play_number'];
            $result[$val[$listKey]]['总赢局数'] = $val['win_number'];

            $resultSum['总玩分数'] = isset($resultSum['总玩分数']) ? $resultSum['总玩分数'] : 0;
            $resultSum['总赢分数'] = isset($resultSum['总赢分数']) ? $resultSum['总赢分数'] : 0;
            $resultSum['总玩局数'] = isset($resultSum['总玩局数']) ? $resultSum['总玩局数'] : 0;
            $resultSum['总赢局数'] = isset($resultSum['总赢局数']) ? $resultSum['总赢局数'] : 0;
            $resultSum['总玩分数'] += $val['play_score'];
            $resultSum['总赢分数'] += $val['win_score'];
            $resultSum['总玩局数'] += $val['play_number'];
            $resultSum['总赢局数'] += $val['win_number'];
        }

        //把机台按照场次加起来
        $machineLevelObj = array();

        foreach ($result as $val) {
            foreach ($machineObjs as $machineObj) {
                if ($val[$listKey] == $machineObj[$listKey]) {

                    $machineLevelObj[$machineObj['room_index']]['总玩分数'] = isset($machineLevelObj[$machineObj['room_index']]['总玩分数']) ? $machineLevelObj[$machineObj['room_index']]['总玩分数'] : 0;
                    $machineLevelObj[$machineObj['room_index']]['总赢分数'] = isset($machineLevelObj[$machineObj['room_index']]['总赢分数']) ? $machineLevelObj[$machineObj['room_index']]['总赢分数'] : 0;
                    $machineLevelObj[$machineObj['room_index']]['总玩局数'] = isset($machineLevelObj[$machineObj['room_index']]['总玩局数']) ? $machineLevelObj[$machineObj['room_index']]['总玩局数'] : 0;
                    $machineLevelObj[$machineObj['room_index']]['总赢局数'] = isset($machineLevelObj[$machineObj['room_index']]['总赢局数']) ? $machineLevelObj[$machineObj['room_index']]['总赢局数'] : 0;

                    $machineLevelObj[$machineObj['room_index']]['总玩分数'] += $val['总玩分数'];
                    $machineLevelObj[$machineObj['room_index']]['总赢分数'] += $val['总赢分数'];
                    $machineLevelObj[$machineObj['room_index']]['总玩局数'] += $val['总玩局数'];
                    $machineLevelObj[$machineObj['room_index']]['总赢局数'] += $val['总赢局数'];
                }
            }
        }


        $machineLevelObj = $this->dataCount($machineLevelObj);
        $newResultSum[0] = $resultSum;
        $resultSum       = $this->dataCount($newResultSum);

        //获取每个场次的游戏人数 只有从轨迹里面取
        $locusModelObj    = $this->getModelLocusDay();
        $peopleList       = $locusModelObj->getRoomLevelPlayerNum($stime, $etime );
        $peopleList['合计'] = array();
        foreach ($peopleList as $val) {
            foreach ($val as $v) {
                array_push($peopleList['合计'], $v);
            }
        }

        $people_game_json['合计'] = count(array_unique($peopleList['合计']));
        $profit_json['合计']      = isset($resultSum[0]['盈利']) ? $resultSum[0]['盈利'] : 0;
        $play_num_json['合计']    = isset($resultSum[0]['总玩局数']) ? $resultSum[0]['总玩局数'] : 0;
        $odds_json['合计']        = isset($resultSum[0]['游戏机率']) ? $resultSum[0]['游戏机率'] : 0;
        $award_json['合计']       = isset($resultSum[0]['中奖率']) ? $resultSum[0]['中奖率'] : 0;

        foreach ($machineLevelObj as $key => $val) {
            $people_game_json[$key] = isset($peopleList[$key]) ? count($peopleList[$key]) : 0;
            $profit_json[$key]      = $val['盈利'];
            $play_num_json[$key]    = $val['总玩局数'];
            $odds_json[$key]        = $val['游戏机率'];
            $award_json[$key]       = $val['中奖率'];
        }

        //今天的注册人数
        $FivepkAccount = new FivepkAccount();
        $people        = $FivepkAccount->findRegistNum($day);
        //因为有新老玩家所有雪豹也要遵守这个规定
        $oddsTypeArr = Yii::$app->params['noOddsTypeList'];
        $RecordHits  = new RecordHits();
        $RecordHits->deleteByDay($day, $this->gameType);
        foreach ($oddsTypeArr as $oddsType) {
            //存入数据库
            $postData   = array(
                'game_type'        => $this->gameType,
                'odds_type'        => $oddsType,
                'people'           => $people,
                'profit_json'      => json_encode($profit_json, JSON_UNESCAPED_UNICODE),
                'people_game_json' => json_encode($people_game_json, JSON_UNESCAPED_UNICODE),
                'play_num_json'    => json_encode($play_num_json, JSON_UNESCAPED_UNICODE),
                'odds_json'        => json_encode($odds_json, JSON_UNESCAPED_UNICODE),
                'award_json'       => json_encode($award_json, JSON_UNESCAPED_UNICODE),
                'create_time'      => $day,
            );
            $RecordHits = new RecordHits();
            $RecordHits->add($postData);
        }

        return true;
    }

    //合并Prize数据重新插入
    public function mergePrizeDay($create_time)
    {
        $tableName  = self::tableName();
        $PrizeModel = new self();

        $sql       = "
             select * from {$tableName} where  create_time = '{$create_time}'
        ";
        $prizeObjs = $PrizeModel::getDb()->createCommand($sql)->queryAll();
        $MergeData = &$this->getMergeData($prizeObjs);

        $newPrizeMachineObjs = $MergeData['newPrizeMachineObjs'];
        $newPrizeAccountObjs = $MergeData['newPrizeAccountObjs'];

        //开启事务，保证插入和删除都成功
        $tr = $PrizeModel::getDb()->beginTransaction();
        foreach ($newPrizeMachineObjs as $val) {
            foreach ($val as $v) {
                $insertKey   = " (" . implode(",", array_keys($v)) . ") ";
                $insertArr[] = " ('" . implode("','", $v) . "') ";
            }
        }
        if (!empty($newPrizeAccountObjs)) {
            foreach ($newPrizeAccountObjs as $val) {
                foreach ($val as $v) {
                    $insertKey   = " (" . implode(",", array_keys($v)) . ") ";
                    $insertArr[] = " ('" . implode("','", $v) . "') ";
                }
            }

            $insertStr = implode(" , ", $insertArr);
            $columnStr = $insertKey;
            $deleteSql = "delete from {$tableName} where create_time = '{$create_time}'";
            $insertSql = "insert into {$tableName} {$columnStr} values {$insertStr}";
            Yii::$app->game_db->createCommand($deleteSql)->query();
            Yii::$app->game_db->createCommand($insertSql)->query();
        }
        $tr->commit();
        return true;
    }

    //获得合并后的数据
    public function &getMergeData(&$prizeObjs)
    {
        $newPrizeAccountObjs = array();
        $newPrizeMachineObjs = array();
        foreach ($prizeObjs as $prizeObj) {
            $accountId   = $prizeObj['account_id'];
            $roomIndexId = $prizeObj['room_index'];
            if ($prizeObj['account_id'] == 0) {
                $machineId = 1;
                $this->mergeDataHandler($machineId, $roomIndexId, $prizeObj, $newPrizeMachineObjs);
            } else {
                $this->mergeDataHandler($accountId, $roomIndexId, $prizeObj, $newPrizeAccountObjs);
            }
        }

        $data = ['newPrizeAccountObjs' => $newPrizeAccountObjs, 'newPrizeMachineObjs' => $newPrizeMachineObjs];
        return $data;
    }

    //合并数据处理
    public function mergeDataHandler(&$key, &$roomIndexId, &$prizeObj, &$data)
    {

        $data[$key][$roomIndexId]['account_id']   = $prizeObj['account_id'];
        $data[$key][$roomIndexId]['room_index']   = $prizeObj['room_index'];
        $data[$key][$roomIndexId]['play_sum']     = $prizeObj['play_sum'];
        $data[$key][$roomIndexId]['create_time']  = $prizeObj['create_time'];
        $data[$key][$roomIndexId]['win_score']    = Tool::examineEmpty($data[$key][$roomIndexId]['win_score'], 0);
        $data[$key][$roomIndexId]['play_score']   = Tool::examineEmpty($data[$key][$roomIndexId]['play_score'], 0);
        $data[$key][$roomIndexId]['play_number']  = Tool::examineEmpty($data[$key][$roomIndexId]['play_number'], 0);
        $data[$key][$roomIndexId]['win_number']   = Tool::examineEmpty($data[$key][$roomIndexId]['win_number'], 0);
        $data[$key][$roomIndexId]['player_count'] = Tool::examineEmpty($data[$key][$roomIndexId]['player_count'], 0);
        $data[$key][$roomIndexId]['profit_score'] = Tool::examineEmpty($data[$key][$roomIndexId]['profit_score'], 0);

        $data[$key][$roomIndexId]['player_prize_json'] = Tool::examineEmpty($data[$key][$roomIndexId]['player_prize_json']);
        $data[$key][$roomIndexId]['room_prize_json']   = Tool::examineEmpty($data[$key][$roomIndexId]['room_prize_json']);

        $data[$key][$roomIndexId]['win_score']    += $prizeObj['win_score'];
        $data[$key][$roomIndexId]['play_score']   += $prizeObj['play_score'];
        $data[$key][$roomIndexId]['play_number']  += $prizeObj['play_number'];
        $data[$key][$roomIndexId]['win_number']   += $prizeObj['win_number'];
        $data[$key][$roomIndexId]['player_count'] += $prizeObj['player_count'];
        $data[$key][$roomIndexId]['profit_score'] += $prizeObj['profit_score'];

        $data[$key][$roomIndexId]['player_prize_json'] = $this->mergePrizeJson($data[$key][$roomIndexId]['player_prize_json'], $prizeObj['player_prize_json']);

        $data[$key][$roomIndexId]['room_prize_json'] = $this->mergePrizeJson($data[$key][$roomIndexId]['room_prize_json'], $prizeObj['room_prize_json']);

    }


    /**
     * 获取玩家所有的数据  老玩家列表
     * @param $accountId
     * @return string
     */
    public function PlayerRecordNever($accountId)
    {
        //获取所有的奖
        $prizeList       = $this->getPrizeTypeList($this->gameType);
        $newPrizeList    = array_column($prizeList, 'prize_name', 'id');

        $prizeDayObjs = self::find()->where('account_id = :account_id and machine_auto_id = 0 and room_index <> 1', array(':account_id' => $accountId))->asArray()->all();
        $resultSum    = array();

        //先把一个玩家当天的数据都合并成一条数据
        foreach ($prizeDayObjs as $val) {
            $this->prizeObjToMerge($resultSum[0], $val);//因为只支持二维数组所以加一个下标
        }

        //计算每个玩家的各种几率
        foreach ($resultSum as $key => $val) {
            $this->prizeObjToChinese($resultSum[$key], $val, $newPrizeList);
        }

        $resultSum = $this->dataCount($resultSum);
        Tool::issetInitValue($resultSum[0], array());
        return $resultSum[0];
    }

    /**
     *  把多条 prize_day表的数据按照要求合成到数组里面
     * @param $newArr
     * @param $obj
     */
    public function prizeObjToMerge(&$newArr, $obj)
    {
        Tool::issetInitValue($newArr['play_score'], 0);
        Tool::issetInitValue($newArr['win_score'], 0);
        Tool::issetInitValue($newArr['play_number'], 0);
        Tool::issetInitValue($newArr['win_number'], 0);
        Tool::issetInitValue($newArr['jp_number'], 0);
        Tool::issetInitValue($newArr['player_prize_json'], array());
//        Tool::issetInitValue($newArr['compare_json'], array());
        $newArr['play_score']  += $obj['play_score'];
        $newArr['win_score']   += $obj['win_score'];
        $newArr['play_number'] += $obj['play_number'];
        $newArr['win_number']  += $obj['win_number'];
//        $newArr['jp_number']       += $obj['jp_number'];
        $newArr['player_prize_json'] = $this->mergePrizeJson($newArr['player_prize_json'], $obj['player_prize_json']);
    }

}
