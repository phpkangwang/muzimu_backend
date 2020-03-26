<?php

namespace common\models\game\base;

use backend\models\Tool;
use common\models\DataRoomInfoList;
use common\models\game\att2\Att;
use common\models\game\byu\Byu;
use common\models\game\firephoenix\Hfh;
use common\models\game\big_plate\Dzb;
use common\models\game\big_shark\Dbs;
use common\models\game\FivepkPlayerInfo;
use common\models\game\ghr\Ghr;
use common\models\game\hfhh\Hfhh;
use common\models\game\paman\Pam;
use common\models\game\sbb\Sbb;
use common\models\game\snow_leopard\Bao;
use common\models\game\star97\Mxj;
use common\models\OddsChangePath;
use common\models\RecordDiamond;
use common\models\RecordHits;
use Yii;
use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use common\models\FivepkPrizeType;


//所有游戏公共类
class GameBase extends BaseModel
{
##################################   基础配置   #######################################
    /**
     *   新玩家机率类型
     */
    const DEFAULT_ODDS_NEW_PLAYER = 1;

    /**
     *   机台玩家几率类型
     */
    const DEFAULT_ODDS_MACHINE = 2;

    /**
     *  老玩家机率类型
     */
    const DEFAULT_ODDS_old_PLAYER = 3;

    /**
     * @var 游戏类型
     */
    public $gameType;

    /**
     * @var 游戏简称
     */
    public $gameName;

    /**
     * @var 游戏中文名称
     */
    public $chineseGameName;


####################################   表配置   ######################################
    /**
     * @var 轨迹 天表
     */
    public $tableLocusDay;

    /**
     * @var 轨迹月表
     */
    public $tableLocusMonth;

    /**
     * @var 轨迹统计表
     */
    public $tablePrizeDay;

    /**
     * @var 比备表
     */
    public $tableCompare;

    /**
     * @var 机台表
     */
    public $tableMachine;

    /**
     * @var 房间表
     */
    public $tableRoom;

    ##################################   对象   ##############################
    /**
     * @var 轨迹日表对象
     */
    public $modelLocusDay;

    /**
     * @var prizeday表对象
     */
    public $modelPrizeDay;

    /**
     * @var 房间表对象
     */
    public $modelRoom;

    /**
     * @var 用户机率对象
     */
    public $modelUserOdds;

    /**
     * @var 用户默认机率对象
     */
    public $modelDefaultUserOdds;

    /**
     * @var 机台表对象
     */
    public $modelMachine;

    /**
     * @var 默认机台机率对象
     */
    public $modelDefaultOdds;


    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    public function __call($name, $arguments)
    {
        return '';
    }

