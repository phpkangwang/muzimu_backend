<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-5
 * Time: 18:27
 */

namespace backend\controllers\platform\thwj\backend\controllers\fivepk;

use backend\models\Account;
use backend\models\Factory;
use backend\models\services\PlayerService;
use backend\models\Tool;
use common\models\game\FivepkAccount;
use common\models\game\FivepkPlayerInfo;
use common\models\pay\platform\PayLayerAccount;
use Yii;

class PlayerController
{
    use \backend\controllers\platform\PlatformTrait;

    /**
     *   获取用户奖券数量
     */
    public function getItemInfo($accountIds)
    {
        return "";
    }


    //玩家列表
    public function actionIndex($param)
    {
        $pageNo        = $param['pageNo'];
        $pageSize      = $param['pageSize'];
        $popCode       = $param['popCode'];
        $accountId     = $param['accountId'];
        $loginSystem   = $param['loginSystem'];
        $name          = $param['name'];
        $phone         = $param['phone'];
        $udid          = $param['udid'];
        $machine       = $param['machine'] ;
        $gameName      = $param['gameName'];
        $ip            = $param['ip'];
        $register_time = $param['register_time'];
        $stime         = $param['stime'];
        $etime         = $param['etime'];
        $sort          = $param['sort'];
        $sortType      = $param['sortType'];
        $loginId       = $param['loginId'];

        $pageArr       = Tool::page($pageNo,$pageSize);
        $limit         = $pageArr['limit'];
        $offset        = $pageArr['offset'];

        $FivepkAccountModel    = new FivepkAccount();
        $FivepkPlayerInfoModel = new FivepkPlayerInfo();
        $AccountModel          = new Account();
        $popCodeArr         = $AccountModel->findAllSonPopCode($loginId);
        $OnlinePlayerData   = $FivepkAccountModel->getOnlinePlayer($popCodeArr);
        $OnlinePlayerUserStatus = $OnlinePlayerData['status'];

        $inStr = "'".implode("','", $popCodeArr)."'";
        $where = " fivepk_account.seoid in ({$inStr})";
        if( $popCode != "" ){
            $where .= " and fivepk_account.seoid = '{$popCode}'";
        }

        if( $accountId != "" ){
            $where .= " and fivepk_account.account_id = '{$accountId}'";
        }

        if( $loginSystem != "" ){
            $where .= " and fivepk_account.login_system = '{$loginSystem}'";
        }

        if( $name != "" ){
            $where .= " and fivepk_account.name = '{$name}'";
        }

        if( $phone != "" ){
            $where .= " and fivepk_account.phone_number = '{$phone}'";
        }

        if( $udid != "" ){
            $where .= " and fivepk_account.udid = '{$udid}'";
        }

        if( $ip != "" ){
            $where .= " and fivepk_account.account_ip = '{$ip}'";
        }

        if( $stime != "" && $etime != ""){
            $where .= " and fivepk_account.last_login_time >= '{$stime}' and fivepk_account.last_login_time < '{$etime}'";
        }

        if( $register_time != ""){
            $sRegisterTime = $register_time." 00:00:00";
            $eRegisterTime = $register_time." 23:59:59";
            $where .= " and fivepk_account.create_date >= '{$sRegisterTime}' and fivepk_account.create_date < '{$eRegisterTime}'";
        }

        if( $machine != ""){
            $where .= " and (fivepk_player_info.reservation_machine_id like '%{$machine}%' 
                            OR fivepk_player_info.seo_machine_id like '%{$machine}%' 
                            OR fivepk_player_info.offline_machine_id like '%{$machine}%' )";
        }

