<?php

namespace backend\controllers\fivepk;

use backend\models\Factory;
use backend\models\redis\MyRedis;
use backend\models\remoteInterface\remoteInterface;
use backend\models\Tool;
use backend\services\MachineService;
use common\models\DataServerList;
use common\models\game\base\GameBase;
use common\models\game\DataErrorCode;
use common\models\game\DataServerLists;
use common\models\game\FivepkPlayerInfo;
use common\models\game\I18nVersion;
use common\models\game\snow_leopard\BaoMachine;
use common\models\RoomPath;
use Yii;
use common\models\DataGameListInfo;
use common\models\game\FivepkNotice;
use common\models\BackendVersion;
use common\models\game\DataFunctionSwitch;
use common\models\game\DataKeyValuePairs;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;


/**
 * @desc 机台管理
 * @package backend\controllers
 */
class MachineController extends MyController
{
    /**
     * @desc 机台列表
     * @param string $type
     * @param null $orderby
     * @return string
     */
    public function actionIndex()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['level'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->get['gameName'];
            $level    = $this->get['level'];

            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];

            $room   = $this->DataRoomInfoList->findByGameIndex($gameType, $level);
            $roomId = $room['id'];

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $seoModelObj = $GameObj->getModelMachine();

            //获取这个房间下的所有机台
            $models = $seoModelObj->findsByRoomId($roomId);

            //获取所有的用户id
            $accountIds  = array_column($models, 'operator');
            $accountIds  = array_unique($accountIds);
            $accountObjs = $this->Account->finds($accountIds);
            foreach ($models as $key => $model) {
                $models[$key]['roomList'] = $room;
                $models[$key]['nickName'] = "";
                foreach ($accountObjs as $v) {
                    if ($v['id'] == $model['operator']) {
                        $models[$key]['nickName'] = $v['name'];
                    }
                }
                if (isset($model['create_time'])) {
                    $models[$key]['create_time'] = date("Y-m-d H:i:s", $model['create_time'] / 1000);
                    $models[$key]['create_date'] = $models[$key]['create_time'];
                }
            }