    /**
     *  获取人气报表实例
     * @return RecordHits
     */
    public function getModelHitsReport()
    {
        return new RecordHits();
    }
    /**
     *
     */
    /**
     * 获取各个游戏的实体类
     * @param $gameName
     * @return AttController|BaoController|DbsController|DzbController|GhrController|HfhController|MxjController|SbbController|string
     */
    public function initGameObj($gameName)
    {
        try {
            switch ($gameName) {
                case "HFH":
                    return new Hfh();
                case "DZB":
                    return new Dzb();
                case "DBS":
                    return new Dbs();
                case "ATT2":
                    return new Att();
                case "MXJ":
                    return new Mxj();
                case "SBB":
                    return new Sbb();
                case "PAM":
                    return new Pam();
                case "BAO":
                    return new Bao();
                case "GHR":
                    return new Ghr();
                case "BYU":
                    return new Byu();
                case "HFHH":
                    return new Hfhh();
                default:
                    throw new MyException(ErrorCode::ERROR_GAME);
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 判断关联那几张表
     * @param stime  筛选条件的开始时间
     * @return string
     */
    public function unionTable($stime)
    {
        $tool = new Tool();
        $rs   = $tool->DayWeekMonth($stime);
        switch ($rs) {
            case "day":
                $table = " backend_locus_" . strtolower($this->gameName) . "_day";
                break;
            case "week":
                $table = " backend_locus_" . strtolower($this->gameName) . "_month";
                break;
            case  "month":
                $table = " backend_locus_" . strtolower($this->gameName) . "_month";
                break;
            default:
                $table = " backend_locus_" . strtolower($this->gameName) . "_day";
        }
        return $table;
    }

    /**
     *  新轨迹合并将
     * @param $fromJson
     * @param $prizeJson
     * @return false|string
     */
    public function mergePrizeJson($fromJson, $prizeJson)
    {
        $rsArr = array();

        if (!empty($fromJson)) {
            $fromArr = json_decode($fromJson, true);
            foreach ($fromArr as $key => $val) {
                $rsArr[$key] = isset($rsArr[$key]) ? $rsArr[$key] : 0;
                $rsArr[$key] += $val;
            }
        }
        if (!empty($prizeJson)) {
            $prizeJsonArr = json_decode($prizeJson, true);
            foreach ($prizeJsonArr as $key => $val) {
                $rsArr[$key] = isset($rsArr[$key]) ? $rsArr[$key] : 0;
                $rsArr[$key] += $val;
            }
        }
        return json_encode($rsArr);
    }

    /**
     * 获取各个游戏的奖
     * @param $gameType
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public function getPrizeTypeList($gameType)
    {
        $FivepkPrizeType = new FivepkPrizeType();
        return $FivepkPrizeType->getPrizeTypeList($gameType);
    }

    /**
     *   获取房间场次
     */
    public function getRoomList()
    {
        $DataRoomInfoList = new DataRoomInfoList();
        return $DataRoomInfoList->tableList($this->gameType);
    }

    public function getRoom()
    {
        $DataRoomInfoList = new DataRoomInfoList();
        return $DataRoomInfoList->findByGame($this->gameType);
    }


    /**
     *  红包来袭 报表统计  刷新这个游戏这一天的  红包来袭  统计
     * @param $day
     * @return bool
     */
    public function reportDiamond($day)
    {
        $stime     = strtotime($day);
        $etime     = strtotime($day . " 23:59:59");
        $gameType  = $this->gameType;
        $tableName = $this->unionTable($stime);
        $prizeName = Yii::$app->params['activityName'];
        //获取送钻是由哪个奖发的
        $sqlPrize  = "
            select * from data_prize_type where game_type = '{$gameType}' and prize_name like '%{$prizeName}%'
        ";
        $PrizeObjs = Yii::$app->game_db->createCommand($sqlPrize)->queryAll();
        if (empty($PrizeObjs)) {
            //如果没有红包来袭则直接跳过
            return true;
        }
        $prizeIdArr = array_column($PrizeObjs, 'id');
        //查一共有多少个人中奖，和总中奖数量
        $inStr        = "'" . implode("','", $prizeIdArr) . "'";
        $locusSql     = "
             select account_id,sum(prize_award_num) as awardSum from {$tableName} where prize_id in ({$inStr}) AND create_time >= '{$stime}' AND create_time <= '{$etime}' group by account_id
        ";
        $LocusObjs    = Yii::$app->game_db->createCommand($locusSql)->queryAll();
        $accountIdArr = array();
        $awardSum     = 0; //奖励总数
        foreach ($LocusObjs as $val) {
            array_push($accountIdArr, $val['account_id']);
            $awardSum += $val['awardSum'];
        }
        //奖励获得总人数
        $peopleSum = count(array_unique($accountIdArr));
        //查每个房间级别，每个奖的类型有多少个奖，和中奖数量
        $countSql  = "
             select room_index,prize_award_type,sum(prize_award_num) as awardSum,count(*) as awardTimes from {$tableName} where prize_id in ({$inStr}) AND create_time >= '{$stime}' AND create_time <= '{$etime}' group by room_index,prize_award_type
        ";
        $countObjs = Yii::$app->game_db->createCommand($countSql)->queryAll();
        //插入送钻报表数据库
        $RecordDiamondObj = new RecordDiamond();
        $RecordDiamondObj->deleteByDay($day, $gameType);
        //计算单个场次的
        foreach ($countObjs as $countObj) {
            $postData = array(
                'game_type'         => $gameType,
                'room_index'        => $countObj['room_index'],
                'prize_award_type'  => $countObj['prize_award_type'],
                'prize_award_num'   => $countObj['awardSum'],
                'prize_award_times' => $countObj['awardTimes'],
                'award_sum'         => $awardSum,
                'people_sum'        => $peopleSum,
                'create_time'       => $day,
            );
            $RecordDiamondObj->add($postData);
        }
        return true;
    }


    /**
     * 获取机率 页面
     * @param $param
     * @return array
     */
    public function oddsIndex($param)
    {
        $seoModel  = $this->getModelMachine();
        $Tool      = new Tool();
        $time      = time();
        $level     = $param['level'];
        $machine   = $param['machine'];
        $accountId = $param['accountId'];
        $status    = $param['status'];

        $seo_machine_type = null;
        $machine_auto     = null;
        switch ($status) {
            case 1:
                $seo_machine_type = 0;
                break;
            case 2:
                $seo_machine_type = 1;
                break;
            case 3:
                $seo_machine_type = 2;
                break;
            case 4:
                $seo_machine_type = 1;
                $machine_auto     = 1;
                break;
        }
        $models = $seoModel::find()->joinWith('roomList')
            ->where(['data_room_info_list.room_index' => $level])
            ->andFilterWhere(['like', $seoModel::tableName() . '.seo_machine_id', strtoupper($machine)])
            ->andFilterWhere([$seoModel::tableName() . '.account_id' => $accountId])
            ->andFilterWhere(['seo_machine_type' => $seo_machine_type])
            ->andFilterWhere(['machine_auto' => $machine_auto])
            ->andFilterWhere(['status' => 1])
            ->asArray()
            ->all();

        //获取所有的用户id
        $accountIds          = array_column($models, 'account_id');
        $accountIds          = array_unique($accountIds);
        $FivepkPlayerInfoObj = new FivepkPlayerInfo();
        $accountObjs         = $FivepkPlayerInfoObj->finds($accountIds);
        $newAccountObjs      = array();
        foreach ($accountObjs as $v) {
            $newAccountObjs[$v['account_id']] = $v;
        }

        $total = [
            '在线' => 0,
            '留机' => 0
        ];

        foreach ($models as $key => $val) {
            unset($models[$key]['roomList']);
            $models[$key]['status'] = $seoModel->findStatus($val);

            if (isset($val['account_id'])) {
                $models[$key]             = $Tool->clearFloatZero($models[$key]);
                $models[$key]['nickName'] = isset($newAccountObjs[$val['account_id']]['nick_name']) ? $newAccountObjs[$val['account_id']]['nick_name'] : "";
            }
            if (isset($val['seo_machine_type'])) {
                if ($val['seo_machine_type'] == 1) {
                    $total['在线'] += 1;
                } elseif ($val['seo_machine_type'] == 2) {
                    $total['留机'] += 1;
                }
            }

            if (isset($val['reservation_date'])) {
                $models[$key]['reservationStatus'] = $val['reservation_date'] > date($Tool::DATE_USUALLY_FORMAT, $time) ? 1 : 0;
            }
        }

        $data = array(
            'models'             => $models,
            'totalMachineStatus' => $total,
        );
        return $data;
    }

    /**
     *   默认机率
     */
    public function defaultOdds()
    {
        $DataRoomInfoList = new DataRoomInfoList();
        $room_info_lists  = $DataRoomInfoList->findByGame($this->gameType);
        $defaultOdds      = $this->getModelDefaultOdds();
        $models           = $defaultOdds->find()->asArray()->all();
        foreach ($models as $key => $model) {
            $models[$key] = $this->Tool->clearFloatZero($models[$key]);
            foreach ($room_info_lists as $room_info_list) {
                if ($model['room_info_list_id'] == $room_info_list['id']) {
                    $models[$key]['roomName'] = $room_info_list['name'];
                }
            }
        }
        return $models;
    }

    //prize 排序
    public static function prizeSort(&$resultSum, &$prizeList)
    {
        if (isset($resultSum['prize']) && !empty($resultSum['prize'])) {
            $sorArr             = array_column($prizeList, 'prize_name', 'prize_name');
            $resultSum['prize'] = Tool::MySort($resultSum['prize'], $sorArr);
        }
    }

    /**
     * 修改机台轨迹数据
     * @param $autoIds
     * @param $data
     * @return int
     */
    public function updateByAutoIds($autoIds, $data)
    {
        //获取所有的机台信息
        $objs = $this->findsByAutoIds($autoIds);
        foreach ($objs as $val) {
            $arr = Tool::distinctArr($data, $val, $this::attributeLabels());
            if (!empty($arr)) {
                $OddsChangePathModel = new OddsChangePath();
                $postData            = array(
                    'game_type' => $this->gameType,
                    'type'      => $OddsChangePathModel->typeMachine,
                    'type_id'   => $val['seo_machine_id'],
                    'content'   => json_encode($arr, JSON_UNESCAPED_UNICODE),
                );
                $OddsChangePathModel->add($postData);
            }
        }

        return self::updateAll($data, ['in', 'auto_id', $autoIds]);
    }


    /**
     * 玩家记录
     * @param $params
     * @return array|mixed
     */
    public function PlayerRecord($params)
    {

        $gameType        = $params['gameType'];
        $popCodeArr      = $params['popCodeArr'];
        $accountId       = $params['accountId'];
        $oddsType        = $params['oddsType'];
        $stime           = $params['stime'];
        $etime           = $params['etime'];
        $sort            = $params['sort'];
        $sortType        = $params['sortType'];
        $isTy            = $params['isTy'];
        $playerCountOpen = $params['playerCountOpen'];
        $showTime        = $params['showTime'];
        //获取所有的奖
        $prizeList    = $this->getPrizeTypeList($gameType);
        $newPrizeList = array_column($prizeList, 'prize_name', 'id');

        $inStr = "'" . implode("','", $popCodeArr) . "'";
        $where = " prize.account_id <> 0 and account.seoid in ({$inStr})";

        if ($isTy) {
            $where .= " and prize.room_index = 1";
        } else {
            $where .= " and prize.room_index <> 1";
        }

        if ($accountId != "") {
            $where .= " and prize.account_id= '{$accountId}'";
        }

        if ($oddsType != "") {
            $where .= " and prize.odds_type= '{$oddsType}'";
        }

        if (!empty($stime) && !empty($etime)) {
            $where .= " AND prize.create_time >= '{$stime}' AND  prize.create_time <= '{$etime}'";
        }


        $sql          = "
              select prize.*,playerInfo.nick_name,account.seoid from 
              {$this->tablePrizeDay} as prize
              left join fivepk_account as account on prize.account_id = account.account_id
              left join fivepk_player_info as playerInfo on playerInfo.account_id = account.account_id
              where {$where}
        ";
        $prizeDayObjs = Yii::$app->game_db->createCommand($sql)->queryAll();


        $result    = array();
        $resultSum = array();

        $field = 'account_id';
        if ($showTime) {
            $field = 'create_time';
        }
        //先把一个玩家当天的数据都合并成一条数据
        foreach ($prizeDayObjs as $val) {
            $result[$val[$field]]['account_id'] = $val['account_id'];
            $result[$val[$field]]['nickName']   = $val['nick_name'];
            $result[$val[$field]]['seoid']      = $val['seoid'];
            if ($showTime) {
                $result[$val[$field]]['create_time'] = $val['create_time'];
            }
            $this->prizeObjToMerge($result[$val[$field]], $val);
            $this->prizeObjToMerge($resultSum[0], $val);//因为只支持二维数组所以加一个下标
        }

        //计算每个玩家的各种几率
        foreach ($result as $key => $val) {
            $this->prizeObjToChinese($result[$val[$field]], $val, $newPrizeList);
        }
        //计算每个玩家的各种几率
        foreach ($resultSum as $key => $val) {
            $this->prizeObjToChinese($resultSum[$key], $val, $newPrizeList);
        }

        $result    = $this->dataCount($result);
        $resultSum = $this->dataCount($resultSum);
        $result    = array_values($result);
        $sortInArr = ["盈利", "总玩局数"];
        $Tool      = new Tool();
        if (in_array($sort, $sortInArr)) {
            $result = $Tool->ArrSort($result, $sort, $sortType);
        }

        if (isset($resultSum[0]['prize']) && !empty($resultSum[0]['prize'])) {
            $sorArr                = array_column($prizeList, 'prize_name', 'prize_name');
            $resultSum[0]['prize'] = Tool::MySort($resultSum[0]['prize'], $sorArr);
        }

        if ($playerCountOpen) {
            Tool::issetInitValue($resultSum[0], array());
            return array('result' => $result, 'resultSum' => $resultSum[0]);
        }
        return $result;
    }

    /**
     * 查询单个
     * @param $isDefault
     * @param $oddsType
     * @param $oddsTypeId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function oddsInfo($isDefault, $oddsType, $oddsTypeId)
    {
        $query = self::find()->where(['=', 'is_default', $isDefault]);
        if ($oddsType != "") {
            $query->andWhere(['=', 'odds_type', $oddsType]);
        }
        //只有查询玩家几率的时候才需要这个值
        if ($oddsTypeId != "") {
            $query->andWhere(['=', 'odds_type_id', $oddsTypeId]);
        }
        $data = $query->asArray()->one();
        return $data;
    }
}
