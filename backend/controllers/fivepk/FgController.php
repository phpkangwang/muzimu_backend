<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-5
 * Time: 18:27
 */

namespace backend\controllers\fivepk;

use backend\models\Account;
use backend\models\Factory;
use backend\models\Tool;
use common\models\DataGameListInfo;
use common\models\FgPath;
use backend\controllers\MyController;

class FgController extends MyController
{

    /**
     * @desc fg上机轨迹
     */
    public function actionEnterMachine()
    {
        Tool::checkParam(['pageNo', 'pageSize', 'stime', 'etime'], $this->get);
        $pageNo    = $this->get['pageNo'];
        $pageSize  = $this->get['pageSize'];
        $stime     = $this->get['stime'];
        $etime     = $this->get['etime'];
        $accountId = Tool::examineEmpty($this->get['accountId'], "");
        $account   = Tool::examineEmpty($this->get['account'], "");
        $gameType  = Tool::examineEmpty($this->get['gameType'], "");

        $loginId    = $this->loginId;
        $Account    = new Account();
        $promoCodes = $Account->findAllSonPopCode($loginId);

        $params = array(
            'pageNo'     => $pageNo,
            'pageSize'   => $pageSize,
            'promoCodes' => $promoCodes,
            'stime'      => $stime,
            'etime'      => $etime,
            'accountId'  => $accountId,
            'account'    => $account,
            'gameType'   => $gameType,
        );

        $FgPath  = new FgPath();
        $results = $FgPath->EnterMachinePage($params);
        $account = $FgPath->EnterMachineCount($params);
        $page    = array(
            'account' => $account,
            'maxPage' => ceil($account / $pageSize),
            'nowPage' => $pageNo
        );

        $DataGameListInfo = new DataGameListInfo();

        $openGame = $DataGameListInfo->getOpenGame();
        $openGame = array_column($openGame, 'game_name', 'game_type');

        foreach ($results as $key => $val) {
            //ip地址
            $results[$key]['address']    = $val['account']['address'];
            $results[$key]['enter_time'] = $val['enter_time'] == 0 ? 0 : date("Y-m-d H:i:s", $val['enter_time'] / 1000);
            $results[$key]['leave_time'] = $val['leave_time'] == 0 ? 0 : date("Y-m-d H:i:s", $val['leave_time'] / 1000);
            $results[$key]['game_name']  = isset($openGame[$val['game_type']]) ? $openGame[$val['game_type']] : '';
            $results[$key]['name']       = Factory::Tool()->hideName($val['name']);
            $results[$key]['profit']     = 0;
            if ($val['leave_time'] != 0) {
                $results[$key]['profit'] = (($val['enter_score'] - $val['leave_score']) / 100 + ($val['enter_coin'] - $val['leave_coin']));
            }
        }

        $this->setData($results);
        $this->setPage($page);
        $this->sendJson();
    }


    /**
     *   fg游戏玩家记录
     */
    public function actionRecordPlayer()
    {
        Tool::checkParam(['pageNo', 'pageSize', 'stime', 'etime'], $this->get);
        $param['pageNo']    = $this->get['pageNo'];
        $param['pageSize']  = $this->get['pageSize'];
        $param['sday']      = $this->get['stime'];
        $param['eday']      = $this->get['etime'];
        $param['accountId'] = isset($this->get['accountId']) ? $this->get['accountId'] : "";
        $param['gameName']  = isset($this->get['gameName']) ? $this->get['gameName'] : "";
        $param['gameType']  = Tool::examineEmpty($this->get['gameType'], "");
        $param['stime']     = strtotime($param['sday']);
        $param['etime']     = strtotime($param['eday']);

        $FgRecordPlayerModel = new FgPath();
        $result              = $FgRecordPlayerModel->RecordPlayerPage($param);
        $profitSum           = $FgRecordPlayerModel->RecordPlayerPageSum($param);
        $resultSum['盈利']     = $profitSum;
        $this->setData(['result' => $result, 'resultSum' => $resultSum, 'time' => time() - 12 * 3600]);
        $this->sendJson();
    }

    /**
     *   fg游戏游戏记录
     */
    public function actionRecordGame()
    {
        Tool::checkParam(['pageNo', 'pageSize', 'stime', 'etime'], $this->get);
        $param['pageNo']   = $this->get['pageNo'];
        $param['pageSize'] = $this->get['pageSize'];
        $param['sday']     = $this->get['stime'];
        $param['eday']     = $this->get['etime'];
        $param['stime']    = strtotime($param['sday']);
        $param['etime']    = strtotime($param['eday']);

        $FgRecordPlayerModel = new FgPath();
        $result              = $FgRecordPlayerModel->RecordGamePage($param);
        $profitSum           = $FgRecordPlayerModel->RecordGamePageSum($param);
        $resultSum['盈利']     = $profitSum;
        $this->setData(['result' => $result, 'resultSum' => $resultSum, 'time' => time() - 12 * 3600]);
        $this->sendJson();
    }

}