            $this->setData($models);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }

    /**
     *   创建机台
     *  type   游戏类型
     *  level  房间等级
     *  number 创建数量
     */
    public function actionMachineCreate()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['level']) || !isset($this->get['number'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->get['gameName'];
            $level    = $this->get['level'];
            $number   = $this->get['number'];
            //开启事务
            $tr          = Yii::$app->db->beginTransaction();
            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);

            $gameType = $GameObj->gameType;
            $roomObj  = $this->DataRoomInfoList->findByGameIndex($gameType, $level);
            $roomId   = $roomObj['id'];

            $seoModelObj = $GameObj->getModelMachine();

            //获取房间机台最大的order_id号
            $maxSeoObj = $seoModelObj->findRoomMaxOrderId($roomId);
            $num       = 1;
            if (Tool::isIssetEmpty($maxSeoObj['seo_machine_id'])) {
                $machine_arr[0] = $roomObj['seo_machine_id'];
            } else {
                $machine_arr = explode('-', $maxSeoObj['seo_machine_id']);
                $num         = $maxSeoObj['order_id'] + 1;
            }


            //根据房间id获取礼物信息
            $gift = $this->DataGiftScoreSettings->findByRoomId($roomId);

            for ($i = 0; $i < $number; $i++) {
                $machine_id = $machine_arr[0] . '-' . $num;
                $data       = [
                    'gameType'       => $gameType,
                    'seoMachineId'   => $machine_id,
                    'roomInfoListId' => $roomId,
                    'orderId'        => $num,
                    'gift'           => !empty($gift) ? $gift['init_gift'] : 0,
                    'operator'       => $this->loginId,
                ];
                if ($gameName == Yii::$app->params['star97']) {
                    $data['gift'] = !empty($gift) ? $gift->init_gift : 0;
                }
                $num++;
                $this->remoteInterface->createMachine($data);
            }
            $tr->commit();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }

    /**
     * 删除机台
     * @throws \yii\db\Exception
     */
    public function actionMachineDelete()
    {
        try {
            if (!isset($this->get['ids']) || !isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $ids      = $this->get['ids'];
            $gameName = $this->get['gameName'];
            Factory::MachineController()->deleteMachine($gameName, $ids);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 游戏列表
     * @return string
     */
    public function actionGameList()
    {
        $objs = $this->DataGameListInfo->tableList();
        $this->setData($objs);
        $this->sendJson();
    }

    /**
     * @desc 获取开启的游戏列表
     * @return string
     */
    public function actionGetOpenGame()
    {
        $data = $this->DataGameListInfo->getOpenGame();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * @desc 获取开启的游戏列表
     * @return string
     */
    public function actionGetOpenService()
    {
        $DataServerListModel = new DataServerList();
        $data                = $DataServerListModel->getServiceList();
        //获取所有服务器
        $DataGameListInfoModel = new DataGameListInfo();
        $DataGameListInfos = $DataGameListInfoModel->tableList();
        foreach ($data as $key=>$val){
            $data[$key]['game_switch'] = 1;
            foreach ($DataGameListInfos as $game)
                if( $data[$key]['gameType'] == $game['game_number'] )
                {
                    $data[$key]['game_switch'] = $game['game_switch'];
                }
        }
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * @desc 获取开启的游戏列表
     * @return string
     */
    public function actionGetFgOpenGame()
    {
        $data = $this->DataGameListInfo->getFgOpenGame();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * 游戏信息详情
     * $id 主键id
     */
    public function actionGameView()
    {
        try {
            if (!isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $model = $this->DataGameListInfo->findBase($this->get['id']);
            if (empty($model)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            } else {
                $this->setData($model);
                $this->sendJson();
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 游戏信息更新
     * @return string
     */
    public function actionGameUpdate()
    {
        try {
            $post = $this->post;
            if (!isset($post['game_name']) ||
                !isset($post['score']) ||
                !isset($post['coin']) ||
                !isset($post['game_notice']) ||
                !isset($post['game_res_url']) ||
                !isset($post['game_white_ip']) ||
                !isset($post['game_server_ip']) ||
                !isset($post['game_server_port']) ||
                !isset($post['game_index']) ||
                !isset($post['game_version']) ||
                !isset($post['game_version_id']) ||
                !isset($post['activity_switch']) ||
                !isset($post['sort']) ||
                !isset($post['id'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id                           = $post['id'];
            $postData                     = array();
            $postData['game_name']        = $post['game_name'];
            $postData['score']            = $post['score'];
            $postData['coin']             = $post['coin'];
            $postData['game_notice']      = $post['game_notice'];
            $postData['game_res_url']     = $post['game_res_url'];
            $postData['game_white_ip']    = $post['game_white_ip'];
            $postData['game_server_ip']   = $post['game_server_ip'];
            $postData['game_server_port'] = $post['game_server_port'];
            $postData['game_index']       = $post['game_index'];
            $postData['activity_switch']  = $post['activity_switch'];
            $postData['game_version']     = $post['game_version'];
            $postData['game_version_id']  = $post['game_version_id'];
            $postData['sort']             = $post['sort'];
            $DataGameListInfoObj          = DataGameListInfo::findOne($id);
            $DataGameListInfoObj->add($postData);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }

    /**
     * @desc 游戏信息更新
     * @return string
     */
    public function actionGameResourceUpdate()
    {
        try {
            $post = $this->post;
            if (
                !isset($post['game_res_url']) ||
                !isset($post['game_server_ip']) ||
                !isset($post['game_server_port']) ||
                !isset($post['game_version_id'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id                           = 0;
            $postData['game_res_url']     = $post['game_res_url'];
            $postData['game_server_ip']   = $post['game_server_ip'];
            $postData['game_server_port'] = $post['game_server_port'];
            $postData['game_version_id']  = $post['game_version_id'];
            $DataGameListInfoObj          = DataGameListInfo::findOne($id);
            $DataGameListInfoObj->add($postData);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }

    /**
     *   游戏开关服
     */
    public function actionGameSwitchUpdate()
    {
        Tool::checkParam(['gameType', 'gameSwitch'], $this->post);
        $switch                = $this->post['gameSwitch'];
        $gameTypeStr           = $this->post['gameType'];
        $gameTypeArr           = explode(",", $gameTypeStr);
        $DataGameListInfoModel = new DataGameListInfo();
        $remoteInterfaceObj    = new remoteInterface();
        foreach ($gameTypeArr as $gameType) {
            //获取这个游戏未修改之前的状态
            $DataGameListInfoArr = $DataGameListInfoModel->findByGameType($gameType);
            $DataGameListInfoObj = $DataGameListInfoModel::findOne($DataGameListInfoArr['id']);
            //某一个游戏的开关 发生变化
            if ($DataGameListInfoObj->game_switch != $switch) {
                if (in_array($DataGameListInfoObj->game_number, Yii::$app->params['hasServerGameType'])) {
                    $postData['game_switch']    = $switch;
                    //代表大厅的开关发生了改变
                    if ($switch == $DataGameListInfoObj->gameSwitchOpen) {
                        //表示服务器状态由关到开
                        $postData['last_open_time'] = time();
                        $last_close_time            = $DataGameListInfoObj['last_close_time'];
                        $DataGameListInfoObj->add($postData);
                        if( $gameType == 0){
                            $remoteInterfaceObj->setReservationTime($postData['last_open_time'] - $last_close_time);
                        }
                    } else {
                        //表示服务器状态由开到关
                        $postData['last_close_time'] = time();
                        $DataGameListInfoObj->add($postData);
                    }
                    //开关服雪豹机台数据要恢复
                    BaoMachine::lightWinRecord($switch);
                    //通知java服务器服务器状态改变了
                    $remoteInterfaceObj->serverStatusUpdate(['gameType' => $gameType, 'gameSwitch' => $switch], $gameType);
                }
            }
        }
        $this->sendJson();
    }

    /**
     * @desc 系统公告
     * @return string
     */
    public function actionNotice()
    {
        $data = $this->FivepkNotice->getList();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * @desc 消息详情
     * @param $id
     * @return string
     */
    public function actionNoticeView($id)
    {
        $data = $this->FivepkNotice->findBase($id);
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * 发送系统公告
     * string
     */
    public function actionNoticeSend()
    {
        try {
            if (!isset($this->post['times']) || !isset($this->post['second']) || !isset($this->post['notice'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $times  = $this->post['times'];
            $second = $this->post['second'];
            $notice = trim($this->post['notice']);
            $id     = isset($this->post['id']) ? $this->post['id'] : "";
            if (empty($id)) {
                $model = $this->FivepkNotice;
            } else {
                $model = FivepkNotice::findOne($id);
            }
            $time             = $this->time;
            $FivepkNoticeData = array(
                'notice' => $notice,
                'time'   => date('Y-m-d H:i:s', $time)
            );
            $data             = $model->add($FivepkNoticeData);
            //推送广告添加到队列里面
            $reidskey = "FivepkNotice";
            for ($i = 1; $i <= $times; $i++) {
                $gatherData = array(
                    'id'     => uniqid($i),
                    'url'    => Yii::$app->params['url'] . "/notice?notice=" . urlencode($notice) . "&type=2",
                    'notice' => $notice,
                    'time'   => $time
                );
                //因为这里的数据不能删除，所以放在第一个库
                $this->MyRedis->ZADD($reidskey, $time, json_encode($gatherData));
                $time += $second;
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    public function actionQueue()
    {
        system(__DIR__ . '/../web/shell/listen.sh');
        return true;
    }

    /**
     *   获取所有未完成的公告
     */
    public function actionNoticeNoComplete()
    {
        $reidskey = "FivepkNotice";
        //因为这里的数据不能删除，所以放在第一个库
        $myRedis = new MyRedis();
        $datas   = $myRedis->ZRANGE($reidskey, 1, -1);
        $data    = array();
        foreach ($datas as $val) {
            $val         = json_decode($val, true);
            $val['time'] = date("Y-m-d H:i:s", $val['time']);
            array_push($data, $val);
        }
        $this->setData($data);
        $this->sendJson();
    }

    public function actionNoticeClear()
    {
        $reidskey = "FivepkNotice";
        //因为这里的数据不能删除，所以放在第一个库
        $myRedis = new MyRedis();
        $myRedis->ZREMRANGEBYSCORE($reidskey, 1, 9999999999999999);
        $this->sendJson();
    }

    /**
     * @desc 删除公告
     * @param $id
     * @return string
     */
    public function actionNoticeDelete($id)
    {
        $this->FivepkNotice->del($id);
        $this->sendJson();
    }

    /**
     * @desc 刷新
     */
    public function actionRefresh()
    {
        $gameName = isset($this->get['gameName']) ? $this->get['gameName'] : "";
        $gameType = 0;
        if (!empty($gameName)) {
            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $gameType    = $GameObj->gameType;
        }

        $this->remoteInterface->refreshMachine($gameType);
        $this->sendJson();
    }

    /**
     * @desc 游戏缓存刷新 -- 金鑫
     */
    public function actionRefreshGameCache()
    {
        $gameName = isset($this->get['gameName']) ? $this->get['gameName'] : "";
        $gameType = 0;
        if (!empty($gameName)) {
            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $gameType    = $GameObj->gameType;
        }

        $this->remoteInterface->refreshGameCache($gameType);
        $this->sendJson();
    }

    /**
     * @desc 玩家下线
     * @return string
     */
    public function actionOffOnline()
    {
        Tool::checkParam(['gameType'], $this->get);
        $gameTypeStr        = $this->get['gameType'];
        $gameTypeArr        = explode(",", $gameTypeStr);
        $remoteInterfaceObj = new remoteInterface();
        foreach ($gameTypeArr as $gameType) {
            $remoteInterfaceObj->offOnline($gameType);
        }
        $this->sendJson();
    }

    /**
     * @desc 清除数据
     * @return string
     */
    public function actionRemoveData()
    {
        try {
            $id                  = 0;
            $DataGameListInfoObj = DataGameListInfo::findOne($id);
            if (empty($DataGameListInfoObj)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            }
            $last_close_time = $DataGameListInfoObj['last_close_time'] * 1000;
            $gameClear       = new \common\models\game\GameClear();
            $gameClear->deleteTestData($last_close_time, 'XO');
            $gameClear = new \common\models\game\GameClear();
            $gameClear->deleteTestData($last_close_time, 'XL');
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 清除全部大奖榜
     * @return string
     */
    public function actionRemoveBigRank()
    {
        $gameClear = new \common\models\game\GameClear();
        $gameClear->deleteBigRankData();
        $this->sendJson();
    }


    /**
     *  后台版本修改
     * @return string
     */
    public function actionBackendVersion()
    {
        try {
            if (!isset($this->post['name']) || !isset($this->post['version'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $name               = $this->post['name'];
            $version            = $this->post['version'];
            $model              = BackendVersion::find()->orderBy('id ASC')->one();
            $BackendVersionData = array(
                'name'    => $name,
                'version' => $version
            );
            $data               = $model->add($BackendVersionData);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  后台版本详情
     * @return string
     */
    public function actionBackendVersionView()
    {
        $data = BackendVersion::find()->asArray()->one();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *  后台版本更新
     * @return string
     */
    public function actionBackendVersionUpdate()
    {
        $version = $this->BackendVersion->VersionPlus();
        $this->setData($version);
        $this->sendJson();
    }

    /**
     *  开关管理列表
     */
    public function actionSwitch()
    {
        $data = $this->DataFunctionSwitch->tableList();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * 开关管理详情
     * @param $id
     * @return false|string
     */
    public function actionSwitchView($id)
    {
        try {
            $data = $this->DataFunctionSwitch->findBase($id);
            if (empty($data)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            } else {
                $this->setData($data);
                $this->sendJson();
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  开关管理修改
     */
    public function actionSwitchUpdate()
    {
        try {
            if (!isset($this->post['id']) || !isset($this->post['switchs']) || !isset($this->post['comments'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id       = $this->post['id'];
            $switchs  = $this->post['switchs'];
            $comments = $this->post['comments'];
            $obj      = DataFunctionSwitch::findOne($id);
            $data     = array(
                'switchs'  => $switchs,
                'comments' => $comments,
            );
            $obj      = $obj->add($data);
            $this->setData($obj);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  公共配置列表
     */
    public function actionPublicConfig()
    {
        try {
            if (!isset($this->get['gameType'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $data = $this->DataKeyValuePairs->tableList($this->get['gameType']);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 公共配置详情
     * @param $id
     * @return false|string
     */
    public function actionPublicConfigView($id)
    {
        try {
            $data = $this->DataKeyValuePairs->findBase($id);
            if (empty($data)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            } else {
                $this->setData($data);
                $this->sendJson();
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改公共配置
     * id主键id
     * int 表value_int
     * varchar表varchar
     */
    public function actionPublicConfigUpdate()
    {
        try {
            if (!isset($this->post['id']) || !isset($this->post['int']) || !isset($this->post['varchar'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id      = $this->post['id'];
            $int     = $this->post['int'];
            $varchar = $this->post['varchar'];
            $obj     = DataKeyValuePairs::findOne($id);
            $data    = array(
                'value_int'     => $int,
                'value_varchar' => $varchar,
            );
            $obj     = $obj->add($data);
            $this->setData($obj);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * @desc 修改房间机率
     * @return string|\yii\web\Response
     */
    public function actionRoomPrize()
    {
        try {
            if (!isset($this->post['gameName']) || !isset($this->post['level'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->post['gameName'];
            $level    = $this->post['level'];
            unset($this->post['gameName']);
            unset($this->post['level']);
            $postData    = $this->post;
            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $RoomModel   = $GameObj->getModelRoom();
            if ($gameName == "PAM" || $gameName == "SBB") {
                foreach ($postData as $key => $val) {
                    $obj = $RoomModel::findOne($key);
                    $obj->add($val);
                }
            } else {
                $obj = $RoomModel->findByLevel($level);
                $obj->add($postData);
            }

            if (in_array($gameName, Yii::$app->params['refreshRoomAwardGame'])) {
                //java服务端要求，修改火凤凰游戏的时候要调用他们的接口
                $data = array(
                    'gameType' => $GameObj->gameType,
                    'roomId'   => $GameObj->gameType . "_" . $level,
                );
                $this->remoteInterface->refreshRoomAward($data);
            }
            $this->sendJson();
            return;


            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
            $roomModel       = Yii::$app->params[$chineseGameName]['roomModel'];
            $roomModelObj    = new $roomModel();
            $roomObj         = $this->DataRoomInfoList->findByGameIndex($gameType, $level);

            //添加房间轨迹
            $before = array();
            $after  = array();
            unset($this->post['gameName']);
            unset($this->post['level']);
            $postData = $this->post;
            if ($gameName == "SBB") {
                $columnNamrArr = array(
                    'room_add_count'  => "累积值",
                    'room_buff_count' => "buff值",
                    'gap'             => "间隔",
                    'gap_random'      => "间隔随机值",
                    'min_bye'         => "随机最小值",
                    'max_bye'         => "随机最大值",
                );

                foreach ($postData as $key => $val) {
                    $model = $roomModelObj::find()->filterWhere(['id' => $key])->one();
                    foreach ($val as $k => $v) {
                        $before[$model['prize_name'] . $columnNamrArr[$k]] = $model->$k;
                        $model->$k                                         = $v;
                        $after[$model['prize_name'] . $columnNamrArr[$k]]  = $model->$k;
                    }
                    $model->save();
                }
            } else if ($gameName == "PAM") {
                $columnNamrArr = array(
                    'room_add_count'     => "累积值",
                    'room_buff_count'    => "buff值",
                    'gap'                => "间隔",
                    'gap_random'         => "间隔随机值",
                    'min_limit'          => "随机最小值",
                    'max_limit'          => "随机最大值",
                    'today_contribution' => "日贡门槛",
                    'total_contribution' => "总贡门槛"
                );

                foreach ($postData as $key => $val) {
                    $model = $roomModelObj::find()->filterWhere(['id' => $key])->one();
                    foreach ($val as $k => $v) {
                        $before[$model['prize_name'] . $columnNamrArr[$k]] = $model->$k;
                        $model->$k                                         = $v;
                        $after[$model['prize_name'] . $columnNamrArr[$k]]  = $model->$k;
                    }
                    $model->save();
                }
            } else {
                $roomColumn = $gameName == "ATT2" ? "room_list_info_id" : "data_room_list_info_id";
                $model      = $roomModelObj::find()->filterWhere([$roomColumn => $roomObj['id']])->one();

                //开启事务
                foreach ($postData as $pkey => $p) {
                    foreach ($model as $rkry => $r) {
                        if ($pkey == $rkry) {
                            $before[$pkey] = $r;
                            $after[$pkey]  = $p;
                        }
                    }
                }

                //修改房间数据
                if (empty($model)) {
                    $this->$roomModelObj->add($postData);
                } else {
                    $model->add($postData);
                }

            }

            if (in_array($gameName, Yii::$app->params['refreshRoomAwardGame'])) {
                //java服务端要求，修改火凤凰游戏的时候要调用他们的接口
                $data = array(
                    'gameType' => $gameType,
                    'roomId'   => $roomObj['id'],
                );
                $this->remoteInterface->refreshRoomAward($data);
            }
            RoomPath::Create($roomObj['seo_machine_id'], json_encode($before), json_encode($after), $this->loginId);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 房间机率详情
     * @return string|\yii\web\Response
     */
    public function actionRoomPrizeView()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['level'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName    = $this->get['gameName'];
            $level       = $this->get['level'];
            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $RoomModel   = $GameObj->getModelRoom();
            $data        = $RoomModel->findByLevel($level, 'array');
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 房间喜从天降机率详情
     * @return string|\yii\web\Response
     */
    public function actionBestBetView()
    {
        try {
            if (!isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName  = $this->get['gameName'];
            $room_type = 'TY';
            $data      = $this->BestBet->LoadData($gameName, $room_type);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 房间喜从天降机率修改
     * @return string|\yii\web\Response
     */
    public function actionBestBet()
    {
        try {
            if (!isset($this->post['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName  = $this->post['gameName'];
            $room_type = 'TY';
            $postData  = $this->post;
            $data      = $this->BestBet->saveData($gameName, $room_type, $postData);
            $this->remoteInterface->refreshMachine();
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  通过游戏名称获取房间列表
     */
    public function actionGetRoomListByGameName()
    {
        try {
            if (!isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName    = $this->get['gameName'];
            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $data        = $GameObj->getRoomList();
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   根据游戏名称获取机台列表
     */
    public function actionGetMachineListByGameName()
    {
        try {
            if (!isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->get['gameName'];

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $seoModelObj = $GameObj->getModelMachine();
            $data        = $seoModelObj->tableList();
            //由于机台数量过多造成传输数据大,这里只穿需要的字段
            $needColumn = ['auto_id', 'seo_machine_id'];
            foreach ($data as $key => $val) {
                foreach ($val as $k => $v) {
                    if (!in_array($k, $needColumn)) {
                        unset($data[$key][$k]);
                    }
                }
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 根据游戏和level获得房间信息
     */
    public function actionGetRoomByGameNameAndRoomLevel()
    {
        try {
            if (!isset($this->get['gameName']) && !isset($this->get['level'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName        = $this->get['gameName'];
            $level           = $this->get['level'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
            $data            = $this->DataRoomInfoList->findByGameIndex($gameType, $level);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *    java端使用的错误码
     */
    public function actionErrorCodeList()
    {
        $errorCode = isset($this->get['errorCode']) ? $this->get['errorCode'] : "";
        $comment   = isset($this->get['comment']) ? $this->get['comment'] : "";
        $obj       = new DataErrorCode();
        $data      = $obj->tableList();
        foreach ($data as $key => $val) {
            if ($errorCode != "" && (strpos($val['error_code'], $errorCode) === false)) {
                unset($data[$key]);
            }
            if ($comment != "" && (strpos($val['comment'], $comment) === false)) {
                unset($data[$key]);
            }
        }

        $this->setData(array_values($data));
        $this->sendJson();
    }

    /**
     *   java端使用的错误码--新增修改
     */
    public function actionErrorCodeAdd()
    {
        try {
            if (!isset($this->post['id']) && !isset($this->post['comment'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id       = $this->post['id'];
            $comment  = $this->post['comment'];
            $postData = array(
                'comment' => $comment
            );
            $obj      = DataErrorCode::findOne($id);
            $obj->add($postData);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionGameVersions()
    {
        try {
            $id_parent = isset($this->get['id_parent']) ? $this->get['id_parent'] : 0;
            $obj       = I18nVersion::find()->filterWhere(['id_parent' => $id_parent])->asArray()->all();
            $this->setData($obj);
            $this->sendJson();
        } catch (\Exception $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionGameVersionAdd()
    {
        try {
            foreach ($this->post as $key => $value) {
                if (!isset($value['id_parent']) && empty($value['id_parent'])) {
                    $obj = I18nVersion::findOne($key);
                    $obj->upd($value);
                } else {
                    if (empty($value['name']) || empty($value['version'])) {
                        continue;
                    }
                    $obj = new I18nVersion();
                    $obj->add(['I18nVersion' => $value]);
                }
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionGameVersionDel()
    {
        try {
            if (!isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $obj = new I18nVersion();
            $obj->del($this->get['id']);
            $this->setData(['id' => $this->get['id']]);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionServerLists()
    {
        try {
            $obj = DataServerLists::find()->asArray()->all();
            $this->setData($obj);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionServerAdd()
    {
        try {
            foreach ($this->post as $key => $value) {
                $obj = DataServerLists::findOne($key);
                if (!empty($obj)) {
                    $obj->upd(['DataServerLists' => $value]);
                } else {
                    if (empty($value['server_name'])) {
                        continue;
                    }
                    $obj = new DataServerLists();
                    $obj->add(['DataServerLists' => $value]);
                }
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionServerDel()
    {
        try {
            if (!isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $obj = new DataServerLists();
            $obj->del($this->get['id']);
            $this->setData(['id' => $this->get['id']]);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   清除留机
     */
    public function actionClearReservation()
    {
        try {
            Tool::checkParam(['gameName', 'autoId'], $this->get);
            $gameName     = $this->get['gameName'];
            $autoId       = $this->get['autoId'];
            $gameBase     = new GameBase();
            $GameBaseObj  = $gameBase->initGameObj($gameName);
            $machineModel = $GameBaseObj->getModelMachine();
            $machineObj   = $machineModel::findOne($autoId);
            if (empty($machineObj)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            }
            //如果这台机子的留机到达的时间大于当前系统时间
            if (time() > strtotime($machineObj->reservation_date) && $machineObj->seo_machine_type == 2) {
                //开启事务
                $tr        = Yii::$app->game_db->beginTransaction();
                $accountId = $machineObj->account_id;

                //修复这个人的卡机台状态
                $FivepkPlayerInfoModel = new FivepkPlayerInfo();
                $FivepkPlayerInfoObj   = $FivepkPlayerInfoModel::findOne($accountId);
                if (!empty($FivepkPlayerInfoObj)) {
                    $FivepkPlayerInfoObj->reservation_machine_id = "";
                    $FivepkPlayerInfoObj->save();
                }

                //修改这个机台的卡机台状态
                $machineObj->seo_machine_type = 0;
                $machineObj->account_id       = 0;
                $machineObj->save();
                $tr->commit();
            } else {
                throw new MyException(ErrorCode::ERROR_MACHINE_RESERVATION);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

}