<?php

namespace backend\controllers\finance;

use backend\models\Factory;
use backend\models\redis\MyRedis;
use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\game\base\GameBase;
use common\models\RecordDiamond;
use common\models\RecordHits;
use Yii;
use backend\controllers\MyController;
use backend\models\search\FivepkDayContributionSearch;
use common\models\ExperienceReport;
use common\models\game\FivepkAccount;
use common\models\game\FivepkPlayerInfo;
use common\models\OnlinePlayerOftenCount;
use common\models\OnlinePlayerTotalCount;
use backend\models\ErrorCode;
use backend\models\MyException;

class RecordController extends MyController
{
    /**
     * @desc 玩家开洗分 开洗分
     *  玩家用户名 $username
     *  昵称       $nickname
     *  推广号     $promo_code
     */
    public function actionUserDiamondInfo()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo     = $this->get['pageNo'];
            $pageSize   = $this->get['pageSize'];
            $account    = isset($this->get['account']) ? $this->get['account'] : Tool::examineEmpty($this->get['name']);
            $accountId  = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $promo_code = isset($this->get['promo_code']) ? strtoupper($this->get['promo_code']) : "";
            $isOnline   = isset($this->get['is_online']) ? $this->get['is_online'] : "";

            //$type 1开洗分列表 2道具列表
            $type = isset($this->get['type']) ? $this->get['type'] : 1;

            $popCodeArr   = $this->Account->findAllSonPopCode($this->loginId);
            $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";

            $where = " fivepk_account.seoid in ({$popCodeStrIn})";
            if (!empty($account)) {
                $where .= " and fivepk_account.name like '%{$account}%'";
            }
            if (!empty($accountId)) {
                $where .= " and fivepk_account.account_id = '{$accountId}'";
            }
            if (!empty($promo_code)) {
                $where .= " and fivepk_account.seoid = '{$promo_code}'";
            }
            if ($isOnline != "") {
                $where .= " and fivepk_player_info.is_online = '{$isOnline}'";
            }

            $data = $this->FivepkAccount->UserDiamondInfoPage($pageNo, $pageSize, $where);

            foreach ($data as &$val) {
                $val['name'] = Factory::Tool()->hideName(($val['name']));
            }

