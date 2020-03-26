<?php

namespace backend\controllers\fivepk;

use backend\models\Account;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\remoteInterface\remoteInterface;
use backend\models\services\PlayerService;
use common\models\game\base\RobotBase;
use common\models\game\DataKeyValuePairs;
use common\models\game\factory\RobotFactory;
use common\models\game\FivepkAccount;
use common\models\game\FivepkPlayerInfo;
use common\models\game\RobotPlayerInfo;
use common\models\game\RobotPlayerSettingData;
use common\models\GlobalConfig;
use Yii;
use backend\controllers\MyController;
use backend\models\Tool;
use yii\db\Expression;

/**
 * 几率调控
 * Class OddsController
 * @package backend\controllers
 */
class RobotController extends MyController
{

    //机器人配置列表
    public function actionGetRobotList()
    {
        try {

            Tool::checkParam(['gameType', 'room'], $this->get);

            $RobotPlayerSettingData = new RobotPlayerSettingData();

            $data = $RobotPlayerSettingData::find()->where(['robot_type' => intval($this->get['gameType']) . '_' . intval($this->get['room'])])->asArray()->all();


            if (
                $this->get['gameType'] == 12
            ) {
                foreach ($data as &$value) {
                    if ($value['active'] == 2) {
                        $value['active'] = 1;
                    }
                }
                //赛马特殊 要兼容前端所以这里转换一下
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //机器人配置添加/修改设置
    public function actionAddOne()
    {
        try {
            $RobotPlayerSettingData = new RobotPlayerSettingData();
            $status                 = $RobotPlayerSettingData->addOne($this->post);
            if (!$status) {
                throw new MyException(ErrorCode::ERROR_DATA_NOT_UP);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //机器人配置删除设置
    public function actionDeleteOne()
    {
        try {
            $RobotPlayerSettingData = new RobotPlayerSettingData();
            $status                 = $RobotPlayerSettingData->deleteOne($this->post);
            if (!$status) {
                throw new MyException(ErrorCode::ERROR_DATA_NOT_UP);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //机器人配置状态设置
    public function actionUpdateActive()
    {
        try {
            $RobotPlayerSettingData = new RobotPlayerSettingData();
            $status                 = $RobotPlayerSettingData->updateActive($this->post);
            if (!$status) {
                throw new MyException(ErrorCode::ERROR_DATA_NOT_UP);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //获取机器人玩家设置
    public function actionGetPlayerInfoOne()
    {
        try {
            Tool::checkParam(['accountId', 'gameType', 'room'], $this->get);
            $RobotPlayerInfo = new RobotPlayerInfo();
            $data            = $RobotPlayerInfo::find()->where(['account_id' => $this->get['accountId'], 'location' => intval($this->get['gameType']) . '_' . intval($this->get['room']), 'active' => 1])->asArray()->one();
            //特殊游戏需要转换
            if (
                $this->get['gameType'] == 11
                || $this->get['gameType'] == 1
                || $this->get['gameType'] == 3
                || $this->get['gameType'] == 2
                || $this->get['gameType'] == 8
            ) {
                $data_json = json_decode($data['data_json'], true);

                if (isset($data_json['nextBigAwardTime'])) {
                    $data_json['nextBigAwardTime'] = date('Y-m-d G:i', $data_json['nextBigAwardTime'] / 1000);
                }
                if (isset($data_json['reservationBackTime'])) {
                    $data_json['reservationBackTime'] = date('Y-m-d G:i', $data_json['reservationBackTime'] / 1000);
                }
                $data['data_json'] = ($data_json);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }

    //机器人玩家添加/修改设置
    public function actionAddPlayerInfoOne()
    {
        try {
            $RobotPlayerInfo = new RobotPlayerInfo();
            $status          = $RobotPlayerInfo->addOne($this->post);
            if (!$status) {
                throw new MyException(ErrorCode::ERROR_DATA_NOT_UP);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //小洛开启查看状态
    public function actionShowStatus()
    {
        try {
            $DataKeyValuePairs = new DataKeyValuePairs();
            $data              = $DataKeyValuePairs->findBase($DataKeyValuePairs::ROBOT_OPEN);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //小洛开启修改值
    public function actionUpdateStatus()
    {
        try {
            Tool::checkParam(['status'], $this->post);
            $DataKeyValuePairs = new DataKeyValuePairs();
            $obj               = $DataKeyValuePairs::find()->where(['id' => $DataKeyValuePairs::ROBOT_OPEN])->one();

            if (empty($obj) || !in_array($this->post['status'], [0, 1])) {
                throw new MyException(ErrorCode::ERROR_DATA_NOT_UP);
            }
            $obj->add(['value_int' => intval($this->post['status'])]);

            $remoteInterface = new remoteInterface();
            $remoteInterface->clearRobotTask();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 小洛玩家列表
     */
    public function actionRobotPlayerList()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $param['pageNo']         = $this->get['pageNo'];
            $param['pageSize']       = $this->get['pageSize'];
            $param['popCode']        = isset($this->get['popCode']) ? $this->get['popCode'] : "";
            $param['accountId']      = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $param['phone']          = isset($this->get['phone']) ? $this->get['phone'] : "";
            $param['name']           = isset($this->get['name']) ? $this->get['name'] : "";
            $param['machine']        = isset($this->get['machine']) ? $this->get['machine'] : "";
            $param['gameName']       = isset($this->get['gameName']) ? $this->get['gameName'] : "";
            $param['ip']             = isset($this->get['ip']) ? $this->get['ip'] : "";
            $param['register_time']  = isset($this->get['register_time']) ? $this->get['register_time'] : "";
            $param['registerToTime'] = isset($this->get['registerToTime']) ? $this->get['registerToTime'] : "";
            $param['stime']          = isset($this->get['stime']) ? $this->get['stime'] : "";
            $param['etime']          = isset($this->get['etime']) ? $this->get['etime'] . " 23:59:59" : "";
            $param['sort']           = isset($this->get['sort']) ? $this->get['sort'] : "";
            $param['sortType']       = isset($this->get['sortType']) ? strtolower($this->get['sortType']) : "desc";
            $param['loginId']        = $this->loginId;
            $param['gameType']       = isset($this->get['gameType']) ? intval($this->get['gameType']) : "";


            $pageNo        = $param['pageNo'];
            $pageSize      = $param['pageSize'];
            $popCode       = $param['popCode'];
            $accountId     = $param['accountId'];
            $name          = $param['name'];
            $machine       = $param['machine'];
            $gameName      = $param['gameName'];
            $ip            = $param['ip'];
            $register_time = $param['register_time'];
            $stime         = $param['stime'];
            $etime         = $param['etime'];
            $sort          = $param['sort'];
            $sortType      = $param['sortType'];
            $loginId       = $param['loginId'];
            $gameType      = $param['gameType'];

            $pageArr = Tool::page($pageNo, $pageSize);
            $limit   = $pageArr['limit'];
            $offset  = $pageArr['offset'];

            $FivepkAccountModel     = new FivepkAccount();
            $FivepkPlayerInfoModel  = new FivepkPlayerInfo();
            $AccountModel           = new Account();
            $popCodeArr             = $AccountModel->findAllSonPopCode($loginId);
            $OnlinePlayerData       = $FivepkAccountModel->getOnlinePlayer($popCodeArr);
            $OnlinePlayerUserStatus = $OnlinePlayerData['status'];

            $inStr = "'" . implode("','", $popCodeArr) . "'";
            $where = " fivepk_account.seoid in ({$inStr})";

            if ($popCode != "") {
                $where .= " and fivepk_account.seoid = '{$popCode}'";
            }

            if ($accountId != "") {
                $where .= " and fivepk_account.account_id = '{$accountId}'";
            }

            if ($name != "") {
                $where .= " and fivepk_account.name = '{$name}'";
            }

            if ($ip != "") {
                $where .= " and fivepk_account.account_ip = '{$ip}'";
            }

            if ($stime != "" && $etime != "") {
                $where .= " and fivepk_account.last_login_time >= '{$stime}' and fivepk_account.last_login_time < '{$etime}'";
            }

            if ($register_time != "") {
                $sRegisterTime = $register_time . " 00:00:00";
                $eRegisterTime = $register_time . " 23:59:59";
                $where         .= " and fivepk_account.create_date >= '{$sRegisterTime}' and fivepk_account.create_date < '{$eRegisterTime}'";
            }

            if ($machine != "") {
                $where .= " and (fivepk_player_info.reservation_machine_id like '%{$machine}%' 
                            OR fivepk_player_info.seo_machine_id like '%{$machine}%' 
                            OR fivepk_player_info.offline_machine_id like '%{$machine}%' )";
            }

            if ($gameName != "") {
                $where .= " and (fivepk_player_info.seo_machine_id like '%{$gameName}%' 
                            OR fivepk_player_info.reservation_machine_id like '%{$gameName}%' 
                            OR fivepk_player_info.offline_machine_id like '%{$gameName}%' )";
            }

            if ($gameType != '') {
                $where .= "and robot_player_info.location like '%{$gameType}_%' ";
            }

            if ($sort != "") {
                $orderBy = $sort . " " . $sortType;
            } else {
                $orderBy = "fivepk_player_info.is_online DESC,fivepk_player_info.seo_machine_id desc,fivepk_player_info.reservation_machine_id DESC,fivepk_account.last_login_time DESC";
            }

            $select = 'fivepk_account.*,robot_player_info.data_json';
            $query  = FivepkAccount::find();
            $data   = $query
                ->joinWith('playerInfo')
                ->innerJoin('robot_player_info', 'fivepk_account.account_id=robot_player_info.account_id')
                ->select([new Expression($select)])
                ->where($where)
                ->orderBy($orderBy)
                ->offset($offset)
                ->limit($limit)
                ->asArray()
                ->all();

            foreach ($data as $key => $val) {
                unset($data[$key]['password']);
                unset($data[$key]['salt']);
                if (isset($OnlinePlayerUserStatus[$val['account_id']])) {
                    $data[$key]['gameStaus'] = $OnlinePlayerUserStatus[$val['account_id']];
                } else {
                    $data[$key]['gameStaus'] = "";
                }

                $reservation_machine_id        = $val['playerInfo']['reservation_machine_id'];
                $seo_machine_id                = $val['playerInfo']['seo_machine_id'];
                $data[$key]['playMachine']     = $FivepkPlayerInfoModel->SwitchMachineId($reservation_machine_id, $seo_machine_id);
                $data[$key]['status']          = $FivepkAccountModel->switchStatus($val['playerInfo']['is_online'], $val['allowed']);
                $data[$key]['create_date']     = mb_substr($val['create_date'], 5);
                $data[$key]['last_login_time'] = mb_substr($val['last_login_time'], 5);
//                $data[$key]['name']            = Factory::Tool()->hideName(($val['name']));
            }
            $data = array(
                'online' => $OnlinePlayerData,
                'data'   => $data
            );


            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  小洛玩家添加/修改
     * @throws MyException
     * @throws \yii\db\Exception
     */

    public function actionRobotPlayerAdd()
    {
        Tool::checkParam(['nickName', 'score', 'coin'], $this->post);

        $FivepkPlayerInfo = new FivepkPlayerInfo();

        if (Tool::examineEmpty($this->post['id'])) {
            $obj = $FivepkPlayerInfo::findOne(['account_id' => $this->post['id']]);
            if (!empty($obj)) {
                $data = $obj->add(
                    [
                        'nick_name' => $this->post['nickName'],
                        'score'     => $this->post['score'],
                        'coin'      => $this->post['coin'],
                    ]
                );
            }
        } else {
            $tr            = GlobalConfig::getDb()->beginTransaction();
            $tr2           = $FivepkPlayerInfo::getDb()->beginTransaction();
            $name          = '17700000001';
            $num           = GlobalConfig::getValue(GlobalConfig::AI_AUTO_NUM);
            $count         = mb_strlen($num);
            $name          = substr_replace($name, $num, -$count, $count);
            $createDate    = date(Tool::DATE_USUALLY_FORMAT, $this->time);
            $data          = [
//            'account_id'             => '505',
                'name'                 => $name,
                'password'             => '5cd833e1625998f2436538126996250b',
                'salt'                 => 'nYL8EO',
                'seoid'                => 'XL',
                'account_type'         => '1',
                'create_date'          => $createDate,
                'last_login_time'      => '',
//                'backend_zdl_account_id' => '0',
                'phone_number'         => '',
                'invitation_code'      => '',
                'invitation_count'     => '0',
                'from_invitation_code' => '',
                'province'             => '',
                'city'                 => '',
                'picture'              => '',
                'is_get_red_bag'       => '0',
                'open_id'              => '',
                'account_ip'           => '',
                'allowed'              => '0',
                'udid'                 => '',
                'address'              => ' '
            ];
            $FivepkAccount = new FivepkAccount();
            $accountData   = $FivepkAccount->add($data);

            $pic = rand(1, 6);

            $data = [
                'account_id'                       => $accountData['account_id'],
                'nick_name'                        => $this->post['nickName'],
                'pic'                              => $pic,
                'is_online'                        => '0',
                'coin'                             => $this->post['coin'],
                'score'                            => $this->post['score'],
                'score_sl'                         => '0',
                'guide'                            => '1',
                'win_history'                      => '0',
                'win_best'                         => '0',
                'is_first_recharge'                => '0',
                'day_first_login'                  => '1',
                'today_contribution'               => '0',
                'yesterday_contribution'           => '0',
                'total_contribution'               => '0',
                'today_experience_contribution'    => '0',
                'experience_contribution'          => '0',
                'reservation_contribution'         => '0',
                'score_guest'                      => '0',
                'score_guest_count'                => '0',
                'nick_name_count'                  => '1',
                'room_info_list_id'                => '',
                'seo_machine_id'                   => '',
                'reservation_machine_id'           => '',
                'offline_machine_id'               => '',
                'prefab_jail'                      => '1',
                'prefab_jail_big_shark'            => '0',
                'prefab_jail_big_plate'            => '1',
                'prefab_jail_gold_crown'           => '0',
                'prefab_jail_att'                  => '0',
                'prefab_jail_star97'               => '0',
                'prefab_jail_sbb'                  => '0',
                'prefab_jail_snow_leopard'         => '1',
                'switch_changed_time'              => '0',
                'total_play'                       => '0',
                'total_win_point'                  => '0',
                'total_play_point'                 => '0',
                'win_point'                        => '0',
                'play_point'                       => '0',
                'play_math'                        => '0',
                'is_reachd_ty_reward'              => '0',
                'ty_online_time'                   => '0',
                'is_star97_newer'                  => '1',
                'star97_play_count'                => '0',
                'star97_newer_interval_count'      => '0',
                'star97_newer_current_index'       => '2',
                'last_enter97_time'                => date(Tool::DATE_USUALLY_FORMAT, $this->time),
                'share_given_money_count'          => '0',
                'newer_gap_count'                  => '0',
                'newer_gap'                        => '0',
                'newer_contribution_rate'          => '0',
                'newer_cur_win_type_continue'      => '',
                'newer_cur_win_type'               => '0',
                'newer_win_pool'                   => '0',
                'four_of_a_kind_gift_count'        => '0',
                'four_of_a_kind_gift_count_random' => '0',
                'ty_activity_left_count'           => '0',
                'share_friends'                    => '0',
                'share_story'                      => '0'
            ];
            $FivepkPlayerInfo->add($data);

            $RobotPlayerInfo = new RobotPlayerInfo();
            $RobotPlayerInfo->add(['account_id' => $accountData['account_id']]);
//            $num += 1;
            GlobalConfig::setValue(GlobalConfig::AI_AUTO_NUM, $num + 1);

            $tr->commit();
            $tr2->commit();
        }

        $this->sendJson();

    }


}

?>