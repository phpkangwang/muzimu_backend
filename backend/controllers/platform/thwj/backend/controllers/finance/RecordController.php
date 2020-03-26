<?php

namespace backend\controllers\platform\thwj\backend\controllers\finance;


use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\Factory;
use backend\models\MyException;
use backend\models\Tool;
use common\models\ContributionReport;
use common\models\game\FivepkAccount;
use common\models\record\ExperienceGiveReport;
use Yii;
use common\models\ExperienceReport;
use common\models\game\FivepkPlayerInfo;
use yii\db\Expression;

class RecordController extends BaseModel
{
    /**
     * 引入特质类 主要用到__call
     */
    use \backend\controllers\platform\PlatformTrait;


    /**
     * 玩家加减钻石
     * @param $post
     * @return bool
     * @throws \yii\db\Exception
     */
    public function UserDiamondUpdate($post)
    {
        try {
            //如果接受钻石的人不存在或者数量不存在
            if (!isset($post['sendPopCode']) || !isset($post['acceptUserId']) || !isset($post['num'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $sendPopCode  = $post['sendPopCode'];
            $acceptUserId = $post['acceptUserId'];
            $num          = $post['num'];
            $operatorType = isset($post['operatorType']) ? $post['operatorType'] : 1;
            $type         = $this->getUserDiamondUpdateType($post);


            //获取能够使用最上级的钻石  例如代理商能使用总代理的钻石，代理商给玩家开洗分用的是总代理的钻石
            $AccountObj    = $this->Account->findByPopCode($sendPopCode);
            $useDiamondId  = $this->Account->getUseParentDiamond($AccountObj->id);
            $useDiamondObj = $AccountObj = $this->Account->findBase($useDiamondId);
            $sendPopCode   = $useDiamondObj['pop_code'];

            $upDiamondTimes   = Yii::$app->params['upDiamondTimes'];//上钻倍数
            $downDiamondTimes = Yii::$app->params['downDiamondTimes'];//下钻倍数
            if (abs($num) < abs($upDiamondTimes)) {
                throw new MyException(ErrorCode::ERROR_DIAMOND_NUM);
            }
            if ($num > 0) {
                if ($num % $upDiamondTimes != 0) {
                    throw new MyException(ErrorCode::ERROR_DIAMOND_NUM);
                }
            } elseif ($num < 0) {
                if ($num % $downDiamondTimes != 0) {
                    throw new MyException(ErrorCode::ERROR_DIAMOND_NUM);
                }
            } else {
                throw new MyException(ErrorCode::ERROR_DIAMOND_NUM);
            }
            //开启事务
            $tr = Yii::$app->db->beginTransaction();

            $acceptUserObj   = FivepkAccount::findOne($acceptUserId);
            $acceptPlayerObj = FivepkPlayerInfo::findOne($acceptUserId);

            //玩家在fg游戏的时候不能开洗分
//            if($acceptPlayerObj['fg_game_number'] >= 100){
//                throw new MyException(ErrorCode::ERROR_FG_GAMING);
//            }

            //玩家是否存在
            if (empty($acceptUserObj) || empty($acceptPlayerObj)) {
                throw new MyException(ErrorCode::ERROR_USER_NOT_EXIST);
            }

            //下钻的时候玩家必须不在线
            if ($acceptPlayerObj->is_online == 1 && $num < 0) {
                throw new MyException(ErrorCode::ERROR_DIAMOND_IS_ONLINE);
            }

            $sendPopCodeAccountObj = $this->Account->findByPopCode($sendPopCode);
            if (empty($sendPopCodeAccountObj)) {
                throw new MyException(ErrorCode::ERROR_POP_CODE);
            }


            if ($type != 2) {
                //查看上级的钻石
                $FivepkSeoidDiamondSendObj = $this->FivepkSeoidDiamond->findByPopCode($sendPopCode);
                if (empty($FivepkSeoidDiamondSendObj)) {
                    $FivepkSeoidDiamondSendObjDiamond = 0;
                } else {
                    $FivepkSeoidDiamondSendObjDiamond = $FivepkSeoidDiamondSendObj->diamond;
                }
                //上级的钻石是否足够
                $FivepkSeoidDiamondSendData['diamond'] = $FivepkSeoidDiamondSendObjDiamond - $num;
                if ($FivepkSeoidDiamondSendData['diamond'] < 0) {
                    throw new MyException(ErrorCode::ERROR_DIAMOND_NUM_MINUS_DL);
                }
            }

            //玩家的钻石是否足够
            if ($acceptPlayerObj->coin + $num < 0) {
                throw new MyException(ErrorCode::ERROR_DIAMOND_NUM_MINUS);
            }


            $data = [
                'accountId'    => $acceptUserId,
                'type'         => $type,
                'operator'     => $post['operateName'],
                'point'        => abs($num),
                'seoid'        => $sendPopCode,
                'operatorType' => $operatorType//1手动操作 2充值 3转出中 4转出成功 5转出退还
            ];
            $this->remoteInterface->fraction($data);
            $tr->commit();
            return true;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    //获取加减钻接口的type
    public static function getUserDiamondUpdateType($post)
    {
        //0减钻  1是加钻 2 加钻但是不记录 不扣代理商的钻
        $type = '';
        if (isset($post['type']) && in_array($post['type'], [0, 1, 2])) {
            $type = $post['type'];
        } elseif (isset($post['num'])) {
            $type = $post['num'] > 0 ? 1 : 0;
        } else {
            try {
                throw new MyException(ErrorCode::ERROR_PARAM);
            } catch (MyException $e) {
                echo $e->toJson($e->getMessage());
            }
        }

        return $type;
    }


    /**
     * 用户营业额报表
     * @param $_this
     */
    public function actionUserReport($_this)
    {
        $popCode = isset($_this->get['popCode']) ? strtoupper($_this->get['popCode']) : "";
        $lookSon = isset($_this->get['lookSon']) ? strtoupper($_this->get['lookSon']) : "";
        $stime   = isset($_this->get['stime']) ? $_this->get['stime'] : date('Y-m-d', $_this->time);
        $etime   = isset($_this->get['etime']) ? $_this->get['etime'] . " 23:59:59" : date('Y-m-d 23:59:59', $_this->time);

        //没有推广码就代表查询当前登录账户的下级
        if (empty($lookSon)) {
            $parentAccountId = $_this->Account->findCanUsePopCodeAccount($_this->loginId);
        } else {
            $popCodeObj      = $_this->Account->findByPopCode($lookSon);
            $parentAccountId = $popCodeObj['id'];
        }
        //获取所有的
        $nameArr         = ['总代理', '代理商', '推广员'];
        $needAccountObjs = $_this->AccountRelation->findSonInRole($nameArr, $parentAccountId);
        //加入自己
        $parentAccountObj     = $_this->Account->findBase($parentAccountId);
        $parentAccountRoleObj = $_this->Role->findBase($parentAccountObj['role']);
        if (in_array($parentAccountRoleObj['name'], $nameArr)) {
            array_unshift($needAccountObjs, $parentAccountObj);
        }

        //只查看搜索的推广码
        if (!empty($popCode)) {
            foreach ($needAccountObjs as $key => $val) {
                if ($val['pop_code'] != $popCode) {
                    unset($needAccountObjs[$key]);
                }
            }
        }

        //计算总统计
        $statisticsSum = array(
            '操作笔数' => 0,
            '总上钻'  => 0,
            '总下钻'  => 0,
            '营业额'  => 0,
        );


        $first = true;

        foreach ($needAccountObjs as $key => &$accountObj) {
            $FivepkPoint            = new \common\models\game\FivepkPoint();
            $accountObj['roleInfo'] = $_this->Role->findBase($accountObj['role']);
            $hasSonArr              = ['总代理', '代理商'];
            if (in_array($accountObj['roleInfo']['name'], $hasSonArr) && (!$first)) {
                $accountObj['hasSon'] = "true";
            } else {
                $accountObj['hasSon'] = "false";
            }
            if ($first) {
                $popCodeArr = [$accountObj['pop_code']];
                $first      = false;
            } else {
                $popCodeArr = $this->Account->findAllSonPopCode($accountObj['id']);
            }
            $FivepkPoint->potion['link'] = function ($obj) use (&$stime, &$etime, &$popCodeArr) {
                $obj->select([new Expression('sum(up_coin) as up_coin,sum(down_coin) as down_coin, count(*) as count,operator_type,max(operate_time) as operate_time')]);
                $obj->andWhere(['in', 'belong_seoid', $popCodeArr]);
                $obj->andFilterWhere(['between', 'operate_time', $stime, $etime]);
                $obj->andWhere(['in', 'operator_type', [1, 2, 4]]);
                $obj->groupBy('operator_type');
            };
            $dataPoint                   = $FivepkPoint->pageData();

            //初始化
            {
                $accountObj['report'][0] = array(
                    'ID'   => $accountObj['id'],
                    '总上钻'  => 0,
                    '总下钻'  => 0,
                    '操作笔数' => 0,
                    '营业额'  => 0,
                );
                $accountObj['充值金额']      = 0;
                $accountObj['转出钻石']      = 0;
            }
            $accountObj['roleInfo'] = $_this->Role->findBase($accountObj['role']);
            foreach ($dataPoint as $point) {
                if ($point['operator_type'] == 1) {
                    $accountObj['report'][0]['总上钻'] += $point['up_coin'];
                    $accountObj['report'][0]['总下钻'] += $point['down_coin'];
                } elseif ($point['operator_type'] == 2) {
                    $accountObj['充值金额'] += $point['up_coin'];
                } elseif ($point['operator_type'] == 4) {
                    $accountObj['转出钻石'] += $point['down_coin'];
                }
                $accountObj['report'][0]['操作笔数'] += $point['count'];
                $accountObj['report'][0]['营业额']  = $accountObj['report'][0]['总上钻'] - $accountObj['report'][0]['总下钻'] + $accountObj['充值金额'] - $accountObj['转出钻石'];
            }
            $statisticsSum['总上钻']  = $statisticsSum['总上钻'] + $accountObj['report'][0]['总上钻'] + $accountObj['充值金额'];
            $statisticsSum['总下钻']  = $statisticsSum['总下钻'] + $accountObj['report'][0]['总下钻'] + $accountObj['转出钻石'];
            $statisticsSum['操作笔数'] += $accountObj['report'][0]['操作笔数'];
            $statisticsSum['营业额']  += $accountObj['report'][0]['营业额'];
        }


        $data = array(
            'list'          => $needAccountObjs,
            'statisticsSum' => $statisticsSum
        );
//        varDump($data);
        $_this->setData($data);
        $_this->sendJson();
    }

    /**
     * @desc 玩家营业额报表详情
     * @param $_this
     * @throws \yii\base\InvalidConfigException
     */
    public function actionPlayerReport($_this)
    {
        try {
            //如果接受钻石的人不存在或者数量不存在
            if (!isset($_this->get['popCode'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $popCode   = $_this->get['popCode'];
            $accountId = isset($_this->get['accountId']) ? $_this->get['accountId'] : "";
            $stime     = isset($_this->get['stime']) ? $_this->get['stime'] : date('Y-m-d', $_this->time);
            $etime     = isset($_this->get['etime']) ? $_this->get['etime'] . " 23:59:59" : date('Y-m-d 23:59:59', $_this->time);

//            $getPopCode=$_this->Account->findOneByField('pop_code',$popCode,false);
//            $popCodeArr = $this->Account->findAllSonPopCode($getPopCode['id']);
            $popCodeArr                  = [$popCode];
            $FivepkPoint                 = new \common\models\game\FivepkPoint();
            $FivepkPoint->potion['link'] = function ($obj) use (&$stime, &$etime, &$popCodeArr, &$accountId) {
                $obj->select([new Expression(
                    'fivepk_point.account_id as ID,sum(fivepk_point.up_coin) as 总上钻,sum(fivepk_point.down_coin) as 总下钻,fivepk_point.belong_seoid as 推广号,fivepk_player_info.nick_name as 玩家昵称,operator_type'
                )]);
                $obj->andWhere(['in', 'fivepk_point.belong_seoid', $popCodeArr]);
                $obj->andWhere(['=', 'fivepk_point.operator_type', 1]);
                $obj->andFilterWhere(['=', 'fivepk_point.account_id', $accountId]);
                $obj->andFilterWhere(['between', 'fivepk_point.operate_time', $stime, $etime]);
                $obj->groupBy('fivepk_point.account_id');
                $obj->leftJoin('fivepk_player_info', 'fivepk_player_info.account_id=fivepk_point.account_id');
                $obj->indexBy('ID');
            };

            //统计1手动操作
            $dataPoint = $FivepkPoint->pageData();

//            die();

            $FivepkPoint                 = new \common\models\game\FivepkPoint();
            $FivepkPoint->potion['link'] = function ($obj) use (&$stime, &$etime, &$popCodeArr, &$accountId) {
                $obj->select([new Expression(
                    'fivepk_point.account_id as ID,sum(fivepk_point.up_coin) as 总上钻,sum(fivepk_point.down_coin) as 总下钻,fivepk_point.belong_seoid as 推广号,fivepk_player_info.nick_name as 玩家昵称,operator_type'
                )]);
                $obj->andWhere(['in', 'fivepk_point.belong_seoid', $popCodeArr]);
                $obj->andWhere(['in', 'fivepk_point.operator_type', [2, 4]]);
                $obj->andFilterWhere(['=', 'fivepk_point.account_id', $accountId]);
                $obj->andFilterWhere(['between', 'fivepk_point.operate_time', $stime, $etime]);
                $obj->groupBy('fivepk_point.account_id');
                $obj->leftJoin('fivepk_player_info', 'fivepk_player_info.account_id=fivepk_point.account_id');
                $obj->indexBy('ID');
            };

            //统计2 充值金额 4 转出钻石
            $dataPoint2 = $FivepkPoint->pageData();


            $data = [];

            foreach ($dataPoint as $value) {
                $data[$value['ID']]['ID']   = $value['ID'];
                $data[$value['ID']]['总上钻']  = $value['总上钻'];
                $data[$value['ID']]['总下钻']  = $value['总下钻'];
                $data[$value['ID']]['推广号']  = $value['推广号'];
                $data[$value['ID']]['玩家昵称'] = $value['玩家昵称'];
                $data[$value['ID']]['充值金额'] = 0;
                $data[$value['ID']]['转出钻石'] = 0;
                $data[$value['ID']]['营业额']  = $value['总上钻'] - $value['总下钻'];
            }

            //统计2充值 4转出

            foreach ($dataPoint2 as $value) {

                if (!isset($data[$value['ID']])) {
                    $data[$value['ID']]['ID']   = $value['ID'];
                    $data[$value['ID']]['玩家昵称'] = $value['玩家昵称'];
                    $data[$value['ID']]['推广号']  = $value['推广号'];
                    $data[$value['ID']]['总上钻']  = 0;
                    $data[$value['ID']]['总下钻']  = 0;
                    $data[$value['ID']]['营业额']  = 0;
                }

                $data[$value['ID']]['充值金额'] = $value['总上钻'];
                $data[$value['ID']]['转出钻石'] = $value['总下钻'];

                $data[$value['ID']]['营业额'] = $data[$value['ID']]['营业额'] + $value['总上钻'] - $value['总下钻'];
            }


            $_this->setData($data);
            $_this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }


    /**
     * hfh用户营业额报表-贡献度
     * @param $_this
     * @throws MyException
     */
    public function actionUserReportList($_this)
    {
        if (Tool::isIssetEmpty($_this->get['stime'])
            || Tool::isIssetEmpty($_this->get['etime'])
        ) {
            throw new MyException(ErrorCode::ERROR_PARAM);
        }

        $stime = $_this->get['stime'];
        $etime = $_this->get['etime'];


        $time = date("Y-m-d", time());
        if (strtotime($stime) >= strtotime($time)) {
            $obj = new ContributionReport();
            $obj->RecordToday();
        }


        $popCode = isset($_this->get['popCode']) ? strtoupper($_this->get['popCode']) : "";
        $lookSon = isset($_this->get['lookSon']) ? strtoupper($_this->get['lookSon']) : "";

        //没有推广码就代表查询当前登录账户的下级
        if (empty($lookSon)) {
            $parentAccountId = $_this->Account->findCanUsePopCodeAccount($_this->loginId);
        } else {
            $popCodeObj      = $_this->Account->findByPopCode($lookSon);
            $parentAccountId = $popCodeObj['id'];
        }
        {
            //判断管理员
            $isManagement = false;
            $myRoleInfo   = $_this->Role->findBase($_this->loginInfo['role']);
            if (strstr($myRoleInfo['name'], '管理员')) {
                $isManagement = true;
            }
        }

        //获取所有的
        $nameArr         = ['总代理', '代理商', '推广员'];
        $needAccountObjs = $_this->AccountRelation->findSonInRole($nameArr, $parentAccountId);
        //加入自己
        $parentAccountObj     = $_this->Account->findBase($parentAccountId);
        $parentAccountRoleObj = $_this->Role->findBase($parentAccountObj['role']);
        if (in_array($parentAccountRoleObj['name'], $nameArr)) {
            array_unshift($needAccountObjs, $parentAccountObj);
        }

        //只查看搜索的推广码
        if (!empty($popCode)) {
            foreach ($needAccountObjs as $key => $val) {
                if ($val['pop_code'] != $popCode) {
                    unset($needAccountObjs[$key]);
                }
            }
        }


        //计算总统计
        $statisticsSum = array(
            '总营业额' => 0,
            '总分成'  => 0,
        );

        $ContributionReportClass = new \common\models\ContributionReport();
        $first                   = [];
        foreach ($needAccountObjs as $key => &$accountObj) {
            unset($accountObj['token']);
            $accountObj['roleInfo'] = $_this->Role->findBase($accountObj['role']);
            $hasSonArr              = ['总代理', '代理商'];
            if (in_array($accountObj['roleInfo']['name'], $hasSonArr) && !empty($first)) {
                $accountObj['hasSon'] = "true";
            } else {
                $accountObj['hasSon'] = "false";
            }

            if (empty($first)) {
                $popCodeArr = $accountObj['pop_code'];
                $first      = $accountObj;
            } else {
                $popCodeArr = $this->Account->findAllSonPopCode($accountObj['id']);
            }

            $num                            = $ContributionReportClass::find()
                ->select('sum(num) as num')
                ->where("create_time between :starttime and :endtime", [':starttime' => $stime, ':endtime' => $etime])
                ->andWhere(['in', 'pop_code', $popCodeArr])
                ->asArray()->one();
            $accountObj['report'][0]['营业额'] = Tool::examineEmpty($num['num'], 0);
            $accountObj['分成']               = $this->getDivideRs($first, $accountObj, $accountObj['report'][0]['营业额']);
            $accountObj['roleInfo']         = $_this->Role->findBase($accountObj['role']);
            $statisticsSum['总营业额']          += $accountObj['report'][0]['营业额'];
            $statisticsSum['总分成']           += $accountObj['分成'];
        }

        $data = array(
            'list'          => $needAccountObjs,
            'statisticsSum' => $statisticsSum,
        );
        $_this->setData($data);
        $_this->sendJson();
    }

    //获取分成结果
    public function getDivideRs($first, $rsObj, $money)
    {
        if ($first['pop_code'] == $rsObj['pop_code']) {
            return $money * $first['money_pre'] / 100;
        } else {
            return $money * ($first['money_pre'] - $rsObj['money_pre']) / 100;
        }
    }

    /**
     * @desc 玩家营业额报表详情
     * @param $_this
     */
    public function actionPlayerReportList($_this)
    {
        try {
            //如果接受钻石的人不存在或者数量不存在
            if (!isset($_this->get['popCode'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            if (Tool::isIssetEmpty($_this->get['stime'])
                || Tool::isIssetEmpty($_this->get['etime'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $stime   = $_this->get['stime'];
            $etime   = $_this->get['etime'];
            $popCode = $_this->get['popCode'];
//            $getPopCode=$_this->Account->findOneByField('pop_code',$popCode,false);
//            $popCodeArr = $this->Account->findAllSonPopCode($getPopCode['id']);
            $popCodeArr = [$popCode];

            $ContributionReportClass = new \common\models\ContributionReport();

            $popCodeData = $_this->Account->findOneByField('pop_code', $popCode, false);

            $money_pre = Tool::examineEmpty($popCodeData['money_pre'], 0);

            $ContributionReportClass->potion['link'] = function ($obj) use (&$stime, &$etime, &$popCodeArr) {
                $obj->select([new Expression('max(create_time)as create_time,account_id as ID,pop_code as 推广号,sum(num) as 营业额,nick_name as 玩家昵称')]);
                $obj->orderBy('id desc');
                $obj->groupBy('account_id');
                $obj->andWhere(['in', 'pop_code', $popCodeArr]);
                $obj->andFilterWhere(['between', 'create_time', $stime, $etime]);
            };

            if (isset($_this->get['accountId'])) {
                $_this->get['account_id'] = $_this->get['accountId'];
            }
            $ContributionReportClass->setWhere('account_id', $_this->get);

            $ContributionReportClass->setPage($_this->get);
            $data          = $ContributionReportClass->pageData();
            $statisticsSum = array(
                '总营业额' => 0,
                '总分成'  => 0
            );
            foreach ($data as &$value) {
                $value['分成']           = $value['营业额'] * $money_pre / 100;
                $statisticsSum['总营业额'] += $value['营业额'];
            }

            $statisticsSum['总分成'] = $statisticsSum['总营业额'] * $money_pre / 100;
            $listData             = array(
                'list'          => $data,
                'statisticsSum' => $statisticsSum,
            );

            $_this->setData($listData);
            $_this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }

    /**
     * @desc 开洗分 记录
     * @param $_this
     */
    public function actionUserDiamondRecord($_this)
    {
        try {
            if (!isset($_this->get['pageNo']) || !isset($_this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo       = $_this->get['pageNo'];
            $pageSize     = $_this->get['pageSize'];
            $account      = isset($_this->get['account']) ? $_this->get['account'] : Tool::examineEmpty($_this->get['name']);
            $accountId    = isset($_this->get['accountId']) ? $_this->get['accountId'] : "";
            $promo_code   = isset($_this->get['promo_code']) ? strtoupper($_this->get['promo_code']) : "";
            $operator     = isset($_this->get['operator']) ? $_this->get['operator'] : "";
            $operatorType = isset($_this->get['operatorType']) ? $_this->get['operatorType'] : "";
            $stime        = isset($_this->get['stime']) ? $_this->get['stime'] : date('Y-m-d', $_this->time);
            $etime        = isset($_this->get['etime']) ? $_this->get['etime'] . " 23:59:59" : date('Y-m-d 23:59:59', $_this->time);


            $popCodeArr   = $_this->Account->findAllSonPopCode($_this->loginId);
            $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";
            $where        = " fivepk_account.seoid in ({$popCodeStrIn}) and operator_type in(1,2,4) ";

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
            $data = $_this->FivepkPoint->UserDiamondRecordPage($pageNo, $pageSize, $where);
            foreach ($data as &$val) {
                $val['name'] = '';
                if (isset($val['account']['name'])) {
                    $val['account']['name'] = Factory::Tool()->hideName($val['account']['name']);
                    $val['name']            = $val['account']['name'];
                }
            }
            $FivepkPoint = $_this->FivepkPoint;

            $sum = $FivepkPoint::find()->leftJoin('fivepk_account', 'fivepk_point.account_id=fivepk_account.account_id')->leftJoin('fivepk_player_info', 'fivepk_point.account_id=fivepk_player_info.account_id')->where($where)->select('count(fivepk_point.account_id) as count,sum(fivepk_point.up_coin) as sum_up_coin,sum(fivepk_point.down_coin) as sum_down_coin')->asArray()->one();

            $account                        = Tool::examineEmpty($sum['count'], 0);
            $statisticsSum['sum_up_coin']   = Tool::examineEmpty($sum['sum_up_coin'], 0);
            $statisticsSum['sum_down_coin'] = Tool::examineEmpty($sum['sum_down_coin'], 0);
            $page                           = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
                'nowPage' => $pageNo
            );

            $_this->setData(array(
                'list'          => $data,
                'statisticsSum' => $statisticsSum
            ));
            $_this->setPage($page);
            $_this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


}

?>