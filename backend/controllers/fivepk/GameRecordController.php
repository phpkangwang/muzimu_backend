<?php

namespace backend\controllers\fivepk;

use backend\models\services\ConfigService;
use backend\models\Tool;
use common\models\FivepkPrizeType;
use common\models\game\att2\FivepkDefaultAtt2;
use common\models\game\base\GameBase;
use common\models\game\firephoenix\FivepkFourOfAKindJaRate;
use common\models\game\FivepkPlayerInfo;
use common\models\game\star97\Star97Service;
use common\models\HitsReport;
use common\models\OddsChangePath;
use common\services\HitsReportService;
use Yii;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;

/**
 * 游戏记录
 * Class OddsController
 * @package backend\controllers
 */
class GameRecordController extends MyController
{

    /**
     *  房间轨迹和机台轨迹
     */
    public function actionOddsPath()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['type']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->get['gameName'];
            $type     = $this->get['type'];
            $pageSize = $this->get['pageSize'];
            $adminId = isset($this->get['adminId']) ? $this->get['adminId'] : "";
            $lastId   = isset($this->get['lastId']) ? $this->get['lastId'] : "";
            $stime    = $this->get['stime'];
            $etime    = $this->get['etime']." 23:59:59";

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $gameType     = $GameObj->gameType;
            $params = [
                'gameType' => $gameType,
                'type'     => $type,
                'lastId'   => $lastId,
                'pageSize' => $pageSize,
                'stime'    => $stime,
                'etime'    => $etime,
                'adminId' => $adminId,
            ];

            $OddsChangePathModel = new OddsChangePath();
            $data                = $OddsChangePathModel->LocusPage($params);

