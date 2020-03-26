<?php

namespace common\models\game\firephoenix;

use backend\models\Tool;
use common\models\game\FivepkAccount;
use common\models\RecordDiamond;
use common\models\RecordHits;
use Yii;

class HfhPrizeDay extends Hfh
{
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_prize_hfh_day';
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
            'prize_json'      => '所有奖集合',
            'compare_json'    => '所有比备集合',
            'created_time'    => '创建时间(天)'
        ];
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
            $results[$key]['比倍后机率']  = $results[$key]['比倍总玩分数'] == 0 ? "0%" : round(($results[$key]['总赢分数'] + $results[$key]['比倍总赢分数'] + $results[$key]['爆机分数'] + $results[$key]['过关分数']) / ($results[$key]['总玩分数'] + $results[$key]['比倍总玩分数']) * 100, 2) . "%";
            $results[$key]['盈利']     = round((($result['总玩分数'] + $result['比倍总玩分数']) - ($result['比倍总赢分数'] + $result['总赢分数'] + $result['过关分数'] + $result['爆机分数'])) / 100, 2);
            $results[$key]['游戏机率']   = $result['总玩分数'] == 0 ? "0%" : round($result['总赢分数'] / $result['总玩分数'] * 100, 2) . "%";
            $results[$key]['中奖率']    = $result['总玩局数'] == 0 ? "0%" : round($result['总赢局数'] / $result['总玩局数'] * 100, 2) . "%";
            $results[$key]['比倍游戏机率'] = $result['比倍总玩分数'] == 0 ? "0%" : round(($result['比倍总赢分数'] + $result['爆机分数'] + $result['过关分数']) / $result['比倍总玩分数'] * 100, 2) . "%";
            $results[$key]['比倍中奖率']  = $result['比倍总玩局数'] == 0 ? "0%" : round($result['比倍总赢局数'] / $result['比倍总玩局数'] * 100, 2) . "%";
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
        $param         = array(
            'stime'     => $stime,
            'etime'     => $etime,
            'day'       => $day,
            'accountId' => $accountId,
            'type'      => "path",
        );
        $LocusObj      = $this->getModelLocusDay();
        $locusObjs     = $LocusObj->findPrizeLocus($param);
        $locusIdArr    = array_column($locusObjs, 'id');
        $CompareObj    = $this->getModelCompare();
        $compareObjs   = $CompareObj->findByLocusIds($locusIdArr);

        $accountPrize = array(); //用来存储用户中奖统计
        //获取所有的奖
        foreach ($locusObjs as $locus) {
            $accountPrize[$locus['account_id']]['account_id']      = $locus['account_id'];
            $accountPrize[$locus['account_id']]['machine_auto_id'] = 0;
            $this->LocusToPrizeMerge($locus, $compareObjs, $accountPrize[$locus['account_id']]);
        }

        //存入数据库
        $PostData = $this->LocusToPrizeDay([$accountPrize], $param);

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
     * 把$time之间游戏轨迹 没有统计到 prizeday表 里面的数据到  prize表   定时运行 轨迹到prizeday表
     * @param $time
     * @param $gameType
     */
    public function LocusToPrize($param)
    {
        $time  = $param['stime'];
        $day   = date("Y-m-d", $time);
        $param = array(
            'stime' => $time,
            'etime' => $param['etime'],
            'day'   => $day,
            'type'  => $param['type'],
        );
        //查找这段时间的所有的轨迹
        $LocusObj      = $this->getModelLocusDay();
        $locusObjs     = $LocusObj->findPrizeLocus($param);
        $locusIdArr    = array_column($locusObjs, 'id');
        $CompareObj    = $this->getModelCompare();
        $compareObjs   = $CompareObj->findByLocusIds($locusIdArr);

        $LocusObj->updateLocusUpdateTime($locusIdArr);

        $accountPrize = array(); //用来存储用户中奖统计
        $machinePrize = array(); //用来存储机台中奖统计
        //获取所有的奖
        foreach ($locusObjs as $locus) {
            $accountPrize[$locus['account_id']][$locus['odds_type']]['account_id']      = $locus['account_id'];
            $accountPrize[$locus['account_id']][$locus['odds_type']]['machine_auto_id'] = 0;
            $this->LocusToPrizeMerge($locus, $compareObjs, $accountPrize[$locus['account_id']][$locus['odds_type']]);

            $machinePrize[$locus['machine_auto_id']][$locus['odds_type']]['account_id']      = 0;
            $machinePrize[$locus['machine_auto_id']][$locus['odds_type']]['machine_auto_id'] = $locus['machine_auto_id'];
            $this->LocusToPrizeMerge($locus, $compareObjs, $machinePrize[$locus['machine_auto_id']][$locus['odds_type']]);
        }
        //存入数据库
        $mergePrize = array_merge($accountPrize, $machinePrize);
        $PostData   = $this->LocusToPrizeDay($mergePrize, $param);

        if (!empty($PostData)) {
            $insertKey = " (" . implode(",", array_keys($PostData[0])) . ") ";

            foreach ($PostData as $val) {
                $insertArr[] = " ('" . implode("','", array_values($val)) . "') ";
            }
            $insertStr = implode(" , ", $insertArr);
            $columnStr = $insertKey;
            $sql       = "insert into {$this->tablePrizeDay} {$columnStr} values {$insertStr}";
            Yii::$app->game_db->createCommand($sql)->query();
        }
    }

    /**
     * 机台记录
     * @param $params
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function MachineRecord($params)
    {
        $gameType      = $this->gameType;
        $machineAutoId = $params['machineAutoId'];
        $oddsType      = $params['oddsType'];
        $stime         = $params['stime'];
        $etime         = $params['etime'];
        $sort          = $params['sort'];
        $sortType      = $params['sortType'];
        $isTy          = $params['isTy'];
        //获取所有的奖
        $prizeList       = $this->getPrizeTypeList($gameType);
        $newPrizeList    = array_column($prizeList, 'prize_name', 'id');

        $where = " prize.machine_auto_id <> 0 ";

        if ($isTy) {
            $where .= " and prize.room_index = 1";
        } else {
            $where .= " and prize.room_index <> 1";
        }
        if ($machineAutoId != "") {
            $where .= " and seo.seo_machine_id like '%{$machineAutoId}%'";
        }

        if ($oddsType != "") {
            $where .= " and prize.odds_type= '{$oddsType}'";
        }

        if (!empty($stime) && !empty($etime)) {
            $where .= " AND prize.create_time >= '{$stime}' AND  prize.create_time <= '{$etime}'";
        }

        $sql          = "
              select prize.*,seo.seo_machine_id from 
              {$this->tablePrizeDay} as prize
              inner join {$this->tableMachine} seo on seo.auto_id=prize.machine_auto_id
              where {$where}
              order by  room_index asc,machine_auto_id asc
        ";
        $prizeDayObjs = Yii::$app->game_db->createCommand($sql)->queryAll();
        $result       = array();
        $resultSum    = array();

        //先把一个玩家当天的数据都合并成一条数据
        foreach ($prizeDayObjs as $val) {
            $result[$val['machine_auto_id']]['machine_auto_id'] = $val['machine_auto_id'];
            $result[$val['machine_auto_id']]['seo_machine_id']  = $val['seo_machine_id'];
            $this->prizeObjToMerge($result[$val['machine_auto_id']], $val);
            $this->prizeObjToMerge($resultSum[0], $val);//因为只支持二维数组所以加一个下标
        }

        //计算每个玩家的各种几率
        foreach ($result as $key => $val) {
            $this->prizeObjToChinese($result[$val['machine_auto_id']], $val, $newPrizeList);
        }


        //计算每个玩家的各种几率
        foreach ($resultSum as $key => $val) {
            $this->prizeObjToChinese($resultSum[$key], $val, $newPrizeList);
        }

        $result    = $this->dataCount($result);
        $resultSum = $this->dataCount($resultSum);

        $Tool = new  Tool();

        $sortInArr = ["盈利", "总玩局数"];
        if (in_array($sort, $sortInArr)) {
            $result = $Tool->ArrSort($result, $sort, $sortType);
        }
        $result = array_values($result);
        Tool::issetInitValue($resultSum[0], array());

        if (isset($resultSum[0]['prize']) && !empty($resultSum[0]['prize'])) {
            $sorArr                = array_column($prizeList, 'prize_name', 'prize_name');
            $resultSum[0]['prize'] = $Tool::MySort($resultSum[0]['prize'], $sorArr);
        }

        return array('result' => $result, 'resultSum' => $resultSum[0]);
    }

    /**
     *   人气报表 不要体验场
     */
    public function reportHits($day)
    {
        $gameType = $this->gameType;
        $stime    = strtotime($day);
        $etime    = strtotime($day . " 23:59:59");
        //获取所有的奖
        $prizeList    = $this->getPrizeTypeList($gameType);
        $newPrizeList = array_column($prizeList, 'prize_name', 'id');

        //今天的注册人数
        $FivepkAccount = new FivepkAccount();
        $people        = $FivepkAccount->findRegistNum($day);

        //删除今天的报表
        $recordHitsObj = $this->getModelHitsReport();
        $recordHitsObj->deleteByDay($day, $gameType);

        //求每一种机率的人气报表
        $oddsTypeArr = Yii::$app->params['oddsTypeList'];
        foreach ($oddsTypeArr as $oddsType) {
            $sql = "
              select prize.* from 
              {$this->tablePrizeDay} as prize
              where prize.machine_auto_id <> 0 
              and room_index <> 1
              AND prize.create_time = '{$day}'
            ";
            if ($oddsType != "") {
                $sql .= " and odds_type = '{$oddsType}'";
            }

            $prizeDayObjs = Yii::$app->game_db->createCommand($sql)->queryAll();
            $result       = array();
            $resultSum    = array();

            //先把一个玩家当天的数据都合并成一条数据
            foreach ($prizeDayObjs as $val) {
                $result[$val['room_index']]['room_index'] = $val['room_index'];
                $this->prizeObjToMerge($result[$val['room_index']], $val);
                $this->prizeObjToMerge($resultSum[0], $val);//因为只支持二维数组所以加一个下标
            }

            //计算每个玩家的各种几率
            foreach ($result as $key => $val) {
                $this->prizeObjToChinese($result[$val['room_index']], $val, $newPrizeList);
            }

            //计算每个玩家的各种几率
            foreach ($resultSum as $key => $val) {
                $this->prizeObjToChinese($resultSum[$key], $val, $newPrizeList);
            }

            $result    = $this->dataCount($result);
            $resultSum = $this->dataCount($resultSum);

            //获取每个场次的游戏人数 只有从轨迹里面取
            $locusModelObj    = $this->getModelLocusDay();
            $peopleList       = $locusModelObj->getRoomLevelPlayerNum($stime, $etime, $oddsType);
            $peopleList['合计'] = array();
            foreach ($peopleList as $val) {
                foreach ($val as $v) {
                    array_push($peopleList['合计'], $v);
                }
            }

            $people_game_json       = $profit_json = $play_num_json = $odds_json = $award_json = array();
            $people_game_json['合计'] = count(array_unique($peopleList['合计']));
            $profit_json['合计']      = isset($resultSum[0]['盈利']) ? $resultSum[0]['盈利'] : 0;
            $play_num_json['合计']    = isset($resultSum[0]['总玩局数']) ? $resultSum[0]['总玩局数'] : 0;
            $odds_json['合计']        = isset($resultSum[0]['游戏机率']) ? $resultSum[0]['游戏机率'] : 0;
            $award_json['合计']       = isset($resultSum[0]['中奖率']) ? $resultSum[0]['中奖率'] : 0;
            foreach ($result as $key => $val) {
                $people_game_json[$key] = isset($peopleList[$key]) ? count($peopleList[$key]) : 0;
                $profit_json[$key]      = $val['盈利'];
                $play_num_json[$key]    = $val['总玩局数'];
                $odds_json[$key]        = $val['游戏机率'];
                $award_json[$key]       = $val['中奖率'];
            }

            //存入数据库
            $postData        = array(
                'game_type'        => $gameType,
                'odds_type'        => $oddsType,
                'people'           => $people,
                'profit_json'      => json_encode($profit_json, JSON_UNESCAPED_UNICODE),
                'people_game_json' => json_encode($people_game_json, JSON_UNESCAPED_UNICODE),
                'play_num_json'    => json_encode($play_num_json, JSON_UNESCAPED_UNICODE),
                'odds_json'        => json_encode($odds_json, JSON_UNESCAPED_UNICODE),
                'award_json'       => json_encode($award_json, JSON_UNESCAPED_UNICODE),
                'create_time'      => $day,
            );
            $RecordHitsModel = new RecordHits();
            $RecordHitsModel->add($postData);
        }
        return true;
    }

    /**
     * 获取玩家所有的数据  老玩家列表
     * @param $accountId
     * @return string
     */
    public function PlayerRecordNever($accountId)
    {
        //获取所有的奖
        $gameType     = $this->gameType;
        $prizeList    = $this->getPrizeTypeList($gameType);
        $newPrizeList = array_column($prizeList, 'prize_name', 'id');

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
     * locus轨迹的数据转换成 prizeday格式的数据
     * @param $locus
     * @param $compareObjs
     * @param $result
     */
    public function LocusToPrizeMerge($locus, $compareObjs, &$result)
    {
        $result['room_index'] = $locus['room_index'];
        $result['odds_type']  = $locus['odds_type'];
        Tool::issetInitValue($result['play_score'], 0);//总玩分数
        Tool::issetInitValue($result['win_score'], 0);//总赢分数
        Tool::issetInitValue($result['play_number'], 0);//总玩局数
        Tool::issetInitValue($result['win_number'], 0);//总赢局数
        Tool::issetInitValue($result['compareBetScore'], 0);//比倍总玩分数
        Tool::issetInitValue($result['compareWinScore'], 0);//比倍总赢分数
        Tool::issetInitValue($result['comparePlayNumber'], 0);//比倍总玩局数
        Tool::issetInitValue($result['compareWinNumber'], 0);//比倍总赢局数
        Tool::issetInitValue($result['compareGuoguanScore'], 0);///比倍过关分数
        Tool::issetInitValue($result['compareBaojiScore'], 0);//比倍爆机分数
        Tool::issetInitValue($result['compareGuoguanNumber'], 0);//比倍过关局数
        Tool::issetInitValue($result['compareBaojiNumber'], 0);//比倍爆机局数

        //红包来袭统计
        for ($i = 1; $i <= 5; $i++) {
            $diamondTimes = Yii::$app->params['diamondTimes'][$i];
            $diamondCount = Yii::$app->params['diamondCount'][$i];
            Tool::issetInitValue($result[$diamondTimes], 0);//红包来袭统计 各个奖的次数统计
            Tool::issetInitValue($result[$diamondCount], 0);//红包来袭统计 各个奖的送钻总和统计
        }
        //代表中了红包来袭
        if ($locus['prize_award_type'] != "" && $locus['prize_award_num']) {
            $addDiamondTimes          = Yii::$app->params['diamondTimes'][$locus['prize_award_type']];
            $addDiamondCount          = Yii::$app->params['diamondCount'][$locus['prize_award_type']];
            $result[$addDiamondTimes] += 1;
            $result[$addDiamondCount] += $locus['prize_award_num'];
        } else {
            //这里只记录不是红包来袭的奖
            Tool::issetInitValue($result[$locus['prize_id']], 0);
            $result[$locus['prize_id']] += 1;
        }

        $result['play_score'] += $locus['bet'];
        $result['win_score']  += $locus['win_score'];

        //红包来袭计算总玩局数，四梅连庄结束多发的一条数据不算总玩局数
        if ($locus['bet'] > 0 || $locus['win'] > 0) {
            //四梅连庄会多发一条押注分是0的轨迹，这里必须不计算
            $result['play_number'] += 1;
        }


        if ($locus['win_score'] > 0)//代表玩家这局赢了
        {
            $result['win_number'] += 1;
        }

        //统计比备数据
        foreach ($compareObjs as $compare) {
            if ($compare['locus_id'] == $locus['id']) {
                $result['compareBetScore']      += $compare['play_score'];
                $result['compareWinScore']      += $compare['win_score'];
                $result['comparePlayNumber']    += $compare['play_number'];
                $result['compareWinNumber']     += $compare['win_number'];
                $result['compareGuoguanScore']  += $compare['guoguan_score'];
                $result['compareGuoguanNumber'] += $compare['guoguan_count'];
                $result['compareBaojiScore']    += $compare['baoji_score'];
                $result['compareBaojiNumber']   += $compare['baoji_count'];
            }
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
        foreach ($locus as $k => $v) {
            foreach ($v as $key => $val) {
                $account_id      = $val['account_id'];
                $machine_auto_id = $val['machine_auto_id'];
                $room_index      = $val['room_index'];
                $odds_type       = $val['odds_type'];
                $play_score      = $val['play_score'];//总玩分数
                $win_score       = $val['win_score'];//总赢分数
                $play_number     = $val['play_number'];//总玩局数
                $win_number      = $val['win_number'];//总赢局数

                $compareJson['compareBetScore']      = $val['compareBetScore'];//比倍总玩分数
                $compareJson['compareWinScore']      = $val['compareWinScore'];//比倍总赢分数
                $compareJson['comparePlayNumber']    = $val['comparePlayNumber'];//比倍总玩局数
                $compareJson['compareWinNumber']     = $val['compareWinNumber'];//比倍总赢局数
                $compareJson['compareGuoguanScore']  = $val['compareGuoguanScore'];//过关分数
                $compareJson['compareGuoguanNumber'] = $val['compareGuoguanNumber'];//过关局数
                $compareJson['compareBaojiScore']    = $val['compareBaojiScore'];//爆机分数
                $compareJson['compareBaojiNumber']   = $val['compareBaojiNumber'];//爆机局数
                for ($i = 1; $i <= 5; $i++) {
                    $diamondTimes               = Yii::$app->params['diamondTimes'][$i];
                    $diamondCount               = Yii::$app->params['diamondCount'][$i];
                    $compareJson[$diamondTimes] = $val[$diamondTimes];
                    $compareJson[$diamondCount] = $val[$diamondCount];
                    unset($val[$diamondTimes]);
                    unset($val[$diamondCount]);
                }
                $compare_json = json_encode($compareJson);

                unset($val['account_id'], $val['machine_auto_id'], $val['room_index'], $val['odds_type']
                    , $val['play_score'], $val['win_score'], $val['play_number'], $val['win_number']
                    , $val['compareBetScore'], $val['compareWinScore'], $val['comparePlayNumber'], $val['compareWinNumber']
                    , $val['compareGuoguanScore'], $val['compareGuoguanNumber'], $val['compareBaojiScore'], $val['compareBaojiNumber']);

                $prize_json = json_encode($val);
                $PostData[] = array(
                    'account_id'      => $account_id,
                    'machine_auto_id' => $machine_auto_id,
                    'room_index'      => $room_index,
                    'odds_type'       => $odds_type,
                    'play_score'      => $play_score,
                    'win_score'       => $win_score,
                    'play_number'     => $play_number,
                    'win_number'      => $win_number,
                    'prize_json'      => $prize_json,
                    'compare_json'    => $compare_json,
                    'update_time'     => $param['stime'],
                    'create_time'     => $param['day'],
                );
            }
        }
        return $PostData;
    }


    /**
     *  多条数据，和并成一条数据，相同的奖和分数相加求和
     * @param $day
     * @return bool
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function mergePrizeDay($day)
    {
        $tableName  = self::tableName();
        $PrizeModel = new self();

        $sql          = "select * from {$tableName} where  create_time = '{$day}'";
        $prizeDayObjs = $PrizeModel::getDb()->createCommand($sql)->queryAll();
        $result       = array();

        //先把一个玩家当天的数据都合并成一条数据
        foreach ($prizeDayObjs as $val) {
            $accountId                                                                  = $val['account_id'];
            $machineId                                                                  = $val['machine_auto_id'];
            $roomIndexId                                                                = $val['room_index'];
            $oddsType                                                                   = $val['odds_type'];
            $result[$accountId][$machineId][$roomIndexId][$oddsType]['account_id']      = $accountId;
            $result[$accountId][$machineId][$roomIndexId][$oddsType]['machine_auto_id'] = $machineId;
            $result[$accountId][$machineId][$roomIndexId][$oddsType]['room_index']      = $roomIndexId;
            $result[$accountId][$machineId][$roomIndexId][$oddsType]['odds_type']       = $oddsType;
            $result[$accountId][$machineId][$roomIndexId][$oddsType]['create_time']     = $val['create_time'];
            $this->prizeObjToMerge($result[$accountId][$machineId][$roomIndexId][$oddsType], $val);
        }

        $insertKey = "";
        $insertArr = array();
        foreach ($result as $value) {
            foreach ($value as $objs) {
                foreach ($objs as $obj) {
                    foreach ($obj as $v) {
                        $insertKey   = " (" . implode(",", array_keys($v)) . ") ";
                        $insertArr[] = " ('" . implode("','", $v) . "') ";
                    }
                }
            }
        }
        //没有数据的时候不执行操作
        if (empty($insertArr)) {
            return true;
        }
        $insertStr = implode(" , ", $insertArr);
        $columnStr = $insertKey;
        $tr        = $PrizeModel::getDb()->beginTransaction();
        $deleteSql = "delete from {$tableName} where create_time = '{$day}'";
        $insertSql = "insert into {$tableName} {$columnStr} values {$insertStr}";
        Yii::$app->game_db->createCommand($deleteSql)->query();
        Yii::$app->game_db->createCommand($insertSql)->query();
        $tr->commit();
        return true;
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
        Tool::issetInitValue($newArr['prize_json'], array());
        Tool::issetInitValue($newArr['compare_json'], array());
        $newArr['play_score']   += $obj['play_score'];
        $newArr['win_score']    += $obj['win_score'];
        $newArr['play_number']  += $obj['play_number'];
        $newArr['win_number']   += $obj['win_number'];
        $newArr['prize_json']   = $this->mergePrizeJson($newArr['prize_json'], $obj['prize_json']);
        $newArr['compare_json'] = $this->mergePrizeJson($newArr['compare_json'], $obj['compare_json']);
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
        $prizeJsonArr    = json_decode($obj['prize_json'], true);
        $result['prize'] = array();
        foreach ($prizeJsonArr as $prizeJsonId => $prizeJsonObj) {
            //假如没有这个奖就跳过这个奖的统计
            if (!isset($newPrizeList[$prizeJsonId])) {
                continue;
            }
            Tool::issetInitValue($result['prize'][$newPrizeList[$prizeJsonId]], 0);
            $result['prize'][$newPrizeList[$prizeJsonId]] += $prizeJsonObj;
        }

        //统计每个用户比倍的数据
        $compareJsonArr = json_decode($obj['compare_json'], true);
        Tool::issetInitValue($result['比倍总玩分数'], 0);
        Tool::issetInitValue($result['比倍总赢分数'], 0);
        Tool::issetInitValue($result['比倍总玩局数'], 0);
        Tool::issetInitValue($result['比倍总赢局数'], 0);
        Tool::issetInitValue($result['过关分数'], 0);
        Tool::issetInitValue($result['过关局数'], 0);
        Tool::issetInitValue($result['爆机分数'], 0);
        Tool::issetInitValue($result['爆机局数'], 0);
        Tool::issetInitValue($result['红包来袭'], 0);

        $result['比倍总玩分数'] += $compareJsonArr['compareBetScore'];//比倍总玩分数
        $result['比倍总赢分数'] += $compareJsonArr['compareWinScore'];//比倍总赢分数
        $result['比倍总玩局数'] += $compareJsonArr['comparePlayNumber'];//比倍总玩局数
        $result['比倍总赢局数'] += $compareJsonArr['compareWinNumber'];//比倍总赢局数
        $result['过关分数']   += $compareJsonArr['compareGuoguanScore'];//过关分数
        $result['过关局数']   += $compareJsonArr['compareGuoguanNumber'];//过关局数
        $result['爆机分数']   += $compareJsonArr['compareBaojiScore'];//爆机分数
        $result['爆机局数']   += $compareJsonArr['compareBaojiNumber'];//爆机局数
        //红包来袭统计
        for ($i = 1; $i <= 5; $i++) {
            $diamondTimes = Yii::$app->params['diamondTimes'][$i];
            $diamondCount = Yii::$app->params['diamondCount'][$i];
            Tool::issetInitValue($result[$diamondTimes], 0);
            Tool::issetInitValue($result[$diamondCount], 0);
            Tool::issetInitValue($result[$diamondTimes], 0);
            Tool::issetInitValue($result[$diamondCount], 0);

            $result[$diamondTimes] += $compareJsonArr[$diamondTimes];//红包来袭统计 各个奖的次数统计
            $result[$diamondCount] += $compareJsonArr[$diamondCount];//红包来袭统计 各个奖的送钻总和统计
            $result['红包来袭']        += $compareJsonArr[$diamondCount];//记录红包来袭总数量
        }

        $result['总玩分数'] = $obj['play_score'];
        $result['总赢分数'] = $obj['win_score'];
        $result['总玩局数'] = $obj['play_number'];
        $result['总赢局数'] = $obj['win_number'];
    }
}