            $account = $this->FivepkAccount->UserDiamondInfoCount($where);
            $page    = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
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
     * @desc 开洗分 记录
     */
    public function actionUserDiamondRecord()
    {

        Factory::RecordController()->actionUserDiamondRecord($this);

        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo       = $this->get['pageNo'];
            $pageSize     = $this->get['pageSize'];
            $account      = isset($this->get['account']) ? $this->get['account'] : Tool::examineEmpty($this->get['name']);
            $accountId    = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $promo_code   = isset($this->get['promo_code']) ? strtoupper($this->get['promo_code']) : "";
            $operator     = isset($this->get['operator']) ? $this->get['operator'] : "";
            $operatorType = isset($this->get['operatorType']) ? $this->get['operatorType'] : "";
            $stime        = isset($this->get['stime']) ? $this->get['stime'] : date('Y-m-d', $this->time);
            $etime        = isset($this->get['etime']) ? $this->get['etime'] . " 23:59:59" : date('Y-m-d 23:59:59', $this->time);


            $popCodeArr   = $this->Account->findAllSonPopCode($this->loginId);
            $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";
            $where        = " fivepk_account.seoid in ({$popCodeStrIn})";

            if (!empty($account)) {
                $where .= " and fivepk_account.name like '%{$account}%'";
            }
            if (!empty($accountId)) {
                $where .= " and fivepk_account.account_id = '{$accountId}'";
            }
            if (!empty($promo_code)) {
                $where .= " and fivepk_account.seoid = '{$promo_code}'";
            }
            if (!empty($operator)) {
                $where .= " and fivepk_point.operator like '%{$operator}%'";
            }
            if (!empty($operatorType)) {
                $where .= " and fivepk_point.operator_type = '$operatorType'";
            }
            if (!empty($stime)) {
                $where .= " and fivepk_point.operate_time > '{$stime}'";
            }
            if (!empty($etime)) {
                $where .= " and fivepk_point.operate_time <= '{$etime}'";
            }
            $data = $this->FivepkPoint->UserDiamondRecordPage($pageNo, $pageSize, $where);
            foreach ($data as &$val) {
                $val['name'] = '';
                if (isset($val['account']['name'])) {
                    $val['account']['name'] = Factory::Tool()->hideName($val['account']['name']);
                    $val['name']            = Factory::Tool()->hideName($val['account']['name']);
                }
            }
            $account = $this->FivepkPoint->UserDiamondRecordCount($where);
            $page    = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
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
     *  玩家开钻石
     */
    public function actionUserDiamondUpdate()
    {
        try {
            //如果接受钻石的人不存在或者数量不存在
            if (!isset($this->post['sendPopCode']) || !isset($this->post['acceptUserId']) || !isset($this->post['num'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $post                = $this->post;
            $post['operateName'] = $this->loginInfo['name'];
            $MyRedis             = new MyRedis();
            $key                 = "DiamondChangeTime:" . $this->post['sendPopCode'];
            $DiamondChangeTime   = $MyRedis->get($key);
            if (!empty($DiamondChangeTime)) {
                throw new MyException(ErrorCode::ERROR_DIAMOND_FREQUENCE_LIMIT);
            }
            $MyRedis->set($key, "1");
            Factory::RecordController()->UserDiamondUpdate($post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  玩家开洗分
     */
    public function actionUserScoreUpdate()
    {
        try {
            //如果接受钻石的人不存在或者数量不存在
            if (!isset($this->post['sendPopCode']) || !isset($this->post['acceptUserId']) || !isset($this->post['num'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $sendPopCode  = $this->post['sendPopCode'];
            $acceptUserId = $this->post['acceptUserId'];
            $num          = $this->post['num'];

            if ($num == 0) {
                throw new MyException(ErrorCode::ERROR_DIAMOND_NUM);
            }
            //开启事务
            $tr = Yii::$app->db->beginTransaction();

            $acceptUserObj   = FivepkAccount::findOne($acceptUserId);
            $acceptPlayerObj = FivepkPlayerInfo::findOne($acceptUserId);
            //玩家是否存在
            if (empty($acceptUserObj) || empty($acceptPlayerObj)) {
                throw new MyException(ErrorCode::ERROR_USER_NOT_EXIST);
            }

            //下钻的时候玩家必须不在线
            if ($acceptPlayerObj->is_online == 1 && $num < 0) {
                throw new MyException(ErrorCode::ERROR_DIAMOND_IS_ONLINE);
            }

            //如果是下分，玩家的分必须大于改变的分
            if ($acceptPlayerObj->score + $num < 0) {
                throw new MyException(ErrorCode::ERROR_SCORE_NUM_MINUS);
            }

            //给玩家添加分数
            $data = [
                'accountId' => $acceptUserId,
                'type'      => $num > 0 ? 1 : 0,
                'operator'  => $this->loginInfo['name'],
                'point'     => abs($num),
                'seoid'     => $sendPopCode,
            ];
            $this->remoteInterface->fraction($data);
            $tr->commit();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  用户营业额报表
     */
    public function actionUserReport()
    {
        Factory::RecordController()->actionUserReport($this);
    }

    /**
     *  用户营业额报表-贡献度
     */
    public function actionUserReportList()
    {
        Factory::RecordController()->actionUserReportList($this);
    }


    /**
     * @desc 玩家营业额报表详情
     */
    public function actionPlayerReport()
    {
        Factory::RecordController()->actionPlayerReport($this);
    }

    /**
     *  玩家营业额报表详情-贡献度
     */
    public function actionPlayerReportList()
    {
        Factory::RecordController()->actionPlayerReportList($this);
    }

    /**
     * @desc 日报表
     */
    public function actionUserDaily()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $time      = isset($this->get['time']) ? $this->get['time'] : "";
            $popCode   = isset($this->get['popCode']) ? strtoupper($this->get['popCode']) : "";
            $sort      = isset($this->get['sort']) ? $this->get['sort'] : "";
            $sortType  = isset($this->get['sortType']) ? strtolower($this->get['sortType']) : "desc";

            $loginId    = $this->loginId;
            $popCodeArr = $this->Account->findAllSonPopCode($loginId);
            if (empty($popCodeArr)) {
                $this->sendJson();
                return;
            }
            $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";

            $where        = " fivepk_account.seoid in ($popCodeStrIn)";
            $orderBy      = "id desc";
            $tableColumns = $this->FivepkDayContribution->attributes();

            if (!empty($sort)) {
                if (!in_array($sort, $tableColumns)) {
                    //throw new MyException( ErrorCode::ERROR_PARAM );
                    $orderBy = "fivepk_player_info." . $sort . " " . $sortType;
                } else {
                    $orderBy = $sort . " " . $sortType;
                }
            }

            if (!empty($accountId)) {
                $where .= " and fivepk_player_info.account_id = '{$accountId}'";
            }

            if (!empty($time)) {
                $where .= " and fivepk_day_contribution.record_time = '{$time}'";
            }

            if (!empty($popCode)) {
                $where .= " and fivepk_account.seoid = '{$popCode}'";
            }

            $data    = $this->FivepkDayContribution->Page($where, $orderBy, $pageNo, $pageSize);
            $account = $this->FivepkDayContribution->Count($where);

            //获取所有开启的游戏
            $DataGameListInfoObjs    = $this->DataGameListInfo->getOpenGame();
            $newDataGameListInfoObjs = array();
            foreach ($DataGameListInfoObjs as $val) {
                $newDataGameListInfoObjs[$val['game_number']] = $val;
            }


            //获取所有的用户
            $accountIdArr        = array_column($data, 'account_id');
            $OldPlayerSwitchObjs = $this->OldPlayerSwitch->finds($accountIdArr);
            foreach ($data as $key => $val) {
                $openStr          = "";
                $lastGameOpenTime = 0;
                foreach ($OldPlayerSwitchObjs as $OldPlayerSwitchObj) {
                    if ($OldPlayerSwitchObj['open_time'] > $lastGameOpenTime && $val['account_id'] == $OldPlayerSwitchObj['account_id']) {
                        $lastGameOpenTime = $OldPlayerSwitchObj['open_time'];
                        $openStr          = date("m-d", $OldPlayerSwitchObj['open_time']);
                    }
                }
                $data[$key]['OldPlayerSwitch'] = $openStr;
            }

            $page = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
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
     * @desc 月报表
     */
    public function actionUserMonthly()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];
            $year      = isset($this->get['year']) ? $this->get['year'] : date("Y", time());
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $popCode   = isset($this->get['popCode']) ? strtoupper($this->get['popCode']) : "";
            $sort      = isset($this->get['sort']) ? $this->get['sort'] : "";
            $sortType  = isset($this->get['sortType']) ? strtolower($this->get['sortType']) : "desc";

            $loginId    = $this->loginId;
            $popCodeArr = $this->Account->findAllSonPopCode($loginId);
            if (empty($popCodeArr)) {
                $this->sendJson();
                return;
            }
            $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";

            $where   = " fivepk_account.seoid in ($popCodeStrIn)";
            $orderBy = "id desc";

            $tableColumns = $this->FivepkMonthContribution->attributes();
            if (!empty($sort)) {
                if (!in_array($sort, $tableColumns)) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                } else {
                    $orderBy = $sort . " " . $sortType;
                }
            }

            if (!empty($year)) {
                $where .= " and record_time = '{$year}'";
            }
            if (!empty($accountId)) {
                $where .= " and fivepk_account.account_id = '{$accountId}'";
            }
            if (!empty($popCode)) {
                $where .= " and fivepk_account.seoid = '{$popCode}'";
            }

            $data    = $this->FivepkMonthContribution->Page($where, $orderBy, $pageNo, $pageSize);
            $account = $this->FivepkMonthContribution->Count($where);
            $page    = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
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
     * @desc 峰值记录
     * @return string
     */
    public function actionUserPeakRecord()
    {
        Factory::RecordController()->actionUserPeakRecord($this);

        $data = OnlinePlayerTotalCount::find()->filterWhere(['status' => 10])->orderBy('id DESC')->asArray()->all();
        foreach ($data as $key => $val) {
            $data[$key]['created_at'] = date("Y-m-d H:i:s", $val['created_at']);
        }
        $this->setData($data);
        $this->sendJson();

    }

    /**
     * 送钻报表
     * @param null $start_time
     * @param null $end_time
     * @return string
     */
    public function actionSendDiamond()
    {
        $data = ExperienceReport::find()->orderBy('id DESC')->asArray()->all();
        //加上新统计送的钻石
        $newDataList = $this->RecordDiamond->tableList();
        foreach ($data as $key => $val) {
            foreach ($newDataList as $newData) {
                if ($val['time'] == $newData['create_time']) {
                    //计算总人数
                    if ($newData['prize_award_type'] == 0) {
                        $data[$key]['diamond']            += $newData['award_sum'];
                        $data[$key]['get_diamond_player'] += $newData['people_sum'];
                    }
                    //计算奖励1
                    if ($newData['prize_award_type'] == 1) {
                        $data[$key]['best_bet1'] += $newData['prize_award_num'];
                    }
                    //计算奖励2
                    if ($newData['prize_award_type'] == 2) {
                        $data[$key]['best_bet2'] += $newData['prize_award_num'];
                    }
                    //计算奖励3
                    if ($newData['prize_award_type'] == 3) {
                        $data[$key]['best_bet3'] += $newData['prize_award_num'];
                    }
                    //计算奖励4
                    if ($newData['prize_award_type'] == 4) {
                        $data[$key]['best_bet4'] += $newData['prize_award_num'];
                    }
                    //计算奖励5
                    if ($newData['prize_award_type'] == 5) {
                        $data[$key]['best_bet5'] += $newData['prize_award_num'];
                    }

                }
            }
        }
        $this->setData($data);
        $this->sendJson();
    }


    /**
     * 人气报表
     * @return string
     */
    public function actionHitsReport()
    {
        try {
            if (!isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->get['gameName'];
            $oddsType = isset($this->get['oddsType']) ? $this->get['oddsType'] : "";
            $day      = date("Y-m-d", time());

            $RecordHits = new RecordHits();

            if ($oddsType == "") {
                //初始化人气报表那一天的数据, 为了防止频繁刷数据，人气报表只有查看总的时候才刷新今天的数据
                $RecordHits->RecordToday($day, $gameName);
            }

            //前端要显示的list
            $headerList        = ["盈利", "游戏人数", "总玩局数", "游戏机率", "中奖率"];
            $tableHeader       = array();
            $tableHeader['新增'] = array();
            $tableHeader['日期'] = array();

            $GameBaseObj = new GameBase();
            $GameObj = $GameBaseObj->initGameObj($gameName);
            $gameType = $GameObj->gameType;
            $roomLevelObj      = $GameObj->getRoom();
            $newRoomLevelObj   = array();
            foreach ($roomLevelObj as $val) {
                $newRoomLevelObj[$val['room_index']] = $val;
                //不要体验场
                if ($val['room_index'] <= 1 && $gameName != "BYU" ) {
                }else{
                    foreach ($headerList as $header) {
                        $tableHeader[$header]   = isset($tableHeader[$header]) ? $tableHeader[$header] : array();
                        $tableHeader[$header][] = $val['name'];
                    }
                }
            }
            foreach ($headerList as $header) {
                array_push($tableHeader[$header], "合计");
            }

            //获取数据
            $data = $RecordHits->findByGameType($gameType, $oddsType);
            $list = array();
            foreach ($data as $key => $val) {
                $list[$key]['新增']   = $val['people'];
                $list[$key]['日期']   = $val['create_time'];
                $list[$key]['盈利']   = $this->hitReportJsonToshow($val['profit_json'], $newRoomLevelObj);
                $list[$key]['游戏人数'] = $this->hitReportJsonToshow($val['people_game_json'], $newRoomLevelObj);
                $list[$key]['总玩局数'] = $this->hitReportJsonToshow($val['play_num_json'], $newRoomLevelObj);
                $list[$key]['游戏机率'] = $this->hitReportJsonToshow($val['odds_json'], $newRoomLevelObj);
                $list[$key]['中奖率']  = $this->hitReportJsonToshow($val['award_json'], $newRoomLevelObj);
            }
            $data = array('tableHeader' => $tableHeader, 'list' => $list);
            $this->setData($data);
            $this->sendJson();
            return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    //专门解析热度报表的json
    public function hitReportJsonToshow($json, $newRoomLevelObj)
    {
        $arr = json_decode($json, true);
        $rs  = array();
        foreach ($arr as $key => $val) {
            $name      = isset($newRoomLevelObj[$key]['name']) ? $newRoomLevelObj[$key]['name'] : $key;
            $rs[$name] = $val;
        }
        return $rs;
    }

    public function actionPeakEcharts()
    {
        $date   = empty($this->get['date']) ? date('Y-m-d') : $this->get['date'];
        $start  = strtotime($date);
        $end    = strtotime($date . " +1 day");
        $models = OnlinePlayerOftenCount::find()->filterWhere(['between', 'created_at', $start, $end])->orderBy('id ASC')->asArray()->all();
        $result = [];

        foreach ($models as $model) {
            $result['online'][] = $model['online'];
            $result['time'][]   = date('H:i', $model['created_at']);
        }
        $this->setData($result);
        $this->sendJson();
    }

    /**
     *   历史新人奖列表
     */
    public function actionFreshAwardHistory()
    {
        if (!isset($this->get['gameName'])) {
            throw new MyException(ErrorCode::ERROR_PARAM);
        }

        $day = date("Y-m-d", time());
        $this->FivepkRecordFreshWin->initDay($day);

        $gameName        = $this->get['gameName'];
        $chineseGameName = Yii::$app->params['game'][$gameName];
        $gameType        = Yii::$app->params[$chineseGameName]['gameType'];

        $FreshWinWhere = " game_type = '{$gameType}'";
        $data          = $this->FivepkRecordFreshWin->findList($FreshWinWhere);
        foreach ($data as $key => $val) {
            $data[$key]['content'] = json_decode($val['content'], true);
        }
        $this->setData($data);
        $this->sendJson();
    }


    /**
     * 送钻报表
     * @param null $start_time
     * @param null $end_time
     * @return string
     */
    public function actionSendDiamond1()
    {
        $newRs = Factory::RecordController()->SendDiamond1();
        $this->setData($newRs);
        $this->sendJson();
    }

    /**
     * 新轨迹送钻报表
     */
    public function actionSendDiamond2()
    {
        $newRs = RecordDiamond::SendDiamond1();
        $this->setData($newRs);
        $this->sendJson();
    }


    /**
     *   用户 奖券统计  除了显示当天的，还要显示历史的数据
     */
    public function actionRecordTicketAccount()
    {
        try {
            if (!isset($this->get['stime']) || !isset($this->get['etime']) ||
                !isset($this->get['pageNo']) || !isset($this->get['pageSize'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $stime     = $this->get['stime'];
            $etime     = $this->get['etime'];
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";

            //如果是查询的包含今天的数据
            if ($this->Tool->isToday(strtotime($stime)) || $this->Tool->isToday($etime)) {
                $this->BackendRecordItemDay->RecordTodayInit();
                //$this->BackendRecordItemDaySum->RecordTodayInit();
            }

            $where = " rid.create_time >= '{$stime}' and rid.create_time <= '{$etime}'";
            if (!empty($accountId)) {
                $where .= " and rid.account_id = '{$accountId}'";
            }

            $data = $this->BackendRecordItemDay->page($pageNo, $pageSize, $where);
            //按条件获取数据
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   房间 奖券统计  除了显示当天的，还要显示历史的数据
     */
    public function actionRecordTicketRoom()
    {
        try {
            if (!isset($this->get['stime']) || !isset($this->get['etime']) ||
                !isset($this->get['pageNo']) || !isset($this->get['pageSize'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $stime     = $this->get['stime'];
            $etime     = $this->get['etime'];
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";

            //如果是查询的包含今天的数据
            if ($this->Tool->isToday($stime) || $this->Tool->isToday($etime)) {
                $this->BackendRecordItemDay->RecordTodayInit();
                //$this->BackendRecordItemDaySum->RecordTodayInit();
            }

            $where = " rid.create_time >= '{$stime}' and rid.create_time <= '{$etime}'";
            if (!empty($accountId)) {
                $where .= " and rid.account_id = '{$accountId}'";
            }

            //获取表头
            $tableHeader = $this->BackendRecordItemDay->getAllRoom();
            //获取数据
            $BackendRecordItemDayObjs = $this->BackendRecordItemDay->pageRoom($pageNo, $pageSize, $where);
            $rs                       = array();
            foreach ($BackendRecordItemDayObjs as $key => $val) {
                if (!isset($tableHeader[$val['game_type']]['roomLevel'][$val['room_index']])) {
                    continue;
                }
                $roomName = $tableHeader[$val['game_type']]['roomLevel'][$val['room_index']];

                $rs[$val['create_time']][$val['game_type']][$roomName] = $val['num'];
                $rs[$val['create_time']]['sum_day']                    = isset($rs[$val['create_time']]['sum_day']) ? $rs[$val['create_time']]['sum_day'] : 0;
                $rs[$val['create_time']]['sum_day']                    += $val['num'];
            }

            $data = array(
                'tableHeader' => $tableHeader,
                'list'        => $rs
            );
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   用户 兑换统计  除了显示当天的，还要显示历史的数据
     */
    public function actionRecordExchangeAccount()
    {
        try {
            if (!isset($this->get['stime']) || !isset($this->get['etime']) ||
                !isset($this->get['pageNo']) || !isset($this->get['pageSize'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $stime     = $this->get['stime'];
            $etime     = $this->get['etime'];
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";

            //如果是查询的包含今天的数据
            if ($this->Tool->isToday(strtotime($stime)) || $this->Tool->isToday(strtotime($etime))) {
                $this->StoreItemExchangeRecordDay->RecordTodayInit();
            }

            $where = " rid.create_time >= '{$stime}' and rid.create_time <= '{$etime}'";
            if (!empty($accountId)) {
                $where .= " and rid.account_id = '{$accountId}'";
            }
            $list = $this->StoreItemExchangeRecordDay->page($pageNo, $pageSize, $where);
            $rs   = array();

            $i = 0;
            foreach ($list as $val) {
                $rs[$val['create_time']][$val['account_id']]['time'][$val['item_exchange_list_order']] = $val['num'];
                $rs[$val['create_time']][$val['account_id']]['nickName']                               = $val['nick_name'];
                $i++;
            }
            $DataCount = $this->StoreItemExchangeRecordDay->DataCount($where);
            $data      = array(
                'list'      => $rs,
                'DataCount' => $DataCount,
            );
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   兑换统计 每天 除了显示当天的，还要显示历史的数据
     */
    public function actionRecordExchangeDay()
    {
        try {
            if (!isset($this->get['stime']) || !isset($this->get['etime'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $stime     = $this->get['stime'];
            $etime     = $this->get['etime'];
            $pageNo    = 1;
            $pageSize  = 9999999;
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";

            //如果是查询的包含今天的数据
            if ($this->Tool->isToday(strtotime($stime)) || $this->Tool->isToday(strtotime($etime))) {
                $this->StoreItemExchangeRecordDay->RecordTodayInit();
            }

            $where = " rid.create_time >= '{$stime}' and rid.create_time <= '{$etime}'";
            if (!empty($accountId)) {
                $where .= " and rid.account_id = '{$accountId}'";
            }

            $list      = $this->StoreItemExchangeRecordDay->pageDay($pageNo, $pageSize, $where);
            $DataCount = $this->StoreItemExchangeRecordDay->DataCount($where);
            $data      = array(
                'list'      => $list,
                'DataCount' => $DataCount,
            );
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  道具查看
     */
    public function actionPackList()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo     = $this->get['pageNo'];
            $pageSize   = $this->get['pageSize'];
            $account    = isset($this->get['account']) ? $this->get['account'] : Tool::examineEmpty($this->get['name']);
            $accountId  = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $promo_code = isset($this->get['promo_code']) ? strtoupper($this->get['promo_code']) : "";
            $isOnline   = isset($this->get['is_online']) ? $this->get['is_online'] : "";

            $popCodeArr   = $this->Account->findAllSonPopCode($this->loginId);
            $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";

            $where = " fivepk_account.seoid in ({$popCodeStrIn})";
            if (!empty($account)) {
                $where .= " and fivepk_account.name like '%{$account}%'";
            }
            if (!empty($accountId)) {
                $where .= " and fivepk_account.account_id = '{$accountId}'";
            }
            if (!empty($promo_code)) {
                $where .= " and fivepk_account.seoid = '{$promo_code}'";
            }
            if ($isOnline != "") {
                $where .= " and fivepk_player_info.is_online = '{$isOnline}'";
            }

            $data                      = $this->FivepkAccount->UserDiamondInfoPage($pageNo, $pageSize, $where);
            $accounts                  = array_column($data, 'account_id');
            $class                     = new \common\models\pay\SotreStoragePackItems();
            $class->potion['link']     = function ($obj) use ($accounts) {
                $obj->select('item_count,account_id');
                $obj->indexBy('account_id')->filterWhere(array('in', 'account_id', $accounts))->andWhere('item_type=1');
            };
            $SotreStoragePackItemsData = $class->pageData();
            foreach ($data as &$val) {
                $val['name']       = Factory::Tool()->hideName(($val['name']));
                $val['item_count'] = Tool::examineEmpty($SotreStoragePackItemsData[$val['account_id']]['item_count'], 0);
            }

            $account = $this->FivepkAccount->UserDiamondInfoCount($where);
            $page    = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
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
     *  道具修改
     */
    public function actionUpdatePack()
    {

        try {
            $type         = intval(Tool::examineEmpty($this->post['type']));//1加2减
            $itemType     = Tool::examineEmpty($this->post['itemType']);
            $acceptUserId = intval(Tool::examineEmpty($this->post['acceptUserId']));
            $num          = intval(Tool::examineEmpty($this->post['num']));


            //验证参数
            {

                if ($acceptUserId < 1 || $acceptUserId < 1 || $num < 1 || empty($itemType)) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }

                if (!in_array($type, [1, 2])) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }

                $acceptUserObj   = FivepkAccount::findOne($acceptUserId);//用于玩家是否存在
                $acceptPlayerObj = FivepkPlayerInfo::findOne($acceptUserId);//用于是否在线

                if (empty($acceptUserObj) || empty($acceptPlayerObj)) {
                    throw new MyException(ErrorCode::ERROR_USER_NOT_EXIST);
                }
                if ($acceptPlayerObj->is_online == 1) {
                    throw new MyException(ErrorCode::ERROR_USER_IS_GAMING);
                }
            }
            $SotreStoragePackItems = new \common\models\pay\SotreStoragePackItems();
            $item_count            = $SotreStoragePackItems::find()->select('item_count')->where("account_id=:account_id and item_type=:item_type", array(':account_id' => $acceptUserId, ':item_type' => $itemType))->asArray()->one();

            if (!isset($item_count['item_count'])) {
                $item_count['item_count'] = 0;
            }
            $data      = [
                'accountId'   => $acceptUserId,
                'modifyType'  => $type == 1 ? 1 : -1,
                'modifyCount' => $num,
                'itemType'    => $itemType,
            ];
            $interface = $this->remoteInterface->UpdatePack($data);

            if (isset($interface['status']) && $interface['status'] == 10) {
                $BackendRecordModifyPackItems = new \common\models\record\BackendRecordModifyPackItems();
                $BackendRecordModifyPackItems->add([
                    'account_id'     => $acceptUserId,
                    'operator'       => $this->loginInfo['name'],
                    'before_operate' => $item_count['item_count'],
                    'up_coin'        => $type == 1 ? $num : 0,
                    'down_coin'      => $type == 2 ? $num : 0,
                    'after_operate'  => $type == 1 ? ($item_count['item_count'] + $num) : ($item_count['item_count'] - $num),
                    'operate_time'   => $this->time,
                    'belong_seoid'   => $acceptUserObj->seoid,
                    'item_type'      => $itemType,// store_item_list_data 的 item_type
                ]);
            }

            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  道具类型列表
     */
    public function actionGetStoreList()
    {
        $class  = new \common\models\pay\StoreItemListData();
        $potion = array(
            'select' => 'name,item_type'
        );
        $data   = $class->pageList(
            \backend\models\Tool::examineEmpty($this->get['pageNo'], 1)
            , Tool::examineEmpty($this->get['pageSize'], 8)
            , $potion
        );

        if (!Tool::isIssetEmpty($this->get['accountId'])) {
            $class     = new \common\models\pay\SotreStoragePackItems();
            $potion    = array(
                'select' => 'item_count,item_type',
                'where'  => 'account_id=:account_id',
                'pdo'    => array(':account_id' => intval($this->get['accountId']))
            );
            $dataCount = $class->pageList(
                \backend\models\Tool::examineEmpty($this->get['pageNo'], 1)
                , Tool::examineEmpty($this->get['pageSize'], 8)
                , $potion
            );

            foreach ($data as &$value) {
                $num = 0;
                foreach ($dataCount as $value2) {
                    $value['item_type'] = $value2['item_type'];
                    $num                = $value2['item_count'];
                }
                $value['item_count'] = $num;
            }
        }


        $this->setData($data);
        $this->sendJson();
    }

    /**
     *  道具开洗记录
     */
    public function actionModifyPackItemsRecord()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo       = $this->get['pageNo'];
            $pageSize     = $this->get['pageSize'];
            $account      = isset($this->get['account']) ? $this->get['account'] : Tool::examineEmpty($this->get['name']);
            $accountId    = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $promo_code   = isset($this->get['promo_code']) ? strtoupper($this->get['promo_code']) : "";
            $operator     = isset($this->get['operator']) ? $this->get['operator'] : "";
            $operatorType = isset($this->get['operatorType']) ? $this->get['operatorType'] : "";
            $stime        = isset($this->get['stime']) ? strtotime($this->get['stime']) : strtotime(date('Y-m-d', $this->time));
            $etime        = isset($this->get['etime']) ? (strtotime($this->get['etime']) + 86400) : (strtotime(date('Y-m-d', $this->time)) + 86400);

            $popCodeArr   = $this->Account->findAllSonPopCode($this->loginId);
            $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";
            $where        = " fivepk_account.seoid in ({$popCodeStrIn})";

            if (!empty($account)) {
                $where .= " and fivepk_account.name like '%{$account}%'";
            }
            if (!empty($accountId)) {
                $where .= " and fivepk_account.account_id = '{$accountId}'";
            }
            if (!empty($promo_code)) {
                $where .= " and fivepk_account.seoid = '{$promo_code}'";
            }
            if (!empty($operator)) {
                $where .= " and backend_record_modify_pack_items.operator like '%{$operator}%'";
            }
            if (!empty($operatorType)) {
                $where .= " and backend_record_modify_pack_items.operator_type = '$operatorType'";
            }
            if (!empty($stime)) {
                $where .= " and backend_record_modify_pack_items.operate_time > '{$stime}'";
            }
            if (!empty($etime)) {
                $where .= " and backend_record_modify_pack_items.operate_time <= '{$etime}'";
            }
            $data = $this->BackendRecordModifyPackItems->UserDiamondRecordPage($pageNo, $pageSize, $where);
            foreach ($data as &$val) {
                $val['name'] = '';
                if (isset($val['account']['name'])) {
                    $val['account']['name'] = Factory::Tool()->hideName($val['account']['name']);
                    $val['name']            = $val['account']['name'];
                }
                $val['operate_time'] = date('Y-m-d H:i:s', $val['operate_time']);
            }
            $account = $this->BackendRecordModifyPackItems->UserDiamondRecordCount($where);
            $page    = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
                'nowPage' => $pageNo
            );

            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

}

?>