<?php

namespace backend\controllers\fivepk;

use backend\models\Account;
use backend\models\Factory;
use backend\models\Tool;
use common\models\DataGameListInfo;
use common\models\DataRoomInfoList;
use common\models\FgPath;
use common\models\FivepkAccessPinots;
use common\models\FivepkPath;
use common\models\game\base\GameBase;
use common\models\game\byu\ByuRoom;
use common\models\game\FivepkAccount;
use common\models\game\FivepkPlayerInfo;
use Yii;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;

/**
 * 通用记录
 * Class OddsController
 * @package backend\controllers
 */
class CommonRecordController extends MyController
{
    /**
     * @desc 玩家兑换记录
     */
    public function actionExchange()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo   = $this->get['pageNo'];
            $pageSize = $this->get['pageSize'];

            $loginId    = $this->loginId;
            $promoCodes = $this->Account->findAllSonPopCode($loginId);

            $account   = isset($this->get['account']) ? $this->get['account'] : Tool::examineEmpty($this->get['name']);
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $stime     = isset($this->get['stime']) ? $this->get['stime'] : date('Y-m-d', $this->time);
            $etime     = isset($this->get['etime']) ? $this->get['etime'] . " 23:59:59" : date('Y-m-d 23:59:59', $this->time);

            $params = array(
                'pageNo'     => $pageNo,
                'pageSize'   => $pageSize,
                'promoCodes' => $promoCodes,
                'account'    => $account,
                'stime'      => $stime,
                'etime'      => $etime,
                'accountId'  => $accountId,
            );