        if( $gameName != "" ){
            if( $gameName == "GHR" ){
                $where .= " and fivepk_player_info.room_info_list_id = '12_2' ";
            }else{
                $where .= " and (fivepk_player_info.seo_machine_id like '%{$gameName}%'
                                    OR fivepk_player_info.reservation_machine_id like '%{$gameName}%'
                                    OR fivepk_player_info.offline_machine_id like '%{$gameName}%' )";
            }
        }

        if ( $sort != "") {
            $orderBy = $sort." ".$sortType;
        } else {
            $orderBy = "fivepk_player_info.is_online DESC,fivepk_player_info.seo_machine_id desc,fivepk_player_info.reservation_machine_id DESC,fivepk_account.last_login_time DESC";
        }

        $select = [
            'fivepk_account.*',
            '(`fivepk_player_info`.`total_contribution`/`fivepk_player_info`.`total_play`) as bi',
            '(`fivepk_player_info`.`total_win_point`/`fivepk_player_info`.`total_play_point`) as ci'
        ];

        $query = FivepkAccount::find();
        $data  = $query
            ->joinWith('playerInfo')
            ->select($select)
            ->where($where)
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();

        //获取所有的层级
        $PayLayerAccountModel = new PayLayerAccount();
        $PayLayerAccountObj = $PayLayerAccountModel->tableList();
        $newPayLayerAccountObj = array();
        foreach ($PayLayerAccountObj as $val)
        {
            $newPayLayerAccountObj[$val['id']] = $val['name'];
        }

        foreach ($data as $key=>$val){
            unset($data[$key]['password']);
            unset($data[$key]['salt']);
            if( isset($OnlinePlayerUserStatus[$val['account_id']]) ){
                $data[$key]['gameStaus'] = $OnlinePlayerUserStatus[$val['account_id']];
            }else{
                $data[$key]['gameStaus'] = "";
            }

            $reservation_machine_id = $val['playerInfo']['reservation_machine_id'];
            $seo_machine_id = $val['playerInfo']['seo_machine_id'];
            $data[$key]['playMachine']     = $FivepkPlayerInfoModel->SwitchMachineId($reservation_machine_id, $seo_machine_id);
            $data[$key]['status']          = $FivepkAccountModel->switchStatus($val['playerInfo']['is_online'], $val['allowed']);
            $data[$key]['create_date']     = mb_substr($val['create_date'],5 );
            $data[$key]['last_login_time'] = mb_substr($val['last_login_time'],5 );
//            $data[$key]['phone_number']            = Factory::Tool()->hideName(($val['phone_number']));
            $data[$key]['payLayer']        = $newPayLayerAccountObj[$val['pay_layer']];
            $data[$key]['name']            = Factory::Tool()->hideName(($val['name']));
        }
        return array(
            'online'=>$OnlinePlayerData,
            'data'=>$data
        );
    }


    //玩家列表
    public function actionIndexOld($param)
    {
        $pageNo        = $param['pageNo'];
        $pageSize      = $param['pageSize'];
        $popCode       = $param['popCode'];
        $accountId     = $param['accountId'];
        $name          = $param['name'];
        $machine       = $param['machine'] ;
        $gameName      = $param['gameName'];
        $register_time = $param['register_time'];
        $stime         = $param['stime'];
        $etime         = $param['etime'];
        $sort          = $param['sort'];
        $sortType      = $param['sortType'];
        $loginId       = $param['loginId'];

        $pageArr       = Tool::page($pageNo,$pageSize);
        $limit         = $pageArr['limit'];
        $offset        = $pageArr['offset'];

        $FivepkAccountModel    = new FivepkAccount();
        $FivepkPlayerInfoModel = new FivepkPlayerInfo();
        $AccountModel          = new Account();
        $popCodeArr         = $AccountModel->findAllSonPopCode($loginId);
        $OnlinePlayerData   = $FivepkAccountModel->getOnlinePlayer($popCodeArr);
        $OnlinePlayerUserStatus = $OnlinePlayerData['status'];

        $inStr = "'".implode("','", $popCodeArr)."'";
        $where = " fivepk_account.seoid in ({$inStr})";
        if( $popCode != "" ){
            $where .= " and fivepk_account.seoid = '{$popCode}'";
        }

        if( $accountId != "" ){
            $where .= " and fivepk_account.account_id = '{$accountId}'";
        }

        if( $name != "" ){
            $where .= " and fivepk_account.name = '{$name}'";
        }

        if( $stime != "" && $etime != ""){
            $where .= " and fivepk_account.last_login_time >= '{$stime}' and fivepk_account.last_login_time < '{$etime}'";
        }

        if( $register_time != ""){
            $sRegisterTime = $register_time." 00:00:00";
            $eRegisterTime = $register_time." 23:59:59";
            $where .= " and fivepk_account.create_date >= '{$sRegisterTime}' and fivepk_account.create_date < '{$eRegisterTime}'";
        }

        if( $machine != ""){
            $where .= " and (fivepk_player_info.reservation_machine_id like '%{$machine}%' 
                                OR fivepk_player_info.seo_machine_id like '%{$machine}%' 
                                OR fivepk_player_info.offline_machine_id like '%{$machine}%' )";
        }

        if( $gameName != "" ){
            if( $gameName == "GHR" ){
                $where .= " and fivepk_player_info.room_info_list_id = '12_2' ";
            }elseif( $gameName == "BYU" ){
                $where .= " and fivepk_player_info.room_info_list_id = '13' ";
            }else{
                $where .= " and (fivepk_player_info.seo_machine_id like '%{$gameName}%'
                                    OR fivepk_player_info.reservation_machine_id like '%{$gameName}%'
                                    OR fivepk_player_info.offline_machine_id like '%{$gameName}%' )";
            }
        }

        if ( $sort != "") {
            $orderBy = $sort." ".$sortType;
        } else {
            $orderBy="fivepk_player_info.is_online DESC,fivepk_player_info.seo_machine_id desc,fivepk_player_info.reservation_machine_id DESC,fivepk_account.last_login_time DESC";
        }

        $select=[
            'fivepk_account.*',
            '(`fivepk_player_info`.`total_contribution`/`fivepk_player_info`.`total_play`) as bi',
            '(`fivepk_player_info`.`total_win_point`/`fivepk_player_info`.`total_play_point`) as ci'
        ];

        $query  = FivepkAccount::find();
        $data  = $query
            ->joinWith('playerInfo')
            ->joinWith('playerInfoFish')
            ->select($select)
            ->where($where)
            ->orderBy($orderBy)
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();

        foreach ($data as $key=>$val){
            unset($data[$key]['password']);
            unset($data[$key]['salt']);
            if( isset($OnlinePlayerUserStatus[$val['account_id']]) ){
                $data[$key]['gameStaus'] = $OnlinePlayerUserStatus[$val['account_id']];
            }else{
                $data[$key]['gameStaus'] = "";
            }

            $reservation_machine_id = $val['playerInfo']['reservation_machine_id'];
            $seo_machine_id = $val['playerInfo']['seo_machine_id'];
            $data[$key]['playMachine']     = $FivepkPlayerInfoModel->SwitchMachineId($reservation_machine_id, $seo_machine_id);
            $data[$key]['status']          = $FivepkAccountModel->switchStatus($val['playerInfo']['is_online'], $val['allowed']);
            $data[$key]['create_date']     = mb_substr($val['create_date'],5 );
            $data[$key]['last_login_time'] = mb_substr($val['last_login_time'],5 );
            $data[$key]['phone_number']    = Factory::Tool()->hideName(($val['phone_number']));
            $data[$key]['name']            = Factory::Tool()->hideName(($val['name']));
        }

        return array(
            'online'=>$OnlinePlayerData,
            'data'=>$data
        );
    }
}