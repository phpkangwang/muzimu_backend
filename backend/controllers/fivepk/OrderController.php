<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-5
 * Time: 18:27
 */

namespace backend\controllers\fivepk;

use backend\models\Tool;
use common\models\Attention;
use common\models\DataGameListInfo;
use common\models\game\FivepkMailReport;
use Yii;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\services\PlayerService;
use common\models\game\FivepkAccount;
use common\models\core\DataStar97NewerReward;
use common\models\game\FivepkPlayerInfo;
use common\models\PlayerIsChange;
use common\services\ToolService;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class OrderController extends MyController
{
    /**
     * 充值订单头部统计信息
     */
    public function actionOrderHeaderData()
    {
        try{
            $accountId       = isset( $this->get['accountId'] ) ? $this->get['accountId'] : "";
            $popCode         = isset( $this->get['popCode'] ) ? $this->get['popCode'] : "";
            $payType         = isset( $this->get['payType'] ) ? $this->get['payType'] : "";
            $innerOrderId    = isset( $this->get['innerOrderId'] ) ? $this->get['innerOrderId'] : "";
            $platformOrderId = isset( $this->get['platformOrderId'] ) ? $this->get['platformOrderId'] : "";
            $isOnline        = isset( $this->get['isOnline'] ) ? $this->get['isOnline'] : "";
            $status          = isset( $this->get['status'] ) ? $this->get['status'] : "";
            $stime           = isset( $this->get['stime'] ) ? $this->get['stime'] : "";
            $etime           = isset( $this->get['etime'] ) ? $this->get['etime'] : "";
            $recharge_type   = isset( $this->get['rechargeType'] ) ? $this->get['rechargeType'] : "";

            $where = " 1";
            if( !empty($accountId) ){
                $where .= " and fivepk_order.account_id = '{$accountId}'";
            }
            if( !empty($popCode) ){
                $where .= " and fivepk_account.seoid = '{$popCode}'";
            }
            if( !empty($payType) ){
                $where .= " and fivepk_order.pay_type = '{$payType}'";
            }
            if( !empty($innerOrderId) ){
                $where .= " and fivepk_order.inner_order_id like '%{$innerOrderId}%'";
            }
            if( !empty($platformOrderId) ){
                $where .= " and fivepk_order.platform_order_id like '%{$platformOrderId}%'";
            }
            if( $isOnline != "" ){
                $where .= " and fivepk_player_info.is_online = '{$isOnline}'";
            }
            if( $status != "" ){
                $where .= " and status = '{$status}'";
            }
            if( !empty($stime) ){
                $where .= " and pay_time >= '".$stime." 00:00:00'";
            }
            if( !empty($etime) ){
                $where .= " and pay_time < '".$etime." 23:59:59'";
            }
            if( !empty($recharge_type)){
                $where .= " and recharge_type = '{$recharge_type}'";
            }
            $headerData = [
                'Google充值'=>0
                ,'OPPO充值'=>0
                ,'充值总分数'=>0
                ,'赠送总分数'=>0
            ];
            $results = $this->FivepkOrder->headerDataCounts($where);
            foreach ($results['sums'] as $value){
                //1-苹果2-谷歌3-OPPO
                if($value['pay_type']==2){
                    $headerData['Google充值']+=$value['rechargeMoneySum'];
                }elseif ($value['pay_type']==3){
                    $headerData['OPPO充值']+=$value['rechargeMoneySum'];
                }
                $headerData['充值总分数']+=$value['scoreSum'];
                $headerData['赠送总分数']+=$value['giftScoreSum'];
            }

            $headerData['Google充值'] = round($headerData['Google充值'],2);
            $headerData['OPPO充值'] = round($headerData['OPPO充值'],2);
            foreach ($results['counts'] as $count){
                $headerData[substr($count['recharge_money'],0,strlen($count['recharge_money'])-2)] = $count['count'];
            }
            $this->setData($headerData);
            $this->sendJson();
        }catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionOrderPage()
    {
        try {
            if ( !isset($this->get['pageSize']) || !isset($this->get['pageNo']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $accountId       = isset( $this->get['accountId'] ) ? $this->get['accountId'] : "";
            $popCode         = isset( $this->get['popCode'] ) ? $this->get['popCode'] : "";
            $payType         = isset( $this->get['payType'] ) ? $this->get['payType'] : "";
            $innerOrderId    = isset( $this->get['innerOrderId'] ) ? $this->get['innerOrderId'] : "";
            $platformOrderId = isset( $this->get['platformOrderId'] ) ? $this->get['platformOrderId'] : "";
            $isOnline        = isset( $this->get['isOnline'] ) ? $this->get['isOnline'] : "";
            $status          = isset( $this->get['status'] ) ? $this->get['status'] : "";
            $stime           = isset( $this->get['stime'] ) ? $this->get['stime'] : "";
            $etime           = isset( $this->get['etime'] ) ? $this->get['etime'] : "";
            $recharge_type   = isset( $this->get['rechargeType'] ) ? $this->get['rechargeType'] : "";

            $where = " 1";
            if( !empty($accountId) ){
                $where .= " and fivepk_order.account_id = '{$accountId}'";
            }
            if( !empty($popCode) ){
                $where .= " and fivepk_account.seoid = '{$popCode}'";
            }
            if( !empty($payType) ){
                $where .= " and fivepk_order.pay_type = '{$payType}'";
            }
            if( !empty($innerOrderId) ){
                $where .= " and fivepk_order.inner_order_id like '%{$innerOrderId}%'";
            }
            if( !empty($platformOrderId) ){
                $where .= " and fivepk_order.platform_order_id like '%{$platformOrderId}%'";
            }
            if( $isOnline != "" ){
                $where .= " and fivepk_player_info.is_online = '{$isOnline}'";
            }
            if( $status != "" ){
                $where .= " and status = '{$status}'";
            }
            if( !empty($stime) ){
                $where .= " and pay_time >= '".$stime." 00:00:00'";
            }
            if( !empty($etime) ){
                $where .= " and pay_time < '".$etime." 23:59:59'";
            }
            if( !empty($recharge_type)){
                $where .= " and recharge_type = '{$recharge_type}'";
            }
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];

            $data = $this->FivepkOrder->page($pageNo,$pageSize,$where);

            $count = $this->FivepkOrder->pageCount($where);
            $page = array(
                'account' => $count,
                'maxPage' => ceil($count/$pageSize),
                'nowPage' => $pageNo
            );
            $recharge_type_arr = Yii::$app->params['orderRechargeType'];
            foreach ($data as $key => $val) {
                $data[$key]['recharge_money'] = round($val['recharge_money'], 2);
                $data[$key]['pay_type'] = Tool::examineEmpty(Yii::$app->params['orderPayType'][$val['pay_type']]);
                $data[$key]['status'] = Tool::examineEmpty(Yii::$app->params['orderPayStatus'][$val['status']]);
                $data[$key]['nickName'] = $val['playerInfo']['nick_name'];
                $data[$key]['is_online'] = $val['playerInfo']['is_online'] == '0' ? '' : '在线';
                $data[$key]['seo_machine_id'] = $val['playerInfo']['seo_machine_id'];
                $data[$key]['recharge_type'] =Tool::examineEmpty($recharge_type_arr[$data[$key]['recharge_type']]);
            }
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  订单统计
     */
    public function actionOrderStatistic()
    {
        try {
            if ( !isset($this->get['stime']) || !isset($this->get['etime']) || !isset($this->get['pageNo']) || !isset($this->get['pageSize'])  ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $stime     = $this->get['stime'];
            $etime     = $this->get['etime'];
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];
            $popCode   = isset( $this->get['popCode'] ) ? $this->get['popCode'] : "";
            $accountId = isset( $this->get['accountId'] ) ? $this->get['accountId'] : "";
            $reMonSum  = isset( $this->get['reMonSum'] ) ? $this->get['reMonSum'] : "";
            $scoreSum  = isset( $this->get['scoreSum'] ) ? $this->get['scoreSum'] : "";
            $gScoreSum = isset( $this->get['gScoreSum'] ) ? $this->get['gScoreSum'] : "";
            $sort      = isset( $this->get['sort'] ) ? $this->get['sort'] : "";
            $sortType  = isset( $this->get['sortType'] ) ? strtolower($this->get['sortType']) : "desc";
            $payType = intval(Tool::examineEmpty($this->get['payType']));

            $where = " fivepk_order.account_id = fivepk_account.account_id and status = 2";
            $whereSon = " 1";//子查询的where条件
            if( !empty($popCode) ){
                $where .= " and fivepk_account.seoid = '{$popCode}'";
            }
            if( !empty($payType) ){
                $where .= " and fivepk_order.pay_type =$payType ";
            }

            if( !empty($stime) ){
                $where .= " and pay_time >= '".$this->get['stime']." 00:00:00'";
            }
            if( !empty($etime) ){
                $where .= " and pay_time < '".$this->get['etime']." 23:59:59'";
            }
            if( !empty($accountId) ){
                $where .= " and fivepk_order.account_id = '{$accountId}'";
            }
            if( !empty($reMonSum) ){
                $whereSon .= " and CONCAT(reMonSum,'') = {$reMonSum}";
            }
            if( !empty($scoreSum) ){
                $whereSon .= " and scoreSum = {$scoreSum}";
            }
            if( !empty($gScoreSum) ){
                $whereSon .= " and gScoreSum = {$gScoreSum}";
            }
            $orderBy = "";
            if( !empty($sort)){
                $orderBy = "order by {$sort} {$sortType}";
            }
            $data = $this->FivepkOrder->statistic( $where , $whereSon, $orderBy, $pageNo, $pageSize);
            $count = $this->FivepkOrder->statisticCount($where , $whereSon);
            $page = array(
                'account' => $count,
                'maxPage' => ceil($count/$pageSize),
                'nowPage' => $pageNo
            );
            $accountIds = array_column($data, 'account_id');
            $accountIds = array_unique($accountIds);
            $accountObjs = $this->FivepkPlayerInfo->finds($accountIds);
            foreach ($data as $key => $val)
            {
                $data[$key]['reMonSum'] = round( $val['reMonSum'],2);
                $data[$key]['nickName'] = "";
                foreach ($accountObjs as $FivepkPlayerInfoObj )
                {
                    if($val['account_id'] == $FivepkPlayerInfoObj['account_id']){
                        $data[$key]['nickName'] = $FivepkPlayerInfoObj['nick_name'];
                    }
                }

            }
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  订单统计求和
     */
    public function actionOrderStatisticSum()
    {
        try {
            if ( !isset($this->get['stime']) || !isset($this->get['etime'])   ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $stime     = $this->get['stime'];
            $etime     = $this->get['etime'];
            $popCode   = isset( $this->get['popCode'] ) ? $this->get['popCode'] : "";
            $accountId = isset( $this->get['accountId'] ) ? $this->get['accountId'] : "";

            $payType = intval(Tool::examineEmpty($this->get['payType']));

            $where = " fivepk_order.account_id = fivepk_account.account_id and status = 2 ";

            if( !empty($popCode) ){
                $where .= " and fivepk_account.seoid = '{$popCode}'";
            }

            if( !empty($payType) ){
                $where .= " and fivepk_order.pay_type =$payType ";
            }

            if( !empty($stime) ){
                $where .= " and pay_time >= '".$this->get['stime']." 00:00:00'";
            }
            if( !empty($etime) ){
                $where .= " and pay_time < '".$this->get['etime']." 23:59:59'";
            }
            if( !empty($accountId) ){
                $where .= " and fivepk_order.account_id = '{$accountId}'";
            }

            $rs = [
                'Google充值'=>0
                ,'OPPO充值'=>0
                ,'充值总分数'=>0
                ,'赠送总分数'=>0
            ];
            $data = $this->FivepkOrder->statisticSumPayType( $where );
            foreach ($data as $value){
                //1-苹果2-谷歌3-OPPO
                if($value['pay_type']==2){
                    $rs['Google充值']+=$value['reMonSum'];
                }elseif ($value['pay_type']==3){
                    $rs['OPPO充值']+=$value['reMonSum'];
                }
                $rs['充值总分数']+=$value['scoreSum'];
                $rs['赠送总分数']+=$value['gScoreSum'];
            }
            $this->setData($rs);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  分享列表
     */
    public function actionSharePage()
    {
        try {
            if ( !isset($this->get['pageSize']) || !isset($this->get['pageNo']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $accountId    = isset( $this->get['accountId'] ) ? $this->get['accountId'] : "";
            $popCode      = isset( $this->get['popCode'] ) ? $this->get['popCode'] : "";
            $type         = isset( $this->get['type'] ) ? $this->get['type'] : "";
            $stime        = isset( $this->get['stime'] ) ? $this->get['stime'] : "";
            $etime        = isset( $this->get['etime'] ) ? $this->get['etime'] : "";
            $pageNo       = $this->get['pageNo'];
            $pageSize     = $this->get['pageSize'];

            $where = " 1 ";
            if( !empty($accountId) ){
                $where .= " and sr.account_id = '{$this->get['accountId']}'";
            }
            if( !empty($popCode) ){
                $where .= " and account.seoid = '{$popCode}'";
            }
            if( $type != "" ){
                $where .= " and sr.type = '{$type}'";
            }

            if( !empty($stime)&&!empty($etime) ) {
                $stime = strtotime($this->get['stime'])*1000;
                $etime = (strtotime($this->get['etime']) + 86400)*1000;
                $where .= " and sr.`create_time` BETWEEN $stime AND $etime";
            }

            $data = $this->FivepkShareRecord->page($pageNo,$pageSize,$where);
            $count = $this->FivepkShareRecord->pageCount($where);
            $page = array(
                'account' => $count,
                'maxPage' => ceil($count/$pageSize),
                'nowPage' => $pageNo
            );

            foreach ($data as $key => $val)
            {
                $data[$key]['type'] = isset(Yii::$app->params['shareType'][$val['type']])?(Yii::$app->params['shareType'][$val['type']]):'';
                $data[$key]['nickName'] = $val['nick_name'];
                $data[$key]['date'] = date('Y-m-d H:i:s',$val['create_time']/1000);
            }

            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        }catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  分享统计
     */
    public function actionShareStatistic()
    {
        try {
            if ( !isset($this->get['stime']) || !isset($this->get['etime']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $accountId    = isset( $this->get['accountId'] ) ? $this->get['accountId'] : "";
            $popCode      = isset( $this->get['popCode'] ) ? $this->get['popCode'] : "";
            $type         = isset( $this->get['type'] ) ? $this->get['type'] : "";
            $stime        = isset( $this->get['stime'] ) ? $this->get['stime'] : "";
            $etime        = isset( $this->get['etime'] ) ? $this->get['etime'] : "";


            $where = " 1 ";
            if( !empty($accountId) ){
                $where .= " and sr.account_id = '{$this->get['accountId']}'";
            }
            if( !empty($popCode) ){
                $where .= " and account.seoid = '{$popCode}'";
            }
            if( $type != "" ){
                $where .= " and sr.type = '{$type}'";
            }

            if( !empty($stime)&&!empty($etime) ) {
                $stime = strtotime($this->get['stime'])*1000;
                $etime = (strtotime($this->get['etime']) + 86400)*1000;
                $where .= " and sr.`create_time` BETWEEN $stime AND $etime";
            }

            $data = $this->FivepkShareRecord->statistic( $where );
            $rsData = array();
            $shareTimes = 0;
            $friendShareTimes = 0;
            $shareScoreSum = 0;
            $friendScoreSum = 0;
            foreach ($data as $val)
            {
                if($val['type'] == '0'){
                    $shareScoreSum += $val['bonus'];
                    $shareTimes += 1;
                }
                if($val['type'] == '2'){
                    $friendScoreSum += $val['bonus'];
                    $friendShareTimes += 1;
                }
            }
            $rsData['分享奖励'] = $shareScoreSum;
            $rsData['分享次数'] = $shareTimes;
            $rsData['邀请次数'] = $friendShareTimes;
            $rsData['邀请奖励'] = $friendScoreSum;

            $this->setData($rsData);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  邀请列表
     */
    public function actionInvitationPage()
    {
        try {
            if ( !isset($this->get['pageSize']) || !isset($this->get['pageNo']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $pageNo          = $this->get['pageNo'];
            $pageSize        = $this->get['pageSize'];

            $accountId = isset( $this->get['accountId'] ) ? $this->get['accountId'] : "";
            $where = " (invited_score <> 0 or invite_success_count <> 0 or invite_success_received_count <> 0)";
            if( $accountId != ""){
                $where .= " and account_id = '{$accountId}'";
            }
            $data  = $this->FivepkPlayerInfo->page($pageNo, $pageSize, $where);
            $count = $this->FivepkPlayerInfo->pageCount( $where );
            $page = array(
                'account' => $count,
                'maxPage' => ceil($count/$pageSize),
                'nowPage' => $pageNo
            );
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  分享统计
     */
    public function actionInvitationStatistic()
    {
        try {
            if ( !isset($this->get['stime']) || !isset($this->get['etime']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $stime = isset( $this->get['stime'] ) ? $this->get['stime'] : "";
            $etime = isset( $this->get['etime'] ) ? $this->get['etime'] : "";

            $where = " 1";
            if( !empty($stime) ){
                $where .= " and date >= '".$this->get['stime']." 00:00:00'";
            }
            if( !empty($etime) ){
                $where .= " and date < '".$this->get['etime']." 23:59:59'";
            }
            $data = $this->FivepkShareRecord->statistic( $where );
            $rsData = array();
            $shareTimes = 0;
            $friendShareTimes = 0;
            $shareScoreSum = 0;
            foreach ($data as $val)
            {
                $shareScoreSum += $val['bonus'];
                $shareTimes += $val['share_fb_zone'] == 0 ? 0 : 1;
                $friendShareTimes += $val['share_fb_friend'] == 0 ? 0 : 1;
            }
            $rsData['分享总得分'] = $shareScoreSum;
            $rsData['动态分享总次数'] = $shareTimes;
            $rsData['朋友分享总次数'] = $friendShareTimes;

            $this->setData($rsData);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   赠送礼物记录
     */
    public function actionSendGiftPage()
    {
        try {
            if ( !isset($this->get['pageSize']) || !isset($this->get['pageNo']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $sendAccountId    = isset( $this->get['sendAccountId'] ) ? $this->get['sendAccountId'] : "";
            $acceptAccountId  = isset( $this->get['acceptAccountId'] ) ? $this->get['acceptAccountId'] : "";
            $stime            = isset( $this->get['stime'] ) ? $this->get['stime'] : "";
            $etime            = isset( $this->get['etime'] ) ? $this->get['etime'] : "";
            $pageNo           = $this->get['pageNo'];
            $pageSize         = $this->get['pageSize'];

            $where = " 1";
            if( !empty( $sendAccountId ) ){
                $where .= " and giver_id = {$this->get['sendAccountId']}";
            }
            if( !empty( $acceptAccountId ) ){
                $where .= " and receiver_id = {$this->get['acceptAccountId']}";
            }
            if( !empty( $stime ) ){
                $where .= " and create_time >= '".$this->get['stime']." 00:00:00'";
            }
            if( !empty( $etime ) ){
                $where .= " and create_time < '".$this->get['etime']." 23:59:59'";
            }

            $data = $this->FivepkGivedGift->page($pageNo,$pageSize,$where);
            $count = $this->FivepkGivedGift->pageCount($where);
            $page = array(
                'account' => $count,
                'maxPage' => ceil($count/$pageSize),
                'nowPage' => $pageNo
            );
            foreach ($data as $key => $val)
            {
                $data[$key]['status']   = Yii::$app->params['givedGift'][$val['status']];
            }
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  月卡统计
     */
    public function actionGetPayMonthList()
    {
        try {
            $class = new \common\models\game\FivepkOrder();
            $MONTH_CARD_RECHARGE_ACTIVITY = $class::MONTH_CARD_RECHARGE_ACTIVITY;

            $potion = [
                'select' => 'account_id,count(account_id) as num,MAX(pay_time) as pay_time',
                'groupBy' => 'account_id',
                'order' => 'pay_time desc',
//                'indexBy' => 'account_id'
            ];

            //这段是PDO where
            {
                $pdo = [];
                $where = "status=2 and recharge_type='$MONTH_CARD_RECHARGE_ACTIVITY' ";

                $field = 'account_id';
                if (!Tool::isIssetEmpty($this->get[$field])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where .= "$field =:$field";
                    $pdo[":$field"] = ($this->get[$field]);
                }

                if (!empty($where)) {
                    $potion['where'] = $where;
                    $potion['pdo'] = $pdo;
                }
            }

            $pageNo = Tool::examineEmpty($this->get['pageNo'], 1);
            $pageSize = Tool::examineEmpty($this->get['pageSize'], 10);
            $data = $class->pageList(
                $pageNo
                , $pageSize
                , $potion
            );

            if (!empty($data)) {
                $FivepkPlayerInfo = new \common\models\game\FivepkPlayerInfo();
                $accountIds = implode(',', ArrayHelper::map($data,'account_id','account_id'));
                $info = $FivepkPlayerInfo->pageList(
                    $pageNo
                    , $pageSize
                    , [
                        'where' => "account_id in($accountIds)",
                        'pdo' => '',
                        'select' => 'month_card_surplus_day,account_id,nick_name',
                        'indexBy' => 'account_id'
                    ]
                );

                foreach ($data as $key => &$val) {
                    $val['month_card_surplus_day'] = isset($info[$val['account_id']]['month_card_surplus_day']) ? $info[$val['account_id']]['month_card_surplus_day'] : 0;
                    $val['nick_name'] = isset($info[$val['account_id']]['nick_name']) ? $info[$val['account_id']]['nick_name'] : '';
                }
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

}