            $results = $this->FivepkAccessPinots->ExchangePage($params);
            $account = $this->FivepkAccessPinots->ExchangeCount($params);
            foreach ($results as $key => $val) {
                $results[$key]['last_time']       = date("Y-m-d H:i:s", $val['last_time'] / 1000);
                $results[$key]['account']['name'] = Factory::Tool()->hideName($val['account']['name']);
            }
            $page = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
                'nowPage' => $pageNo
            );

            $this->setData($results);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * @desc 上机轨迹
     */
    public function actionEnterMachine()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName         = isset($this->get['gameName']) ? $this->get['gameName'] : "";
            if($gameName == "BYU"){
                $this->EnterMachineBYU($this->get);
                return;
            }
            $pageNo   = $this->get['pageNo'];
            $pageSize = $this->get['pageSize'];
            $account          = isset($this->get['account']) ? $this->get['account'] : "";
            $accountId        = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $machine          = isset($this->get['machine']) ? $this->get['machine'] : "";
            $stime            = isset($this->get['stime']) ? $this->get['stime'] : date('Y-m-d', $this->time);
            $etime            = isset($this->get['etime']) ? $this->get['etime'] . " 23:59:59" : date('Y-m-d 23:59:59', $this->time);
            $gameType         = "";
            $roomSeoMachineId = Tool::examineEmpty($this->get['roomSeoMachineId']);
            $loginId          = $this->loginId;
            $promoCodes       = $this->Account->findAllSonPopCode($loginId);

            if( $gameName != ""){
                $GameBaseObj = new GameBase();
                $gameObj = $GameBaseObj->initGameObj($gameName);
                $gameType = $gameObj->gameType;
            }
            $params  = array(
                'pageNo'           => $pageNo,
                'pageSize'         => $pageSize,
                'promoCodes'       => $promoCodes,
                'machine'          => $machine,
                'stime'            => $stime,
                'etime'            => $etime,
                'accountId'        => $accountId,
                'account'          => $account,
                'roomSeoMachineId' => $roomSeoMachineId,
                'gameType'         => $gameType,
            );
            $results = $this->FivepkPath->EnterMachinePage($params);
            $account = $this->FivepkPath->EnterMachineCount($params);
            $page    = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
                'nowPage' => $pageNo
            );

            $openGame = $this->DataGameListInfo->getOpenGame();

            $opGameType = array_column($openGame, 'game_number', 'game_number');

            $DataRoomInfoList = new DataRoomInfoList();
            foreach ($results as $key => $val) {
                //ip地址
                $results[$key]['address']         = $val['account']['address'];
                $results[$key]['enter_time']      = date("Y-m-d H:i:s", $val['enter_time'] / 1000);
                $results[$key]['leave_time']      = $val['leave_time'] == 0 ? 0 : date("Y-m-d H:i:s", $val['leave_time'] / 1000);
                $results[$key]['account']['name'] = Factory::Tool()->hideName($val['account']['name']);
                $results[$key]['commonJson']      = json_decode($val['common_json'], true);
                if (isset($val['room_info_list_id'])) {
                    if( $val['game_type'] == 12){
                        //赛马没有房间号  场次表是DataRoomInfoList类
                        $data                                 = $DataRoomInfoList->findBase($val['room_info_list_id']);
                        $results[$key]['room_seo_machine_id'] = $data['seo_machine_id'];
                    }
                }
                unset($results[$key]['common_json']);

                //如果开启雪豹
                if (isset($opGameType['11'])) {
                    $point                             = $this->FivepkAccessPinots->findAccessPointSumSl($val['id']);
                    $results[$key]['accessPointSum']   = Tool::examineEmpty($point['point'], 0);
                    $results[$key]['accessPointSumSl'] = Tool::examineEmpty($point['pointSl'], 0);
                } else {
                    $results[$key]['accessPointSum'] = $this->FivepkAccessPinots->findAccessPointSum($val['id']);
                }

            }

            $this->setData($results);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   为了优化速度，捕鱼的上机轨迹单独写，去掉没有用的东西
     */
    public function EnterMachineBYU($get)
    {
        $gameName         = isset($get['gameName']) ? $get['gameName'] : "";
        $pageNo           = $get['pageNo'];
        $pageSize         = $get['pageSize'];
        $account          = isset($get['account']) ? $get['account'] : "";
        $accountId        = isset($get['accountId']) ? $get['accountId'] : "";
        $machine          = isset($get['machine']) ? $get['machine'] : "";
        $stime            = isset($get['stime']) ? $get['stime'] : date('Y-m-d', $this->time);
        $etime            = isset($get['etime']) ? $get['etime'] . " 23:59:59" : date('Y-m-d 23:59:59', $this->time);
        $gameType         = "";
        $roomSeoMachineId = Tool::examineEmpty($get['roomSeoMachineId']);
        $loginId          = $this->loginId;
        $promoCodes       = $this->Account->findAllSonPopCode($loginId);

        if( $gameName != ""){
            $GameBaseObj = new GameBase();
            $gameObj = $GameBaseObj->initGameObj($gameName);
            $gameType = $gameObj->gameType;
        }
        $params  = array(
            'pageNo'           => $pageNo,
            'pageSize'         => $pageSize,
            'promoCodes'       => $promoCodes,
            'machine'          => $machine,
            'stime'            => $stime,
            'etime'            => $etime,
            'accountId'        => $accountId,
            'account'          => $account,
            'roomSeoMachineId' => $roomSeoMachineId,
            'gameType'         => $gameType,
        );
        $results = $this->FivepkPath->EnterMachinePage($params);
        $account = $this->FivepkPath->EnterMachineCount($params);
        $page    = array(
            'account' => $account,
            'maxPage' => ceil($account / $pageSize),
            'nowPage' => $pageNo
        );

        $ByuRoomModel = new ByuRoom();
        $ByuRoomList = $ByuRoomModel->tableList();
        $newByuRoomList = array();
        foreach ($ByuRoomList as $val){
            $newByuRoomList[$val['id']] = $val;
        }
        foreach ($results as $key => $val) {
            //ip地址
            $results[$key]['address']         = $val['account']['address'];
            $results[$key]['enter_time']      = date("Y-m-d H:i:s", $val['enter_time'] / 1000);
            $results[$key]['leave_time']      = $val['leave_time'] == 0 ? 0 : date("Y-m-d H:i:s", $val['leave_time'] / 1000);
            $results[$key]['account']['name'] = Factory::Tool()->hideName($val['account']['name']);
            $results[$key]['commonJson']      = json_decode($val['common_json'], true);
            //捕鱼的房间号  ByuRoom
            $roomIdArr = explode("_",$val['room_info_list_id']);
            $results[$key]['room_seo_machine_id'] = $newByuRoomList[$roomIdArr[1]]['name'];
            $results[$key]['room_id'] = $roomIdArr[1];
        }
        $this->setData($results);
        $this->setPage($page);
        $this->sendJson();
    }


    /**
     *   计算指定一个轨迹的数据
     */
    public function actionPathInfo()
    {
        Tool::checkParam(['pathId'], $this->get);
        $pathId = $this->get['pathId'];
        //获取当前还没有计算的轨迹数据
        $obj             = FivepkPath::findOne($pathId);
        $gameType        = $obj->game_type;
        $chineseGameName = Yii::$app->params['gameType'][$gameType];
        $accountId       = $obj->account_id;
        $stime           = $obj->enter_time / 1000;
        $etime           = $obj->leave_time == 0 ? $this->time : $obj->leave_time / 1000 + 1;

        $gameName = Yii::$app->params[$chineseGameName]['short'];

        $GameBaseObj = new GameBase();
        $GameObj = $GameBaseObj->initGameObj($gameName);
        $prizeDayModelObj = $GameObj->getModelPrizeDay();

        $commonJson     = $prizeDayModelObj->LocusToPath($accountId, $stime, $etime);
        $obj->common_json   = json_encode($commonJson, JSON_UNESCAPED_UNICODE);
        $data               = $obj->toArray();
        $data['commonJson'] = json_decode($data['common_json'], true);
        unset($data['common_json']);
        $obj->save();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * @desc 留机轨迹
     */
    public function actionLeaveMachine()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo     = $this->get['pageNo'];
            $pageSize   = $this->get['pageSize'];
            $loginId    = $this->loginId;
            $promoCodes = $this->Account->findAllSonPopCode($loginId);

            $account   = isset($this->get['account']) ? $this->get['account'] : "";
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $machine   = isset($this->get['machine']) ? $this->get['machine'] : "";
            $stime     = isset($this->get['stime']) ? $this->get['stime'] : date('Y-m-d', $this->time);
            $etime     = isset($this->get['etime']) ? $this->get['etime'] . " 23:59:59" : date('Y-m-d 23:59:59', $this->time);

            $params = array(
                'pageNo'     => $pageNo,
                'pageSize'   => $pageSize,
                'promoCodes' => $promoCodes,
                'accountId'  => $accountId,
                'account'    => $account,
                'machine'    => $machine,
                'stime'      => $stime,
                'etime'      => $etime,
            );

            $results = $this->FivepkPath->LeaveMachinePage($params);
            $account = $this->FivepkPath->LeaveMachineCount($params);
            foreach ($results as $key => $val) {
                $results[$key]['leave_time']      = date("Y-m-d H:i:s", $val['leave_time'] / 1000);
                $results[$key]['account']['name'] = Factory::Tool()->hideName($val['account']['name']);
                $overTime                         = $val['leave_time'] + 60 * $val['dataReservation']['mill'] * 1000;
                $results[$key]['over_time']       = date("Y-m-d H:i:s", $overTime / 1000);
            }
            $page = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
                'nowPage' => $pageNo
            );
            $this->setData($results);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  留存报表
     */
    public function actionStatRemain()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $where = " 1";
            if (isset($this->get['time'])) {
                $time  = $this->get['time'] . ' 00:00:00';
                $where .= " and stat_time = '{$time}'";
            }
            $pageNo   = $this->get['pageNo'];
            $pageSize = $this->get['pageSize'];

            $data  = $this->StatRemain->page($pageNo, $pageSize, $where);
            $count = $this->StatRemain->pageCount($where);
            $page  = array(
                'account' => $count,
                'maxPage' => ceil($count / $pageSize),
                'nowPage' => $pageNo
            );
            foreach ($data as $key => $val) {
                $data[$key]['second_day']    = round($val['second_day'] * 100, 2) . "%";
                $data[$key]['third_day']     = round($val['third_day'] * 100, 2) . "%";
                $data[$key]['seventh_day']   = round($val['seventh_day'] * 100, 2) . "%";
                $data[$key]['fourteen_day']  = round($val['fourteen_day'] * 100, 2) . "%";
                $data[$key]['thirtieth_day'] = round($val['thirtieth_day'] * 100, 2) . "%";
            }
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  新留存报表
     */
    public function actionReportRemain()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $where = " 1";
            if (isset($this->get['time'])) {
                $time  = $this->get['time'] . ' 00:00:00';
                $where .= " and stat_time = '{$time}'";
            }

            $channel = Tool::examineEmpty($this->get['channel'], 'ALL');
            $where   .= " and channel = '$channel'";

            $pageNo       = $this->get['pageNo'];
            $pageSize     = $this->get['pageSize'];
            $ReportRemain = new \common\models\game\ReportRemain();

            $data  = $ReportRemain->page($pageNo, $pageSize, $where);
            $count = $ReportRemain->pageCount($where);
            $page  = array(
                'account' => $count,
                'maxPage' => ceil($count / $pageSize),
                'nowPage' => $pageNo
            );

            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  热度报表
     */
    public function actionReportHot()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize']) || !isset($this->get['stime']) || !isset($this->get['etime'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];
            $stime     = $this->get['stime'];
            $etime     = $this->get['etime'];
            $where     = " create_time >= '{$stime}' and create_time <= '{$etime}'";
            if ($accountId != "") {
                $where .= " and account_id = '{$accountId}'";
            }

            //获取条件下所有的用户id
            $accountObjs = $this->FivepkReportHot->getAccountIdsInTime($where);
            $count       = count($accountObjs);
            $maxPage     = ceil($count / $pageSize);
            $page        = array(
                'account' => $count,
                'maxPage' => $maxPage,
                'nowPage' => $pageNo
            );
            //取出当前页的accountId
            $accountIds   = array_slice($accountObjs, ($pageNo - 1) * $pageSize, $pageSize);
            $accountIdStr = implode(",", $accountIds);
            $where        .= " and account_id in ({$accountIdStr})";

            //原始数据求和
            $result = array();
            $data   = $this->FivepkReportHot->tableList($where);
            foreach ($data as $val) {
                $o = json_decode($val['origin'], true);
                if (empty($result[$val['account_id']][$val['game_type']])) {
                    $result[$val['account_id']][$val['game_type']] = $o;
                } else {
                    foreach ($result[$val['account_id']][$val['game_type']] as $key => $v) {
                        $result[$val['account_id']][$val['game_type']][$key] += $o[$key];
                    }
                }

            }

            //求盈利和各种几率
            $OpenGame = $this->DataGameListInfo->getOpenGame();
            foreach ($OpenGame as $val) {
                $selfModelClassName = Yii::$app->params[$val['game_name']]['selfModel'];
                $selfModel          = new $selfModelClassName();
                $gameType           = $val['game_number'];
                if ($gameType == 1) {
                    $selfModel->statistics($result);
                }
            }

            $rsData = array();
            //整理数据
            foreach ($result as $accountId => $val) {
                //求总几率，需要统计当前玩家下的所有的 总赢分数 和 总玩分数
                $sumWinScore                 = 0;
                $sumPlayScore                = 0;
                $sumWinNumber                = 0;
                $sumPlayNumber               = 0;
                $rsData[$accountId]['玩家总盈利'] = 0;
                foreach ($val as $v) {
                    //总盈利可以把多个游戏盈利之和相加
                    $rsData[$accountId]['玩家总盈利'] += $v['玩家盈利'];
                    $sumWinScore                 += $v['总赢分数'];
                    $sumPlayScore                += $v['总玩分数'];
                    $sumWinNumber                += $v['总赢局数'];
                    $sumPlayNumber               += $v['总玩局数'];
                }
                $rsData[$accountId]['玩家总游戏几率']   = round($sumWinScore / $sumPlayScore, 2) * 100;
                $rsData[$accountId]['玩家总中奖率']    = round($sumWinNumber / $sumPlayNumber, 2) * 100;
                $rsData[$accountId]['accountId'] = $accountId;
                $FivepkPlayerInfoObj             = FivepkPlayerInfo::findOne($accountId);
                //$FivepkAccountObj    = FivepkAccount::findOne($accountId);
                $rsData[$accountId]['nick_name'] = isset($FivepkPlayerInfoObj->nick_name) ? $FivepkPlayerInfoObj->nick_name : "";
                //$rsData[$accountId]['popCode']   = isset($FivepkAccountObj->seoid) ? $FivepkAccountObj->seoid : "";
                $rsData[$accountId]['gameInfo'] = $val;
            }
            $this->setData($rsData);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


}

?>