            if (!empty($data)) {
                $endData = end($data);
                $lastId  = isset($endData['id']) ? $endData['id'] : 0;
            }
            $this->setData(array(
                'list'   => $data,
                'lastId' => $lastId,
            ));
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * @desc 游戏轨迹和大奖记录
     */
    public function actionGameRecord()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['bigAward']) || !isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName      = $this->get['gameName'];
            $pageSize      = $this->get['pageSize'];
            $accountId     = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $machineId     = isset($this->get['machine']) ? $this->get['machine'] : "";
            $prizeId       = isset($this->get['prizeId']) ? $this->get['prizeId'] : "";//中奖类型
            $prizeOutId    = isset($this->get['prizeOutId']) ? $this->get['prizeOutId'] : "";//出奖类型
            $prizeItem     = isset($this->get['prizeItem']) ? $this->get['prizeItem'] : "";//明星97奖项
            $jpPrizeId     = isset($this->get['jpPrizeId']) ? $this->get['jpPrizeId'] : "";//jp奖id
            $plusCardsType = isset($this->get['plusCardsType']) ? $this->get['plusCardsType'] : "";//超级大亨加买类型
            $stime         = Tool::startTimeHandler($this->get['stime'], $this->time);
            $etime         = Tool::endTimeHandler($this->get['etime'], $this->time);
            $bigAward      = $this->get['bigAward'] == 2 ? 2 : 1;//查询所有的记录（包括大奖和不是大奖）
            $roomIndex     = isset($this->get['roomIndex']) ? $this->get['roomIndex'] : "";
            $isKill        = isset($this->get['isKill']) ? $this->get['isKill'] : "";//是否击杀，捕鱼专用
            $oddsType      = isset($this->get['oddsType']) ? $this->get['oddsType'] : "";
            $lastId        = isset($this->get['lastId']) ? $this->get['lastId'] : "";

            $loginId    = $this->loginId;
            $popCodeArr = $this->Account->findAllSonPopCode($loginId);

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);

            //获取所有的奖列表
            $newPrizeList = array();
            $gameType     = $GameObj->gameType;
            $prizeList    = $GameObj->getPrizeTypeList($gameType);
            foreach ($prizeList as $val) {
                $newPrizeList[$val['id']] = $val;
            }

            //获取所有的机台
            $newMachineList = array();
            $seoModelObj    = $GameObj->getModelMachine();
            $MachineList    = $seoModelObj->tableList();
            foreach ($MachineList as $val) {
                $newMachineList[$val['auto_id']] = $val;
            }

            $params        = [
                'accountId'      => $accountId,
                'machineId'      => $machineId,
                'prizeItem'      => $prizeItem,
                'prizeId'        => $prizeId,
                'stime'          => $stime,
                'etime'          => $etime,
                'gameType'       => $gameType,
                'jpPrizeId'      => $jpPrizeId,
                'plusCardsType'  => $plusCardsType,
                'popCodeArr'     => $popCodeArr,
                'prizeOutId'     => $prizeOutId,
                'pageSize'       => $pageSize,
                'bigAward'       => $bigAward,
                'lastId'         => $lastId,
                'newPrizeList'   => $newPrizeList,
                'newMachineList' => $newMachineList,
                'roomIndex'      => $roomIndex,
                'isKill'         => $isKill,
                'oddsType'       => $oddsType,
            ];
            $locusModelObj = $GameObj->getModelLocusDay();
            $data          = $locusModelObj->LocusPage($params, $gameName);
            if (!empty($data)) {
                $endData = end($data);
                $lastId  = isset($endData['id']) ? $endData['id'] : 0;
            }
            $this->setData(array(
                'list'   => $data,
                'lastId' => $lastId,
            ));
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  大奖记录统计
     */
    public function actionAwardsCount()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['stime']) || !isset($this->get['etime'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName      = $this->get['gameName'];
            $stime         = Tool::startTimeHandler($this->get['stime'], $this->time);
            $etime         = Tool::endTimeHandler($this->get['etime'], $this->time);
            $accountId     = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $machineId     = isset($this->get['machine']) ? $this->get['machine'] : "";
            $prizeId       = isset($this->get['prizeId']) ? $this->get['prizeId'] : "";//中奖类型
            $prizeOutId    = isset($this->get['prizeOutId']) ? $this->get['prizeOutId'] : "";//出奖类型
            $prizeItem     = isset($this->get['prizeItem']) ? $this->get['prizeItem'] : "";//明星97奖项
            $jpPrizeId     = isset($this->get['jpPrizeId']) ? $this->get['jpPrizeId'] : "";//jp奖id
            $plusCardsType = isset($this->get['plusCardsType']) ? $this->get['plusCardsType'] : "";//超级大亨加买类型
            $bigAward      = isset($this->get['bigAward']) ? $this->get['bigAward'] : 1;
            $bigAward      = $bigAward == 2 ? 2 : 1;//查询所有的记录（包括大奖和不是大奖）
            $oddsType      = isset($this->get['oddsType']) ? $this->get['oddsType'] : "";
            $roomIndex     = isset($this->get['roomIndex']) ? $this->get['roomIndex'] : "";

            $loginId    = $this->loginId;
            $popCodeArr = $this->Account->findAllSonPopCode($loginId);

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);

            //获取所有的奖列表
            $gameType     = $GameObj->gameType;
            $prizeList    = $GameObj->getPrizeTypeList($gameType);
            $newPrizeList = array();
            foreach ($prizeList as $val) {
                $newPrizeList[$val['id']] = $val;
            }

            $params        = [
                'gameName'        => $gameName,
                'stime'           => $stime,
                'etime'           => $etime,
                'accountId'       => $accountId,
                'machineId'       => $machineId,
                'prizeItem'       => $prizeItem,
                'prizeId'         => $prizeId,
                'gameType'        => $gameType,
                'jpPrizeId'       => $jpPrizeId,
                'plus_cards_type' => $plusCardsType,
                'popCodeArr'      => $popCodeArr,
                'prizeOutId'      => $prizeOutId,
                'bigAward'        => $bigAward,
                'oddsType'        => $oddsType,
                'roomIndex'       => $roomIndex,
                'newPrizeList'    => $newPrizeList
            ];
            $locusModelObj = $GameObj->getModelLocusDay();
            $data          = $locusModelObj->PrizeCount($params);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionGetCompare()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['locusId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $locusId  = $this->get['locusId'];
            $gameName = $this->get['gameName'];

            $GameBaseObj     = new GameBase();
            $GameObj         = $GameBaseObj->initGameObj($gameName);
            $compareModelObj = $GameObj->getModelCompare();
            $data            = $compareModelObj->findByLocusIds([$locusId]);
            foreach ($data as $key => $val) {
                $data[$key]['big_small']   = $compareModelObj->getbig_small($val['big_small']);
                $data[$key]['compare_bet'] = $compareModelObj->getCompare_bet($val['compare_bet']);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  玩家记录
     */
    public function actionPlayerRecord()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['stime']) || !isset($this->get['etime'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName        = $this->get['gameName'];
            $stime           = $this->get['stime'];
            $etime           = $this->get['etime'] . " 23:59:59";
            $accountId       = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $sort            = isset($this->get['sort']) ? $this->get['sort'] : "account_id";//默认按照用户id排序
            $sortType        = isset($this->get['sortType']) ? strtolower($this->get['sortType']) : "asc";
            $isTy            = false;
            $playerCountOpen = isset($this->get['playerCountOpen']) ? $this->get['playerCountOpen'] : "";
            $showTime        = isset($this->get['showTime']) ? $this->get['showTime'] : "";
            $oddsType        = isset($this->get['oddsType']) ? $this->get['oddsType'] : "";
            $popCode         = isset($this->get['popCode']) ? $this->get['popCode'] : "";

            $loginId    = $this->loginId;
            $popCodeArr = $this->Account->findAllSonPopCode($loginId);

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $gameType    = $GameObj->gameType;


            $params     = array(
                'gameName'        => $gameName,
                'gameType'        => $gameType,
                'popCodeArr'      => $popCodeArr,
                'accountId'       => $accountId,
                'stime'           => $stime,
                'etime'           => $etime,
                'sort'            => $sort,
                'sortType'        => $sortType,
                'isTy'            => $isTy,
                'playerCountOpen' => $playerCountOpen,
                'showTime'        => $showTime,
                'oddsType'        => $oddsType,
                'popCode'         => $popCode
            );
            $prizeModel = $GameObj->getModelPrizeDay();
            $results    = $prizeModel->PlayerRecord($params);

            $this->setData($results);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * @desc 机台记录
     * @return string
     */
    public function actionMachineRecord()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['stime']) || !isset($this->get['etime'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName      = $this->get['gameName'];
            $machineAutoId = isset($this->get['machineId']) ? $this->get['machineId'] : "";
            $oddsType      = isset($this->get['oddsType']) ? $this->get['oddsType'] : "";
            $roomIndex     = isset($this->get['roomIndex']) ? $this->get['roomIndex'] : "";
            $stime         = $this->get['stime'];
            $etime         = $this->get['etime'] . " 23:59:59";
            $isTy          = isset($this->get['isTy']) ? true : false;
            $sort          = isset($this->get['sort']) ? $this->get['sort'] : "";
            $sortType      = isset($this->get['sortType']) ? strtolower($this->get['sortType']) : "desc";

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $gameType    = $GameObj->gameType;

            $params     = array(
                'gameType'      => $gameType,
                'machineAutoId' => $machineAutoId,
                'roomIndex'     => $roomIndex,
                'oddsType'      => $oddsType,
                'stime'         => $stime,
                'etime'         => $etime,
                'sort'          => $sort,
                'sortType'      => $sortType,
                'isTy'          => $isTy
            );
            $prizeModel = $GameObj->getModelPrizeDay();
            $results    = $prizeModel->MachineRecord($params);
            $this->setData($results);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   获取这个玩家从注册开始的所有信息
     */
    public function actionPlayerRecordNever()
    {
        Tool::checkParam(['gameName', 'accountId'], $this->get);
        $gameName  = $this->get['gameName'];
        $accountId = $this->get['accountId'];

        $GameBaseObj      = new GameBase();
        $GameObj          = $GameBaseObj->initGameObj($gameName);
        $prizeDayModelObj = $GameObj->getModelPrizeDay();
        $data             = $prizeDayModelObj->PlayerRecordNever($accountId);

        //获取用户昵称
        $FivepkPlayerInfoModel = new FivepkPlayerInfo();
        $FivepkPlayerInfoObj   = $FivepkPlayerInfoModel->findBase($accountId);
        $data['昵称']            = $FivepkPlayerInfoObj['nick_name'];
        if (isset($data['prize'])) {
            $data = array_merge($data, $data['prize']);
        }
        $this->setData([$data]);
        $this->sendJson();
    }
}

?>