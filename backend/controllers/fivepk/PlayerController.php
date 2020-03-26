<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-5
 * Time: 18:27
 */

namespace backend\controllers\fivepk;

use backend\models\Factory;
use backend\models\OldPlayerSwitch;
use backend\models\remoteInterface\remoteInterface;
use backend\models\Tool;
use common\models\Attention;
use common\models\game\att2\FivepkPlayerAtt2CardtypeandvalueDefault;
use common\models\game\base\GameBase;
use common\models\game\DataFivepkNewerWinType;
use common\models\game\DataKeyValuePairs;
use common\models\game\firephoenix\HfhOdds;
use common\models\game\FivepkMailReport;
use common\models\odds\OddsBao;
use common\models\odds\OddsDzb;
use common\models\odds\OddsHfh;
use common\models\pay\platform\PayLayerAccount;
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
use \common\models\GlobalConfig;

class PlayerController extends MyController
{
    public function actionGetPlayerListByNick()
    {
        try {
            if (!isset($this->get['nick'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $nick    = $this->get['nick'];
            $results = $this->FivepkPlayerInfo->getPlayerListByNick($nick);
            $this->setData($results);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   获取在线玩家信息
     */
    public function actionUserOnlineInfo()
    {
        $popCodeArr         = $this->Account->findAllSonPopCode($this->loginId);
        $FivepkAccountModel = new FivepkAccount();
        $data               = $FivepkAccountModel->getOnlinePlayer($popCodeArr);
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * @desc 玩家列表
     * @url GET pnrs
     * @return int id
     */
    public function actionIndex()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $param['pageNo']         = $this->get['pageNo'];
            $param['pageSize']       = $this->get['pageSize'];
            $param['popCode']        = isset($this->get['popCode']) ? $this->get['popCode'] : "";
            $param['accountId']      = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $param['loginSystem']    = isset($this->get['loginSystem']) ? $this->get['loginSystem'] : "";
            $param['phone']          = isset($this->get['phone']) ? $this->get['phone'] : "";
            $param['udid']           = isset($this->get['udid']) ? $this->get['udid'] : "";
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
            $data                    = $this->platform(__FUNCTION__, [$param]);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * @desc 老玩家列表
     */
    public function actionIndexOld()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $param['pageNo']        = $this->get['pageNo'];
            $param['pageSize']      = $this->get['pageSize'];
            $param['popCode']       = isset($this->get['popCode']) ? $this->get['popCode'] : "";
            $param['accountId']     = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $param['name']          = isset($this->get['name']) ? $this->get['name'] : "";
            $param['machine']       = isset($this->get['machine']) ? $this->get['machine'] : "";
            $param['gameName']      = isset($this->get['gameName']) ? $this->get['gameName'] : "";
            $param['register_time'] = isset($this->get['register_time']) ? $this->get['register_time'] : "";
            $param['stime']         = isset($this->get['stime']) ? $this->get['stime'] : "";
            $param['etime']         = isset($this->get['etime']) ? $this->get['etime'] . " 23:59:59" : "";
            $param['sort']          = isset($this->get['sort']) ? $this->get['sort'] : "";
            $param['sortType']      = isset($this->get['sortType']) ? strtolower($this->get['sortType']) : "desc";
            $param['loginId']       = $this->loginId;
            $data                   = Factory::PlayerController()->actionIndexOld($param);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  修改用户的层级，只支持这一个字段的修改
     */
    public function actionUpdatePayLayer()
    {
        Tool::checkParam(['accountId', 'payLayer'], $this->post);
        $accountId          = $this->post['accountId'];
        $payLayer           = $this->post['payLayer'];
        $FivepkAccountModel = new FivepkAccount();
        $FivepkAccountModel->updatePayLayer($accountId, $payLayer);

    }

    /**
     * @desc 修改老玩家开关
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionUserSwitch()
    {
        try {
            if (!isset($this->get['accountId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId    = $this->get['accountId'];
            $accountIdArr = explode(",", $accountId);

            $getData = $this->get;

            //获取所有的开启的游戏
            $openGames = $this->DataGameListInfo->getOpenGame();
            //修改playerInfo的字段
            $FivepkPlayerInfoGameSwitch = array();
            //存放可以修改的游戏的开关
            $games = array();
            foreach ($openGames as $game) {
                foreach ($getData as $key => $get) {
                    $chineseGameName = $game['game_name'];
                    $gameName        = Yii::$app->params[$chineseGameName]['short'];
                    $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
                    if (!isset(Yii::$app->params[$chineseGameName]['playerInfoSwitchColumn'])) {
                        continue;
                    }
                    $playerInfoSwitchColumn = Yii::$app->params[$chineseGameName]['playerInfoSwitchColumn'];
                    if ($gameName == $key) {
                        $games[$gameType]                                    = $get;
                        $FivepkPlayerInfoGameSwitch[$playerInfoSwitchColumn] = $get;
                    }

                    if ($gameType == 10) {
                        //为了防止开老玩家，而且老玩家没有数据，必须先检测是否有数据，没有数据就插入数据
                        $userOdds      = Yii::$app->params[$chineseGameName]['userOddsModel'];
                        $userOddsModel = new $userOdds();
                        foreach ($accountIdArr as $val) {
                            $data = $userOddsModel->getPlayerOdds($val);
                            if (empty($data)) {
                                $userOddsModel->initUserOdds($val);
                            }
                        }
                    }

                }
            }
            //开启事务
            $tr = Yii::$app->game_db->beginTransaction();
            if (empty($games)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $loginId            = $this->loginId;
            $popCodeArr         = $this->Account->findAllSonPopCode($loginId);
            $FivepkAccountModel = new FivepkAccount();
            $player_result      = $FivepkAccountModel->getOnlinePlayer($popCodeArr);
            foreach ($player_result['status'] as $key => $status) {
                if (strpos($status, '连庄') !== false && $accountId == $key) {
                    throw new MyException("当前玩家正在 连庄");
                }
            }
            FivepkPlayerInfo::updateAll($FivepkPlayerInfoGameSwitch, ['in', 'account_id', $accountIdArr]);
            foreach ($games as $key => $switch) {
                $this->OldPlayerSwitch->updateByAccountIds($accountIdArr, $key, $switch);
            }
            $tr->commit();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 修改Fg游戏开关
     */
    public function actionFgSwitch()
    {
        Tool::checkParam(['accountId', 'fg'], $this->post);
        $fg           = $this->post['fg'];
        $accountId    = $this->post['accountId'];
        $accountIdArr = explode(",", $accountId);
        FivepkPlayerInfo::updateAll(['prefab_jail_fg' => $fg], ['in', 'account_id', $accountIdArr]);
        $this->sendJson();
    }

    /**
     * @desc 或有新老玩家开关时间
     */
    public function actionUserSwitchTime()
    {
        Tool::checkParam(['accountId', 'gameName'], $this->get);
        $gameName    = $this->get['gameName'];
        $accountId   = $this->get['accountId'];
        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $OddsModel   = $GameObj->getModelOdds();

        $OldPlayerSwitchModel = new OldPlayerSwitch();
        $obj                  = $OldPlayerSwitchModel->findByAccountGame($accountId, $GameObj->gameType);
        $time                 = "";
        if (!empty($obj)) {
            $time = $obj['open_time'] == 0 ? date("Y-m-d H:i:s", $obj['close_time']) : date("Y-m-d H:i:s", $obj['open_time']);
        }
        $rs = array();

        $DataKeyValuePairsObj = DataKeyValuePairs::findOne(22);
        $rs['公共配置新玩家局数'] = $DataKeyValuePairsObj->value_int;
        $rs['开启时间'] = $time;
        $playerTimes = 0;
        if( !empty($OddsModel) ){
            $OddsObj = $OddsModel::find()
                ->andWhere(['=', 'odds_type', 1] )
                ->andWhere(['=', 'odds_type_id', $accountId])
                ->one();
            if( !empty($OddsObj)){
                $playerTimes = $OddsObj['seo_machine_play_count'];
            }
        }
        $rs[$GameObj->chineseGameName . '新玩家局数'] = $playerTimes;
        $this->setData($rs);
        $this->sendJson();
    }


    /**
     * @desc 获取老玩家机率
     */
    public function actionUserGetOdds()
    {
        try {
            if (!isset($this->get['accountId']) || !isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId       = $this->get['accountId'];
            $gameName        = $this->get['gameName'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $userOdds        = Yii::$app->params[$chineseGameName]['userOddsModel'];
            $userOddsModel   = new $userOdds();
            $data            = $userOddsModel->getPlayerOdds($accountId);
            if (empty($data)) {
                $userOddsModel->initUserOdds($accountId);
                $data = $userOddsModel->getPlayerOdds($accountId);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 修改老玩家机率
     */
    public function actionUserUpdateOdds()
    {
        try {
            if (!isset($this->post['accountIds']) || !isset($this->post['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountIds = $this->post['accountIds'];
            $gameName   = $this->post['gameName'];
            if (!isset(Yii::$app->params['game'][$gameName])) {
                throw new MyException(ErrorCode::ERROR_GAME_NOT_EXIST);
            }
            unset($this->post['accountIds']);
            unset($this->post['gameName']);
            $postData   = $this->post;
            $accountIds = explode(',', $accountIds);

            $chineseGameName    = Yii::$app->params['game'][$gameName];
            $gameType           = Yii::$app->params[$chineseGameName]['gameType'];
            $userOddsModel      = Yii::$app->params[$chineseGameName]['userOddsModel'];
            $userOddsEmptyModel = new $userOddsModel();

            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            foreach ($accountIds as $accountId) {
                $PlayerIsChangeObj = PlayerIsChange::findOne(['account_id' => $accountId, 'game_type' => $gameType]);
                if (empty($PlayerIsChangeObj)) {
                    $PlayerIsChangeObj = new PlayerIsChange();
                    $data              = array(
                        'game_type'  => $gameType,
                        'account_id' => $accountId,
                        'column'     => json_encode($postData),
                    );
                } else {
                    $oldColumn = json_decode($PlayerIsChangeObj->column, true);
                    $newColumn = array_merge($postData, $oldColumn);
                    $data      = array(
                        'column' => $newColumn = json_encode($newColumn)
                    );
                }
                $PlayerIsChangeObj->add($data);
            }

            $userOddsEmptyModel->updatePlayerOdds($postData, $accountIds);
            $tr->commit();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  初始化所有的用户的机率
     */
    public function actionInitUserAllOdds()
    {
        if (!isset($this->get['gameName'])) {
            throw new MyException(ErrorCode::ERROR_PARAM);
        }
        $gameName         = $this->get['gameName'];
        $chineseGameName  = Yii::$app->params['game'][$gameName];
        $userOddsModel    = Yii::$app->params[$chineseGameName]['userOddsModel'];
        $userOddsModelObj = new $userOddsModel();
        //修改所有用户的默认机率
        $userOddsModelObj->initUserAllOdds();
        $this->sendJson();
    }

    /**
     * @desc 修改所有玩家机率
     */
    public function actionUserUpdateAllOdds()
    {
        try {
            if (!isset($this->post['gameName']) || !isset($this->post['type'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->post['gameName'];
            if (!isset(Yii::$app->params['game'][$gameName])) {
                throw new MyException(ErrorCode::ERROR_GAME_NOT_EXIST);
            }
            $type = $this->post['type'];
            unset($this->post['gameName']);
            unset($this->post['type']);
            $postData = $this->post;
            //开启事务
            $tr           = Yii::$app->db->beginTransaction();
            $accountIdArr = array();
            if ($type == 2) {
                //不覆盖，获取一级修改过机率玩家的id
                $accountIdArr = $this->PlayerIsChange->getIds();
            }
            //清空PlayerIsChange
            $this->PlayerIsChange->clearTable();
            //修改游戏机率表
            $chineseGameName  = Yii::$app->params['game'][$gameName];
            $userOddsModel    = Yii::$app->params[$chineseGameName]['userOddsModel'];
            $userOddsModelObj = new $userOddsModel();
            //修改所有用户的默认机率
            $userOddsModelObj->updateAllUserOdds($postData, $accountIdArr);
            $tr->commit();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   获取已经修改机率的玩家列表
     */
    public function actionAllUpdateOddsUser()
    {
        try {
            if (!isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName        = $this->get['gameName'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
            $objs            = $this->PlayerIsChange->findByGameType($gameType);
            $data            = array_column($objs, 'account_id');
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionUserOddsDefaultView()
    {
        try {
            if (!isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName           = $this->get['gameName'];
            $chineseGameName    = Yii::$app->params['game'][$gameName];
            $userOddsDefault    = Yii::$app->params[$chineseGameName]['userOddsDefault'];
            $userOddsDefaultObj = new $userOddsDefault();
            if ($gameName != "PAM") {
                $data = $userOddsDefaultObj::find()->asArray()->one();
                $data = $this->Tool->clearFloatZero($data);
            } else {
                $data = $userOddsDefaultObj::find()->asArray()->all();
                foreach ($data as $key => $val) {
                    $data[$key] = $this->Tool->clearFloatZero($val);
                }
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionUserOddsDefaultUpdate()
    {
        try {
            if (!isset($this->post['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id                 = isset($this->post['id']) ? $this->post['id'] : "";
            $gameName           = $this->post['gameName'];
            $chineseGameName    = Yii::$app->params['game'][$gameName];
            $userOddsDefault    = Yii::$app->params[$chineseGameName]['userOddsDefault'];
            $userOddsDefaultObj = new $userOddsDefault();
            $postData           = $this->post;
            if ($gameName != "PAM") {
                $obj = $userOddsDefaultObj::findOne($id);
                if (empty($obj)) {
                    throw new MyException(ErrorCode::ERROR_OBJ);
                }
                unset($postData['id']);
                unset($postData['gameName']);
                $data = $obj->add($postData);
            } else {
                unset($this->post['gameName']);
                $data = $userOddsDefaultObj->add($this->post);
            }

            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 获取四梅强补值
     * @return string
     */
    public function actionFourKindsView()
    {
        try {
            if (!isset($this->get['accountId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId                 = $this->get['accountId'];
            $FivepkPlayerInfoObj       = $this->FivepkPlayerInfo->findBase($accountId);
            $four_of_a_kind_gift_count = empty($FivepkPlayerInfoObj) ? 0 : $FivepkPlayerInfoObj['four_of_a_kind_gift_count'];
            $this->setData($four_of_a_kind_gift_count);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改四梅强补值
     */
    public function actionFourKindsUpdate()
    {
        try {
            if (!isset($this->get['accountId']) || !isset($this->get['value'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId           = $this->get['accountId'];
            $value               = $this->get['value'];
            $FivepkPlayerInfoObj = FivepkPlayerInfo::findOne($accountId);
            if (empty($FivepkPlayerInfoObj)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            }
            $data = array(
                'four_of_a_kind_gift_count' => $value,
            );
            $FivepkPlayerInfoObj->add($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改老玩家用户名
     */
    public function actionPlayerUpdate()
    {
        try {
            if (!isset($this->post['accountId']) || !isset($this->post['name'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId        = $this->post['accountId'];
            $name             = $this->post['name'];
            $FivepkAccountObj = FivepkAccount::findOne($accountId);
            if (empty($FivepkAccountObj)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            }
            $data = array(
                'name' => $name,
            );

            if (isset($this->post['phoneNumber'])) {
                $data['phone_number'] = $this->post['phoneNumber'];
            }

            $FivepkAccountObj->add($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 玩家详情-日报表月报表
     * @param $id
     * @return string
     */
    public function actionPlayerView()
    {
        try {
            if (!isset($this->get['accountId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId = $this->get['accountId'];

            $loginId    = $this->loginId;
            $popCodeArr = $this->Account->findAllSonPopCode($loginId);
            if (empty($popCodeArr)) {
                $this->sendJson();
                return;
            }
            $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";

            $where     = " fivepk_account.seoid in ($popCodeStrIn)";
            $where     .= " and fivepk_player_info.account_id = '{$accountId}'";
            $orderBy   = "id desc";
            $DayData   = $this->FivepkDayContribution->Page($where, $orderBy, 1, 9999);
            $MonthData = $this->FivepkMonthContribution->Page($where, $orderBy, 1, 9999);
            $data      = array(
                'DayData'   => $DayData,
                'MonthData' => $MonthData
            );
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 玩家详情-游戏报表
     * @param $id
     * @return string
     */
    public function actionPlayerGameView()
    {
        try {
            if (!isset($this->get['accountId']) || !isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId       = $this->get['accountId'];
            $gameName        = $this->get['gameName'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['service'];
            $model           = new $gameType();
            $data            = $model->getPlayerRecord($accountId);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * @desc 体验场玩家列表
     */
    public function actionExperience()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];
            $popCode   = isset($this->get['popCode']) ? $this->get['popCode'] : "";
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $stime     = isset($this->get['stime']) ? $this->get['stime'] : "";
            $etime     = isset($this->get['etime']) ? $this->get['etime'] . " 23:59:59" : "";
            $sort      = isset($this->get['sort']) ? $this->get['sort'] : "";
            $sortType  = isset($this->get['sortType']) ? strtolower($this->get['sortType']) : "desc";

            $pageNo   = $pageNo < 1 ? 1 : $pageNo;
            $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
            $limit    = $pageSize;
            $offset   = ($pageNo - 1) * $pageSize;

            $loginId                = $this->loginId;
            $popCodeArr             = $this->Account->findAllSonPopCode($loginId);
            $FivepkAccountModel     = new FivepkAccount();
            $player_result          = $FivepkAccountModel->getOnlinePlayer($popCodeArr);
            $OnlinePlayerUserArr    = $player_result['arr'];
            $OnlinePlayerUserStatus = $player_result['status'];

            $query = FivepkAccount::find()->joinWith('playerInfo')->filterWhere(['in', 'fivepk_account.account_id', $player_result['arr']])
                ->andFilterWhere(['fivepk_account.seoid' => strtoupper($popCode)])
                ->andFilterWhere(['like', 'fivepk_player_info.seo_machine_id', 'TY'])
                ->andFilterWhere(["fivepk_account.account_id" => $accountId])
                ->andFilterWhere(['between', 'fivepk_account.last_login_time', $stime, $etime]);
            if (!empty($sort)) {
                $query = $query->orderBy($sort . " " . $sortType);
            } else {
                $query = $query->orderBy('fivepk_player_info.seo_machine_id DESC,fivepk_account.last_login_time DESC');
            }
            $models1 = $query->asArray()->all();
            $arr     = [];
            foreach ($models1 as $value) {
                $arr[] = $value['account_id'];
            }
            $player_result['arr'] = $arr;

            $query1 = FivepkAccount::find()->joinWith('playerInfo')->where(['in', 'fivepk_account.seoid', $popCodeArr])
                ->andFilterWhere(['not in', 'fivepk_account.account_id', $player_result['arr']])
                ->andFilterWhere(['fivepk_account.seoid' => strtoupper($popCode)])
                ->andFilterWhere(["fivepk_account.account_id" => $accountId]);
            if (!empty($register_time)) {
                $query1 = $query1->andFilterWhere(['between', 'fivepk_account.create_date', $register_time, date('Y-m-d', strtotime($register_time . '+1 day'))]);
            } else {
                $query1 = $query1->andFilterWhere(['between', 'fivepk_account.last_login_time', $stime, $etime]);
            }

            if (!empty($sort)) {
                $query1 = $query1->orderBy($sort . " " . $sortType);
            } else {
                $query1 = $query1->orderBy('fivepk_account.last_login_time DESC');
            }

            $models2 = $query1
                ->offset($offset)
                ->limit($limit)
                ->asArray()
                ->all();

            $data = array_merge($models1, $models2);
            foreach ($data as $key => $val) {
                if (isset($OnlinePlayerUserStatus[$val['account_id']])) {
                    $data[$key]['gameStaus'] = $OnlinePlayerUserStatus[$val['account_id']];
                } else {
                    $data[$key]['gameStaus'] = "";
                }
                //$data[$key]['address'] = Tool::getIpAddress($val['account_ip']);
                $reservation_machine_id                  = $val['playerInfo']['reservation_machine_id'];
                $seo_machine_id                          = $val['playerInfo']['seo_machine_id'];
                $data[$key]['playerInfo']['playMachine'] = $this->FivepkPlayerInfo->SwitchMachineId($reservation_machine_id, $seo_machine_id);
                $data[$key]['status']                    = $this->FivepkAccount->switchStatus($val['playerInfo']['is_online'], $val['allowed']);
                $data[$key]['create_date']               = mb_substr($val['create_date'], 5);
                $data[$key]['last_login_time']           = mb_substr($val['last_login_time'], 5);
            }

            $account = $query1->count();
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
     *  体验场玩家统计
     */
    public function actionExperienceCount()
    {
        $data = FivepkAccount::getExperienceCount();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * @desc 修改密码
     * @return string
     */
    public function actionUserUpdatePassword()
    {
        try {
            if (!isset($this->post['name']) || !isset($this->post['password'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $name     = $this->post['name'];
            $password = $this->post['password'];
            //密码是否符合规则
            $preg = '/^[_0-9a-z]{6,12}$/i';
            if (!preg_match($preg, $password)) {
                throw new MyException(ErrorCode::ERROR_USER_PWD_FORMAT);
            }
            //账户是否存在
            $accountObj = $this->FivepkAccount->findByAccount($name);
            if (empty($accountObj)) {
                throw new MyException(ErrorCode::ERROR_USER_NOT_EXIST);
            }
            $accountPopCode = $accountObj['seoid'];
            //查看是不是你的下级
            $loginId    = $this->loginId;
            $popCodeArr = $this->Account->findAllSonPopCode($loginId);
            if (!in_array($accountPopCode, $popCodeArr)) {
                throw new MyException(ErrorCode::ERROR_NOT_SON);
            }

            $data     = [
                'account'  => $name,
                'password' => $password,
            ];
            $result   = ToolService::encryptByPublicKey($data);
            $url      = Yii::$app->params['url'] . "/password?data={$result['data']}&sign={$result['sign']}";
            $contents = json_decode(ToolService::curl($url));
            if ($contents->status != 10) {
                throw new MyException(ErrorCode::ERROR_CURL_STATUS);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 玩家下线 和封禁与解封
     * @return string
     */
    public function actionPlayerAllowed()
    {
        Tool::checkParam(['gameType'], $this->get);
        $gameTypeStr   = $this->get['gameType'];
        $gameTypeArr = explode(",",$gameTypeStr);

        $accountIds = isset($this->get['accountIds']) ? $this->get['accountIds'] : "";
        $accountIds = explode(",", $accountIds);
        //踢下线 参数
        $isKicPlayer = isset($this->get['isKicPlayer']) ? $this->get['isKicPlayer'] : 0;
        //封禁参数
        $status = isset($this->get['status']) ? $this->get['status'] : 0;

        $remoteInterfaceObj = new remoteInterface();
        //玩家下线接口
        $params = [
            'accountIds'   => $accountIds,
            'status'       => $status,
            'isKickPlayer' => $isKicPlayer,
        ];

        foreach ($gameTypeArr as $gameType){
            $remoteInterfaceObj->allowed($params, $gameType);
        }
        $this->sendJson();
    }


    /**
     * @desc 明星97玩家列表
     * @url GET pnrs
     * @return int id
     */
    public function actionIndexStar97()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo        = $this->get['pageNo'];
            $pageSize      = $this->get['pageSize'];
            $popCode       = isset($this->get['popCode']) ? $this->get['popCode'] : "";
            $accountId     = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $ip            = isset($this->get['ip']) ? $this->get['ip'] : "";
            $stime         = isset($this->get['stime']) ? $this->get['stime'] : "";
            $etime         = isset($this->get['etime']) ? $this->get['etime'] . " 23:59:59" : "";
            $register_time = isset($this->get['register_time']) ? $this->get['register_time'] : "";
            $sort          = isset($this->get['sort']) ? $this->get['sort'] : "";
            $sortType      = isset($this->get['sortType']) ? strtolower($this->get['sortType']) : "desc";

            $pageNo   = $pageNo < 1 ? 1 : $pageNo;
            $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
            $limit    = $pageSize;
            $offset   = ($pageNo - 1) * $pageSize;

            $loginId    = $this->loginId;
            $popCodeArr = $this->Account->findAllSonPopCode($loginId);

            $FivepkAccountModel     = new FivepkAccount();
            $player_result          = $FivepkAccountModel->getOnlinePlayer($popCodeArr);
            $OnlinePlayerUserArr    = $player_result['arr'];
            $OnlinePlayerUserStatus = $player_result['status'];

            $query = FivepkAccount::find()->joinWith('playerInfo')->where(['in', 'fivepk_account.seoid', $popCodeArr])
                ->andFilterWhere(['fivepk_account.seoid' => strtoupper($popCode)])
                ->andFilterWhere(['fivepk_account.account_id' => $accountId])
                ->andFilterWhere(['fivepk_account.account_ip' => $ip]);

            if (!empty($OnlinePlayerUserArr)) {
                $query->select(['account_status' => 'if(t1.account_id >0 ,10,0)', 'fivepk_player_info.*', 'fivepk_account.*']);
                $query->leftJoin(['t1' => '(' . FivepkAccount::find()->select('account_id')->where(['account_id' => $player_result['arr']])->createCommand()->getRawSql() . ')'], 't1.account_id = fivepk_account.account_id');
            }

            if (!empty($register_time)) {
                $query = $query->andFilterWhere(['between', 'fivepk_account.create_date', $register_time, date('Y-m-d', strtotime($register_time . '+1 day'))]);
            } else {
                $query = $query->andFilterWhere(['between', 'fivepk_player_info.last_enter97_time', $stime, $etime]);
            }
            if (!empty($sort)) {
                $query = $query->orderBy($sort . " " . $sortType);
            } else {
                $orderBy = '';
                if (!empty($player_result['arr'])) {
                    $orderBy = 'account_status DESC,';
                }
                $query = $query->orderBy($orderBy . 'fivepk_player_info.reservation_machine_id DESC,fivepk_account.last_login_time DESC');
            }

            $data    = $query->offset($offset)->limit($limit)->asArray()->all();
            $account = $query->count();
            $page    = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
                'nowPage' => $pageNo
            );


            //获取新人总玩局数数量
            $DataKeyValuePairsObj = $this->DataKeyValuePairs->findByValueName("新人总玩局数");

            foreach ($data as $key => $val) {
                if (isset($OnlinePlayerUserStatus[$val['account_id']])) {
                    $data[$key]['gameStaus'] = $OnlinePlayerUserStatus[$val['account_id']];
                } else {
                    $data[$key]['gameStaus'] = "";
                }
                //$data[$key]['address'] = Tool::getIpAddress($val['account_ip']);
                $reservation_machine_id                  = $val['playerInfo']['reservation_machine_id'];
                $seo_machine_id                          = $val['playerInfo']['seo_machine_id'];
                $data[$key]['playerInfo']['playMachine'] = $this->FivepkPlayerInfo->SwitchMachineId($reservation_machine_id, $seo_machine_id);
                $data[$key]['status']                    = $this->FivepkAccount->switchStatus($val['playerInfo']['is_online'], $val['allowed']);
                if ($data[$key]['playerInfo']['total_play'] > $DataKeyValuePairsObj['value_int'] || $data[$key]['playerInfo']['newer_win_pool'] <= 0) {
                    $data[$key]['cmNewPlayer'] = "否";
                } else {
                    $data[$key]['cmNewPlayer'] = "是";
                }
                $data[$key]['freshAwardCount']             = $val['playerInfo']['star97_newer_current_index'] - 2;
                $data[$key]['star97_newer_interval_count'] = $val['playerInfo']['star97_newer_interval_count'] >= 1 ? $val['playerInfo']['star97_newer_interval_count'] - 1 : 0;
            }

            $this->setData(['data' => $data, 'online' => $player_result]);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  明星97新手总开关
     */
    public function actionIndexStar97RewardSwitch()
    {
        $id  = 1;
        $obj = $this->DataStar97NewerReward->findBase($id);
        $this->setData($obj['is_open']);
        $this->sendJson();
    }

    /**
     *  明星97新手总开关修改
     */
    public function actionIndexStar97RewardSwitchUpdate()
    {
        try {
            if (!isset($this->post['type'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $type = $this->post['type'] == 1 ? 1 : 0;
            $id   = 1;
            $obj  = DataStar97NewerReward::findOne($id);
            $data = array(
                'is_open' => $type
            );
            $rs   = $obj->add($data);
            $this->setData($rs);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97新手玩家开关
     */
    public function actionIndexStar97UserSwitch()
    {
        try {
            if (!isset($this->post['accountIds']) || !isset($this->post['type'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountIds   = $this->post['accountIds'];
            $type         = $this->post['type'] == 1 ? 1 : 0;
            $accountIdArr = explode(",", $accountIds);
            $params       = array('is_star97_newer' => $type);
            $this->FivepkPlayerInfo->updates($accountIdArr, $params);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97新人奖列表
     */
    public function actionStar97NewerRewardList()
    {
        $data = $this->DataStar97NewerReward->tableList();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *  明星97新人奖列表修改
     */
    public function actionStar97NewerRewardUpdate()
    {
        try {
            if (!isset($this->post['id']) || !isset($this->post['total_buff_count'])
                || !isset($this->post['today_contribution_percent']) || !isset($this->post['total_contribution_percent'])
                || !isset($this->post['min_interval_count']) || !isset($this->post['max_interval_count'])
                || !isset($this->post['newer_star97_play_count']) || !isset($this->post['is_open'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id       = $this->post['id'];
            $postData = $this->post;
            $obj      = DataStar97NewerReward::findOne($id);
            $data     = array(
                'total_buff_count'           => $postData['total_buff_count'],
                'today_contribution_percent' => $postData['today_contribution_percent'],
                'total_contribution_percent' => $postData['total_contribution_percent'],
                'min_interval_count'         => $postData['min_interval_count'],
                'max_interval_count'         => $postData['max_interval_count'],
                'newer_star97_play_count'    => $postData['newer_star97_play_count'],
                'is_open'                    => $postData['is_open'],
            );
            $rs       = $obj->add($data);
            $this->setData($rs);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //玩家反馈
    public function actionFeedback()
    {
        try {
            if (!isset($this->get['pageSize']) || !isset($this->get['pageNo'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $popCode   = isset($this->get['popCode']) ? $this->get['popCode'] : "";
            $type      = isset($this->get['type']) ? $this->get['type'] : "";
            $title     = isset($this->get['title']) ? $this->get['title'] : "";
            $status    = isset($this->get['status']) ? $this->get['status'] : "";

            $where = " 1";
            if ($accountId != "") {
                $where .= " and account_id = '{$this->get['accountId']}'";
            }
            if ($popCode != "") {
                $where .= " and seo_id = {$this->get['popCode']}";
            }
            if ($type != "") {
                $where .= " and type = {$this->get['type']}";
            }
            if ($title != "") {
                $where .= " and title like '%{$this->get['title']}%'";
            }
            if ($status != "") {
                $where .= " and is_readed = {$this->get['status']}";
            }
            $pageNo   = $this->get['pageNo'];
            $pageSize = $this->get['pageSize'];

            $data  = $this->FivepkMailReport->page($pageNo, $pageSize, $where);
            $count = $this->FivepkMailReport->pageCount($where);
            $page  = array(
                'account' => $count,
                'maxPage' => ceil($count / $pageSize),
                'nowPage' => $pageNo
            );
            foreach ($data as $key => $val) {
                $FivepkPlayerInfoObj    = FivepkPlayerInfo::findOne($val['account_id']);
                $data[$key]['nickName'] = isset($FivepkPlayerInfoObj->nick_name) ? $FivepkPlayerInfoObj->nick_name : "";
                $data[$key]['type']     = Yii::$app->params['mailReportType'][$data[$key]['type']];
            }
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //玩家反馈--修改
    public function actionFeedbackUpdate()
    {
        try {
            if (!isset($this->post['ids']) || !isset($this->post['comment']) || !isset($this->post['status'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $ids      = $this->post['ids'];
            $ids      = explode(',', $ids);
            $comment  = $this->post['comment'];
            $status   = $this->post['status'];
            $postData = array(
                'comment'   => $comment,
                'is_readed' => $status,
            );

            FivepkMailReport::updateAll($postData, ['id' => $ids]);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //玩家反馈--修改
    public function actionFeedbackDelete()
    {
        try {
            if (!isset($this->post['ids'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $ids = $this->post['ids'];
            $ids = explode(',', $ids);
            FivepkMailReport::DeleteAll(['id' => $ids]);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    //玩家关注
    public function actionAttention()
    {
        try {
            if (!isset($this->get['pageSize']) || !isset($this->get['pageNo'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $operator  = isset($this->get['operator']) ? $this->get['operator'] : "";
            $stime     = isset($this->get['stime']) ? $this->get['stime'] : "";
            $etime     = isset($this->get['etime']) ? $this->get['etime'] : "";
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];

            $where = " 1";
            if (!empty($accountId)) {
                $where .= " and account_id = {$this->get['accountId']}";
            }
            if (!empty($operator)) {
                $where .= " and operator like '%{$this->get['operator']}%'";
            }
            if (!empty($stime)) {
                $where .= " and time >= " . strtotime($this->get['stime']) * 1000;
            }
            if (!empty($etime)) {
                $where .= " and time < " . strtotime($this->get['etime'] . " 23:59:59") * 1000;
            }

            $data  = $this->Attention->page($pageNo, $pageSize, $where);
            $count = $this->Attention->pageCount($where);
            $page  = array(
                'account' => $count,
                'maxPage' => ceil($count / $pageSize),
                'nowPage' => $pageNo
            );
            foreach ($data as $key => $val) {
                $FivepkPlayerInfoObj    = FivepkPlayerInfo::findOne($val['account_id']);
                $data[$key]['nickName'] = isset($FivepkPlayerInfoObj->nick_name) ? $FivepkPlayerInfoObj->nick_name : "";
            }
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //玩家关注求和
    public function actionAttentionSumList()
    {
        $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
        $operator  = isset($this->get['operator']) ? $this->get['operator'] : "";
        $stime     = isset($this->get['stime']) ? $this->get['stime'] : "";
        $etime     = isset($this->get['etime']) ? $this->get['etime'] : "";

        $where = " 1";
        if (!empty($accountId)) {
            $where .= " and account_id = {$this->get['accountId']}";
        }
        if (!empty($operator)) {
            $where .= " and operator like '%{$this->get['operator']}%'";
        }
        if (!empty($stime)) {
            $where .= " and time >= " . strtotime($this->get['stime']) * 1000;
        }
        if (!empty($etime)) {
            $where .= " and time < " . strtotime($this->get['etime'] . " 23:59:59") * 1000;
        }

        $AccountNum = $this->Attention->getAccountNum($where);
        $RewardSum  = $this->Attention->getRewardSum($where);
        $data       = array(
            '关注总人数' => $AccountNum,
            '关注总奖励' => $RewardSum,
        );
        $this->setData($data);
        $this->sendJson();
    }

    //玩家反馈--删除
    public function actionAttentionDelete()
    {
        try {
            if (!isset($this->post['ids'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $ids = $this->post['ids'];
            $ids = explode(',', $ids);
            Attention::DeleteAll(['id' => $ids]);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //玩家反馈--增加
    public function actionAttentionAdd()
    {
        try {
            if (!isset($this->post['accountId']) || !isset($this->post['reward'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId           = $this->post['accountId'];
            $reward              = $this->post['reward'];
            $FivepkPlayerInfoObj = FivepkPlayerInfo::findOne($accountId);
            if (empty($FivepkPlayerInfoObj)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            }
            if (!empty($FivepkPlayerInfoObj['seo_machine_id'])) {
                throw new MyException(ErrorCode::ERROR_USER_IS_GAMING);
            }
            //查看该用户是否已经关注过
            $AttentionObj = $this->Attention->findByAccountId($accountId);
            if (!empty($AttentionObj)) {
                throw new MyException(ErrorCode::ERROR_USER_IS_ATTENTION);
            }
            $nickName = $FivepkPlayerInfoObj['nick_name'];
            $postData = array(
                'account_id' => $accountId,
                'nick_name'  => $nickName,
                'reward'     => $reward,
                'time'       => $this->time * 1000,
                'operator'   => $this->loginInfo['name']
            );
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            $this->Attention->add($postData);
            //给玩家添加分数
            $data = json_encode([
                'accountId'     => $accountId,
                'rechargeScore' => $reward,
                'rechargeType'  => 1
            ]);
            $this->remoteInterface->addScore($data);
            $tr->commit();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  新人列表
     * @return string
     *
     */
    public function actionFreshPlayerList()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo        = $this->get['pageNo'];
            $pageSize      = $this->get['pageSize'];
            $popCode       = isset($this->get['popCode']) ? $this->get['popCode'] : "";
            $accountId     = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $machine       = isset($this->get['machine']) ? $this->get['machine'] : "";
            $register_time = isset($this->get['register_time']) ? $this->get['register_time'] : "";
            $sort          = isset($this->get['sort']) ? $this->get['sort'] : "";
            $sortType      = isset($this->get['sortType']) ? strtolower($this->get['sortType']) : "desc";

            $pageNo   = $pageNo < 1 ? 1 : $pageNo;
            $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
            $limit    = $pageSize;
            $offset   = ($pageNo - 1) * $pageSize;

            $loginId    = $this->loginId;
            $popCodeArr = $this->Account->findAllSonPopCode($loginId);
            //获取在线的玩家
            $FivepkAccountModel  = new FivepkAccount();
            $OnlinePlayerData    = $FivepkAccountModel->getOnlinePlayer($popCodeArr);
            $OnlinePlayerUserArr = $OnlinePlayerData['arr'];
            //获取所有的留机用户 的id
            $ReservationMachineObj   = $this->FivepkPlayerInfo->getReservationMachine();
            $ReservationMachineIdArr = array_column($ReservationMachineObj, 'account_id');
            //合并数组 按id排序
            $sortAccountIdArr = array_merge($ReservationMachineIdArr, $OnlinePlayerUserArr);

            $query = FivepkAccount::find()->joinWith(['playerInfo'])->filterWhere(['in', 'fivepk_account.seoid', $popCodeArr]);
            if (!empty($popCode)) {
                $query = $query->andFilterWhere(['fivepk_account.seoid' => strtoupper($popCode)]);
            }
            if (!empty($accountId)) {
                $query = $query->andFilterWhere(['fivepk_account.account_id' => $accountId]);
            }

//            if (!empty($player_result['arr'])) {
//                $query->select(['account_status' => 'if(t1.account_id >0 ,10,0)', 'fivepk_player_info.*', 'fivepk_account.*']);
//                $query->leftJoin(['t1' => '(' . FivepkAccount::find()->select('account_id')->where(['account_id' => $player_result['arr']])->createCommand()->getRawSql() . ')'], 't1.account_id = fivepk_account.account_id');
//            }

            if (!empty($register_time)) {
                $query = $query->andFilterWhere(['between', 'fivepk_account.create_date', $register_time, date('Y-m-d', strtotime($register_time . '+1 day'))]);
            }

            if (!empty($machine)) {
                $query->andWhere("(fivepk_player_info.reservation_machine_id like '%{$machine}%' OR fivepk_player_info.seo_machine_id like '%{$machine}%' OR fivepk_player_info.offline_machine_id like '%{$machine}%' ) ");
            }

            if (!empty($sort)) {
                $query = $query->orderBy($sort . " " . $sortType);
            } else {
                if (!empty($sortAccountIdArr)) {
                    $sortAccountIdIn = implode(",", $sortAccountIdArr);
                    $query           = $query->orderBy([new Expression("FIELD( fivepk_account.account_id, {$sortAccountIdIn}) desc,last_login_time DESC")]);
                } else {
                    $query = $query->orderBy('last_login_time DESC');
                }
            }

            $data = $query->offset($offset)->limit($limit)->asArray()->all();
            //所有的id
            $accoungIdArr = array_column($data, 'account_id');

            $count                      = $query->count();
            $page                       = array(
                'account' => $count,
                'maxPage' => ceil($count / $pageSize),
                'nowPage' => $pageNo
            );
            $DataKeyValuePairsObj       = $this->DataKeyValuePairs->findByValueName("新人总玩局数");
            $DataFivepkNewerWinTypeObjs = DataFivepkNewerWinType::find()->asArray()->all();
            //获取当前开启的游戏
            $getOpenGame = $this->DataGameListInfo->getOpenGame();

            $FivepkPlayerNewerObjs = $this->FivepkPlayerNewer->GetNewer($accoungIdArr);
            foreach ($data as $key => $val) {
                $data[$key]['isNewPlayer'] = "";
                foreach ($FivepkPlayerNewerObjs as $FivepkPlayerNewerObj) {
                    foreach ($getOpenGame as $game) {
                        if ($game['game_number'] == $FivepkPlayerNewerObj['game_type'] && $data[$key]['playerInfo']['account_id'] == $FivepkPlayerNewerObj['account_id']) {
                            $data[$key]['isNewPlayer'] .= $game['game_name'] . ":";
                            //游戏新人/局数
                            if ($DataKeyValuePairsObj['value_int'] > $FivepkPlayerNewerObj['play_count']) {
                                $data[$key]['isNewPlayer'] .= "是/";
                            } else {
                                $data[$key]['isNewPlayer'] .= "<span style='color: red'>否</span>/";
                            }
                            $data[$key]['isNewPlayer'] .= $FivepkPlayerNewerObj['play_count'] . "</br>";
                        }
                    }
                }

                //当前奖型 newer_cur_win_type
                $data[$key]['playerInfo']['newer_cur_win_type_comment'] = "";
                if (isset($data[$key]['playerInfo']['newer_cur_win_type']) && $data[$key]['playerInfo']['newer_cur_win_type'] > 0) {
                    foreach ($DataFivepkNewerWinTypeObjs as $winType) {
                        if ($data[$key]['playerInfo']['newer_cur_win_type'] == $winType['win_type'] &&
                            $data[$key]['playerInfo']['newer_cur_win_type_rate'] == $winType['win_type_rate']) {
                            $data[$key]['playerInfo']['newer_cur_win_type_comment'] = $winType['comment'];
                        }
                    }
                }
                $reservation_machine_id                  = $val['playerInfo']['reservation_machine_id'];
                $seo_machine_id                          = $val['playerInfo']['seo_machine_id'];
                $data[$key]['playerInfo']['playMachine'] = $this->FivepkPlayerInfo->SwitchMachineId($reservation_machine_id, $seo_machine_id);
                $data[$key]['status']                    = $this->FivepkAccount->switchStatus($val['playerInfo']['is_online'], $val['allowed']);
            }
            $this->setData(['online' => $OnlinePlayerData, 'data' => $data]);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionFreshPlayerView()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['accountId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            //查询这个玩家所有奖型列表
            $gameName = $this->get['gameName'];
            if (!isset(Yii::$app->params['game'][$gameName])) {
                throw new MyException(ErrorCode::ERROR_GAME_NOT_EXIST);
            }
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
            $accountId       = $this->get['accountId'];
            $data            = $this->DataFivepkNewerWinType->getWinTypeList($gameType);
            $AccountDatas    = $this->FivepkPlayerNewer->findByGameTypeAccoountId($accountId, $gameType);
            foreach ($data as $key => $val) {
                //去掉名字中所有的X
                $data[$key]['comment'] = $this->Tool->clearNameX($val['comment']);
                $data[$key]['hasNum']  = 0;
                foreach ($AccountDatas as $AccountData) {
                    if ($val['win_type'] == $AccountData['win_type']) {
                        $data[$key]['hasNum'] = $AccountData['count'];
                    }
                }
                $data[$key]['lastNum'] = $data[$key]['limit_count'] - $data[$key]['hasNum'];
            }

            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  Paman新人列表
     * @return string
     *
     */
    public function actionPamanFreshPlayerList()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo        = $this->get['pageNo'];
            $pageSize      = $this->get['pageSize'];
            $popCode       = isset($this->get['popCode']) ? $this->get['popCode'] : "";
            $accountId     = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $machine       = isset($this->get['machine']) ? $this->get['machine'] : "";
            $register_time = isset($this->get['register_time']) ? $this->get['register_time'] : "";
            $sort          = isset($this->get['sort']) ? $this->get['sort'] : "";
            $sortType      = isset($this->get['sortType']) ? strtolower($this->get['sortType']) : "desc";
            $gameType      = Yii::$app->params['paman'];

            $loginId    = $this->loginId;
            $popCodeArr = $this->Account->findAllSonPopCode($loginId);
            $popCodes   = "'" . implode("','", $popCodeArr) . "'";

            $params = array(
                'pageNo'        => $pageNo,
                'pageSize'      => $pageSize,
                'popCode'       => $popCode,
                'accountId'     => $accountId,
                'machine'       => $machine,
                'register_time' => $register_time,
                'sort'          => $sort,
                'sortType'      => $sortType,
                'popCodes'      => $popCodes
            );

            $FivepkAccountModel = new FivepkAccount();
            $data               = $FivepkAccountModel->findPage($params);

            //查找paman的新人奖的规则
            $DataKeyValuePairsModel = new DataKeyValuePairs();
            $DataKeyValuePairsObj   = $DataKeyValuePairsModel->tableList($gameType);
            foreach ($data as $key => $val) {
                $data[$key]['status']      = $FivepkAccountModel->switchStatus($val['is_online'], $val['allowed']);
                $data[$key]['playMachine'] = $this->FivepkPlayerInfo->SwitchMachineId($val['reservation_machine_id'], $val['seo_machine_id']);
                $data[$key]['isNewPlayer'] = $DataKeyValuePairsModel->IsNewPlayerPaman($val, $DataKeyValuePairsObj) ? "是" : "否";
                $data[$key]['intervalNum'] = $DataKeyValuePairsModel->findBase('10003');//间隔局数
            }

            $this->setData(['data' => $data]);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    //------- 老玩家jp几率 -------//

    /**
     *   paman老玩家默认JP查看
     */
    public function actionUserOddsDefaultViewJp()
    {
        try {
            $FivepkPlayerPamanSetting = new \common\models\game\paman\FivepkPlayerPamanSetting();
            $data                     = $FivepkPlayerPamanSetting->getDefault();
            $data                     = $this->Tool->clearFloatZero($data);
            $data['bet_score']        = GlobalConfig::getValue(GlobalConfig::Old_PLAYER_JP_BET_SCORE);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   paman老玩家默认JP修改
     */
    public function actionUserOddsDefaultUpdateJp()
    {
        try {
            $FivepkPlayerPamanSetting = new \common\models\game\paman\FivepkPlayerPamanSetting();
            $data                     = $FivepkPlayerPamanSetting->updateDefault($this->post, $this->loginId);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc paman获取老玩家机率Jp
     */
    public function actionUserGetOddsJp()
    {
        try {
            if (!isset($this->get['accountId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId = $this->get['accountId'];
            $class     = new \common\models\game\paman\FivepkPlayerPamanSetting();
            $data      = $class->findOneByField('account_id', $accountId, false);
            if (empty($data)) {
                $data = $class->initUserOdds($accountId);
            }
            $data['bet_score'] = GlobalConfig::getValue(GlobalConfig::Old_PLAYER_JP_BET_SCORE);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc paman修改老玩家机率Jp
     */
    public function actionUserUpdateOddsJp()
    {
        try {
            if (
            Tool::isIssetEmpty($this->post['accountIds'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $class = new \common\models\game\paman\FivepkPlayerPamanSetting();
            $data  = $class->UserUpdateOddsJp($this->post);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  paman初始化所有的用户的机率Jp
     */
    public function actionInitUserAllOddsJp()
    {
        $class = new \common\models\game\paman\FivepkPlayerPamanSetting();
        //修改所有用户的默认机率
        $class->initUserAllOdds();
        $this->sendJson();
    }

    /**
     * @desc paman覆盖玩家机率Jp
     */
    public function actionUserUpdateAllOddsJp()
    {
        try {
            $class = new \common\models\game\paman\FivepkPlayerPamanSetting();
            //修改所有用户的默认机率
            $class->updateAllUserOdds($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   paman获取已经修改机率的玩家列表Jp
     */
    public function actionAllUpdateOddsUserJp()
    {
        try {
            $OldPlayerJpChange = new \common\models\OldPlayerJpChangePaman();
            $changeIds         = $OldPlayerJpChange->getIds();
            $this->setData($changeIds);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   video poker 加查询注册后登陆人数
     */
    public function actionGetLogin()
    {
        try {

            $param['startRegisterTime'] = Tool::examineEmpty($this->get['startRegisterTime']);
            $param['endRegisterTime']   = Tool::examineEmpty($this->get['endRegisterTime']);

            $param['startLastLoginTime'] = Tool::examineEmpty($this->get['startLastLoginTime']);
            $param['endLastLoginTime']   = Tool::examineEmpty($this->get['endLastLoginTime']);

            if (
                empty($param['startRegisterTime'])
                || empty($param['endRegisterTime'])
                || empty($param['startLastLoginTime'])
                || empty($param['endLastLoginTime'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $sql  = "
SELECT count(*) as num FROM (
SELECT account_id from log_login WHERE 
last_login_time BETWEEN '{$param['startLastLoginTime']}' AND '{$param['endLastLoginTime']}'
AND
register_time BETWEEN '{$param['startRegisterTime']}' AND '{$param['endRegisterTime']}'
GROUP BY account_id
) a";
            $data = Yii::$app->game_db->createCommand($sql)->queryAll();
            $num  = Tool::examineEmpty($data[0]['num'], 0);
            $this->setData($num);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function add()
    {

    }


}