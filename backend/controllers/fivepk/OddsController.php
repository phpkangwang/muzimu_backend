<?php

namespace backend\controllers\fivepk;

use common\models\DataRoomInfoList;
use common\models\game\base\GameBase;
use common\models\game\byu\ByuRoomFishRate;
use common\models\game\DataKeyValuePairs;
use common\models\game\factory\RobotFactory;
use common\models\game\FivepkFourOfAKindGift;
use common\models\game\ghr\GhrMachine;
use common\models\game\ghr\GhrRedisLocusHandler;
use common\models\game\ghr\GhrSeo;
use common\models\game\paman\FivepkSeoPaman;
use common\models\game\snow_leopard\SeoMachineSetRate;
use common\models\game\star97\core\DataGiftDiamondStar97;
use common\models\game\star97\DataStar97GridSettings;
use common\models\GlobalConfig;
use common\models\MachinePath;
use common\models\odds\OddsModel;
use common\models\PlayerIsChange;
use Yii;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;

/**
 * 几率调控
 * Class OddsController
 * @package backend\controllers
 */
class OddsController extends MyController
{
    public function actionIndex()
    {
        Tool::checkParam(['gameName', 'level'], $this->get);
        //配置文件里面 eg: HFH
        $gameName           = $this->get['gameName'];
        $param['level']     = $this->get['level'];
        $param['machine']   = isset($this->get['machine']) ? $this->get['machine'] : "";
        $param['accountId'] = isset($this->get['accountId']) ? $this->get['accountId'] : "";
        $param['status']    = isset($this->get['status']) ? $this->get['status'] : "";

        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $data        = $GameObj->oddsIndex($param);
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *   修改房间属性
     */
    public function actionUpdateRoom()
    {
        Tool::checkParam(['gameName', 'level', 'correction'], $this->post);
        $gameName = $this->post['gameName'];
        $level    = $this->post['level'];
        $postData = $this->post;
        unset($postData['gameName']);
        unset($postData['level']);
        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $roomModel   = $GameObj->getModelRoom();
        $roomObj     = $roomModel->findOne($level);
        $roomObj->add($postData);
        $this->sendJson();
    }

    /**
     *  捕鱼获取奖项buff值
     */
    public function actionPoolAddRate()
    {
        Tool::checkParam(['gameName', 'level'], $this->get);
        $gameName             = $this->get['gameName'];
        $GameBaseObj          = new GameBase();
        $GameObj              = $GameBaseObj->initGameObj($gameName);
        $ByuRoomFishRateModel = $GameObj->getModelRoomFishRate();
        $data                 = $ByuRoomFishRateModel->poolAddRate($this->get['level']);
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *  捕鱼获取奖项buff值--修改
     */
    public function actionPoolAddRateUpdate()
    {
        Tool::checkParam(['gameName', 'level'], $this->post);
        $gameName             = $this->post['gameName'];
        $GameBaseObj          = new GameBase();
        $GameObj              = $GameBaseObj->initGameObj($gameName);
        $ByuRoomFishRateModel = $GameObj->getModelRoomFishRate();
        $level                = $this->post['level'];
        unset($this->post['level']);
        unset($this->post['gameName']);
        $postData = $this->post;
        $ByuRoomFishRateModel->poolAddRateUpdate($level, $postData);
        $this->sendJson();
    }

    /**
     *   获取某个鱼的奖池
     */
    public function actionFishRate()
    {
        Tool::checkParam(['gameName', 'fishId', 'level'], $this->get);
        $gameName             = $this->get['gameName'];
        $GameBaseObj          = new GameBase();
        $GameObj              = $GameBaseObj->initGameObj($gameName);
        $ByuRoomFishRateModel = $GameObj->getModelRoomFishRate();
        $data                 = $ByuRoomFishRateModel->poolAddRateAll($this->get['level'], $this->get['fishId']);
        $rs                   = array();
        $tool                 = new Tool();
        foreach ($data as $val) {
            $rs[$val['bet']][$val['pool']] = $val['cur_count'];
            $rs[$val['bet']]['discount']   = $val['discount'];
            $rs[$val['bet']]               = $tool->clearFloatZero($rs[$val['bet']]);
        }
        $this->setData($rs);
        $this->sendJson();
    }

    /**
     *   修改某个鱼的基本属性
     */
    public function actionFishUpdate()
    {
        Tool::checkParam(['gameName', 'level', 'fishId'], $this->post);
        $fishId   = $this->post['fishId'];
        $level    = $this->post['level'];
        $gameName = $this->post['gameName'];
        unset($this->post['fishId']);
        unset($this->post['level']);
        unset($this->post['gameName']);
        $postData      = $this->post;
        $GameBaseObj   = new GameBase();
        $GameObj       = $GameBaseObj->initGameObj($gameName);
        $fishModel     = $GameObj->getModelFish();
        $roomFishModel = $GameObj->getModelRoomFish();
        $fishObj       = $fishModel->findOne($fishId);
        $roomFishObj   = $roomFishModel->findByRoomFish($level, $fishId);

        $fishPost     = array();
        $roomFishPost = array();
        foreach ($postData as $key => $val) {
            $keyArr = explode('__', $key);
            if ($keyArr[0] == "fish") {
                $fishPost[$keyArr[1]] = $val;
            } elseif ($keyArr[0] == "roomFish") {
                $roomFishPost[$keyArr[1]] = $val;
            }
        }
        $fishObj->add($fishPost);
        $roomFishObj->add($roomFishPost);
        $this->sendJson();
    }

    /**
     *   修改某个鱼奖池数据
     */
    public function actionFishRateUpdate()
    {
        Tool::checkParam(['gameName', 'fishId', 'level'], $this->post);
        $fishId   = $this->post['fishId'];
        $level    = $this->post['level'];
        $gameName = $this->post['gameName'];
        unset($this->post['fishId']);
        unset($this->post['level']);
        unset($this->post['gameName']);
        $postData      = $this->post;
        $GameBaseObj   = new GameBase();
        $GameObj       = $GameBaseObj->initGameObj($gameName);
        $FishRateModel = $GameObj->getModelRoomFishRate();
        $FishRateModel->fishRateUpdate($level, $fishId, $postData);
        $this->sendJson();
    }

    /**
     *  玩家机率
     */
    public function actionPlayerOdds()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName  = $this->get['gameName'];
            $pageSize  = $this->get['pageSize'];
            $lastId    = isset($this->get['lastId']) ? $this->get['lastId'] : 0;
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $popCode   = isset($this->get['popCode']) ? $this->get['popCode'] : "";

            $GameBaseObj     = new GameBase();
            $GameObj         = $GameBaseObj->initGameObj($gameName);
            $PlayerOddsModel = $GameObj->getModelPlayerOdds();
            $params          = array(
                'lastId'    => $lastId,
                'pageSize'  => $pageSize,
                'accountId' => $accountId,
                'popCode'   => $popCode
            );
            $data            = $PlayerOddsModel->playerOddsPage($params);

            if (!empty($data)) {
                $endData = end($data);
                $lastId  = isset($endData['id']) ? $endData['id'] : 0;
            }
            $this->setData(array('list' => $data, 'lastId' => $lastId));
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  玩家机率修改
     */
    public function actionPlayerOddsUpdate()
    {
        try {
            if (!isset($this->post['gameName']) || !isset($this->post['type'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->post['gameName'];
            $ids      = isset($this->post['ids']) ? $this->post['ids'] : "";
            $type     = $this->post['type'];

            unset($this->post['ids']);
            unset($this->post['type']);
            unset($this->post['gameName']);
            $postData        = $this->post;
            $GameBaseObj     = new GameBase();
            $GameObj         = $GameBaseObj->initGameObj($gameName);
            $PlayerOddsModel = $GameObj->getModelPlayerOdds();
            $accountIdArr    = explode(",", $ids);
            $PlayerOddsModel->updateByAccounts($accountIdArr, $postData, $type);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  默认机率列表
     */
    public function actionDefaultOdds()
    {
        try {
            if (!isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->get['gameName'];

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $data        = $GameObj->defaultOdds();
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   初始化某个游戏的 buff值和 触顶值
     */
    public function actionOddsCountInit()
    {
        Tool::checkParam(['gameName','oddsType'], $this->get);
        $gameName = $this->get['gameName'];
        $oddsType = $this->get['oddsType'];

        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $OddsCountModel = $GameObj->getModelOddsCount();
        $OddsCountModel->initInfo($oddsType,array());

        $this->sendJson();
    }

    /**
     *   默认buff值和触顶值列表
     */
    public function actionOddsCountList()
    {
        Tool::checkParam(['gameName','isDefault','oddsType','oddsTypeId'], $this->get);
        $gameName  = $this->get['gameName'];
        unset($this->get['gameName']);
        $postData = $this->get;

        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $OddsCountModel = $GameObj->getModelOddsCount();
        $data = $OddsCountModel->tableList($postData);
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *   默认buff值和触顶值列表
     */
    public function actionOddsCountUpdate()
    {
        Tool::checkParam(['gameName','isDefault','oddsType','oddsTypeId'], $this->get);
        $gameName  = $this->get['gameName'];
        $postWhere['isDefault']  = $this->get['isDefault'];
        $postWhere['oddsType']  = $this->get['oddsType'];
        $postWhere['oddsTypeId']  = $this->get['oddsTypeId'];
        unset($this->get['gameName'],$this->get['isDefault'],$this->get['oddsType'],$this->get['oddsTypeId']);
        $postData = array();

        foreach ($this->get as $key => $val){
            $keyArr = explode(":",$key);
            if( is_numeric($keyArr[0])){
                //只有押注分是数字的时候才为有效参数
                $bet = $keyArr[0];
                $column = $keyArr[1];
                $value = $val;
                $postData[$bet][$column] = $value;
            }
        }

        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $OddsCountModel = $GameObj->getModelOddsCount();
        foreach ($postData as $bet=>$val){
            $postWhere['bet_score']=$bet;
            $OddsCountModel->tableUpdate($postWhere, $val);
        }
        $this->sendJson();
    }

    /**
     *   默认buff值和触顶值列表
     */
    public function actionDefaultOddsCountTopList()
    {
        Tool::checkParam(['gameName'], $this->get);
        $gameName = $this->get['gameName'];
        $oddsType = $this->get['oddsType'];

        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $OddsCountModel = $GameObj->getModelOddsCount();
        $data = $OddsCountModel->DefaultOddsCountTopList($oddsType);
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *   修改所有的触顶值触顶值
     */
    public function actionDefaultOddsCountTopUpdate()
    {
        Tool::checkParam(['gameName'], $this->get);
        $gameName = $this->get['gameName'];
        unset($this->get['gameName']);
        $postData = array();
        foreach ($this->get as $key => $val){
            $keyArr = explode(":",$key);
            if( is_numeric($keyArr[0])){
                //只有押注分是数字的时候才为有效参数
                $bet = $keyArr[0];
                $column = $keyArr[1];
                $value = $val;
                $postData[$bet][$column] = $value;
            }
        }

        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $OddsCountModel = $GameObj->getModelOddsCount();
        foreach ($postData as $bet=>$val){
            $OddsCountModel->OddsCountTopUpdate(['is_default'=>1,'bet_score'=>$bet], $val);
        }
        $this->sendJson();
    }

    /**
     *   用户触顶值buff值列表
     */
    public function actionOddsCountTopList()
    {
        Tool::checkParam(['gameName','oddsType','oddsTypeId'], $this->get);
        $gameName = $this->get['gameName'];
        $oddsType = $this->get['oddsType'];
        $oddsTypeId = $this->get['oddsTypeId'];

        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $OddsCountModel = $GameObj->getModelOddsCount();
        $data = $OddsCountModel->OddsCountTopList($oddsType,$oddsTypeId);
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *   修改所有的触顶值buff值
     */
    public function actionOddsCountTopUpdate()
    {
        Tool::checkParam(['gameName','oddsType','oddsTypeId'], $this->get);
        $gameName = $this->get['gameName'];
        $oddsType = $this->get['oddsType'];
        $oddsTypeId = $this->get['oddsTypeId'];
        unset($this->get['gameName'],$this->get['oddsType'],$this->get['oddsTypeId']);
        $postData = array();
        foreach ($this->get as $key => $val){
            $keyArr = explode(":",$key);
            if( is_numeric($keyArr[0])){
                //只有押注分是数字的时候才为有效参数
                $bet = $keyArr[0];
                $column = $keyArr[1];
                $value = $val;
                $postData[$bet][$column] = $value;
            }
        }

        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $OddsCountModel = $GameObj->getModelOddsCount();
        foreach ($postData as $bet=>$val){
            $whereBy = array(
                'is_default'=> 2,
                'odds_type' => $oddsType,
                'odds_type_id' => $oddsTypeId,
                'bet_score' => $bet,
            );
            $OddsCountModel->OddsCountTopUpdate($whereBy, $val);
        }
        $this->sendJson();
    }


    /**
     * 修改页面详情
     * @param $ids
     * @param $level
     */
    public function actionUpdateView()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['ids']) || !isset($this->get['level'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $ids      = $this->get['ids'];
            $level    = $this->get['level'];
            $autoIds  = explode(',', $ids);
            $gameName = $this->get['gameName'];

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $seoModel    = $GameObj->getModelMachine();
            $models      = $seoModel->findsByAutoIds($autoIds);
            $title       = null;
            foreach ($models as $key => $model) {
                if (empty($model['playerInfo'])) {
                    $title .= ',' . $model['seo_machine_id'];
                } else {
                    $title .= ',' . $model['seo_machine_id'] . '(' . ($model['playerInfo']['nick_name']) . ')';
                }
            }
            //获取这个机台的buff值和触顶置不存在就为空
            $buffList = array();
            if($gameName == "DZB"){
                //获取这个机台的buff值和触顶置不存在就为空
                $oddsCountModel = $GameObj->getModelOddsCount();
                $postData = array(
                    'isDefault' => 2,
                    'oddsType' => 2,
                    'oddsTypeId' => $ids,
                );
                $buffList = $oddsCountModel->tableList($postData);
            }
            $title = mb_substr($title, 1);
            $data  = [
                'model' => $models,
                'title' => $title,
                'ids'   => $ids,
                'level' => $level,
                'buffList' => $buffList,
            ];
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  机率调控修改
     */
    public function actionUpdate()
    {
        try {
            if (!isset($this->post['gameName']) || !isset($this->post['ids'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $ids      = $this->post['ids'];
            $gameName = $this->post['gameName'];

            unset($this->post['ids']);
            unset($this->post['gameName']);
            $autoIds = explode(',', $ids);

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $seoModel    = $GameObj->getModelMachine();
            $seoModel->updateByAutoIds($autoIds, $this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  默认机率调控详情
     */
    public function actionDefaultUpdateView()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id       = $this->get['id'];
            $gameName = $this->get['gameName'];
            if (!isset(Yii::$app->params['game'][$gameName])) {
                throw new MyException(ErrorCode::ERROR_GAME_NOT_EXIST);
            }
            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $defaultOdds = $GameObj->getModelDefaultOdds();

            $data = $defaultOdds->findBase($id);
            if (empty($data)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            }
            if ($gameName == "PAM") {
                foreach ($data as $key => $val) {
                    $data[$key] = $this->Tool->clearFloatZero($val);
                }
            } else {
                $data = $this->Tool->clearFloatZero($data);
            }
            if($gameName == "DZB"){
                //获取这个机台的buff值和触顶置不存在就为空
                $oddsCountModel = $GameObj->getModelOddsCount();
                $postData = array(
                    'isDefault' => 1,
                    'oddsType' => 2,
                    'oddsTypeId' => 0,
                );
                $data['buffList'] = $oddsCountModel->tableList($postData);
            }

            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  默认机率调控更新
     */
    public function actionDefaultUpdate()
    {
        try {
            if (!isset($this->post['gameName']) || !isset($this->post['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id       = $this->post['id'];
            $gameName = $this->post['gameName'];
            $postData = $this->post;
            unset($postData['id']);
            unset($postData['gameName']);
            if (empty($postData)) {
                $this->sendJson();
                return;
            }

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $defaultOdds = $GameObj->getModelDefaultOdds();

            if ($gameName == 'PAM') {
                foreach ($postData['data'] as $key => $val) {
                    $obj = $defaultOdds->findByRoomPrize($id, $key);
                    $obj->add($val['FivepkDefaultOddsPamanWintype']);
                }
            } else {
                $obj = $defaultOdds::findOne($id);
                if (empty($obj)) {
                    throw new MyException(ErrorCode::ERROR_OBJ);
                }
                $obj->add($postData);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @desc 设置默认机率
     */
    public function actionSetDefault()
    {
        try {
            if (!isset($this->post['gameName']) || !isset($this->post['ids']) || !isset($this->post['level'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $ids      = $this->post['ids'];
            $level    = $this->post['level'];
            $gameName = $this->post['gameName'];

            $GameBaseObj  = new GameBase();
            $GameObj      = $GameBaseObj->initGameObj($gameName);
            $MachineModel = $GameObj->getModelMachine();
            $postData     = array(
                'ids'   => explode(',', $ids),
                'level' => $level,
            );
            $MachineModel->initDefault($postData);
            $this->sendJson();
            return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   游戏房间四梅强补--详情
     */
    public function actionFourKindsView()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['level'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $level    = $this->get['level'];
            $gameName = $this->get['gameName'];
            if (!isset(Yii::$app->params['game'][$gameName])) {
                throw new MyException(ErrorCode::ERROR_GAME_NOT_EXIST);
            }
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];

            $room = DataRoomInfoList::find()->filterWhere(['room_index' => $level, 'game' => $gameType])->one();
            $data = FivepkFourOfAKindGift::find()->filterWhere(['game_number' => $gameType, 'seo_machine_id' => substr($room->seo_machine_id, 0, 2)])->asArray()->one();
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   游戏房间四梅强补--修改
     */
    public function actionFourKindsUpdate()
    {
        try {
            if (!isset($this->post['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id       = $this->post['id'];
            $postData = $this->post;
            //只能修改指定的字段
            $tableColumns = $this->FivepkFourOfAKindGift->attributes();
            foreach ($postData as $key => $value) {
                if (!in_array($key, $tableColumns)) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }
            }
            $obj = FivepkFourOfAKindGift::findOne($id);
            $obj->add($postData);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97房间机率设置--详情
     */
    public function actionStart97RoomOddsView()
    {
        try {
            if (!isset($this->get['level']) || !isset($this->get['RoomOddsId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $level       = $this->get['level'];
            $RoomOddsId  = $this->get['RoomOddsId'];
            $gameName    = "MXJ";
            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $roomModel   = $GameObj->getModelRoomRewardPool($RoomOddsId);
            $obj         = $roomModel->findByLevel($level, 'array');
            $this->setData($obj);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97房间机率设置--修改
     */
    public function actionStart97RoomOddsUpdate()
    {
        try {
            if (!isset($this->post['level']) || !isset($this->post['RoomOddsId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName   = "MXJ";
            $level      = $this->post['level'];
            $RoomOddsId = $this->post['RoomOddsId'];
            unset($this->post['level']);
            unset($this->post['RoomOddsId']);
            $postData = $this->post;

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $roomModel   = $GameObj->getModelRoomRewardPool($RoomOddsId);
            $obj         = $roomModel->findByLevel($level);
            $obj->add($postData);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97外奖送钻--详情
     */
    public function actionStart97BestBetView()
    {
        try {
            if (!isset($this->get['level'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $level           = $this->get['level'];
            $gameName        = "MXJ";
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
            $roomObj         = $this->DataRoomInfoList->findByGameIndex($gameType, $level);
            if (empty($roomObj)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            }
            $data = $this->DataGiftDiamondStar97->findByRoomId($roomObj['id']);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97外奖送钻--修改
     */
    public function actionStart97BestBetUpdate()
    {
        try {
            if (!isset($this->post['level'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $level           = $this->post['level'];
            $gameName        = "MXJ";
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
            $roomObj         = $this->DataRoomInfoList->findByGameIndex($gameType, $level);

            unset($this->post['level']);
            $postData = $this->post;
            if (empty($roomObj)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            }

            $tableColumns = $this->DataGiftDiamondStar97->attributes();
            foreach ($postData as $key => $value) {
                if (!in_array($key, $tableColumns)) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }
            }
            $obj = DataGiftDiamondStar97::find()->where("room_info_list_id=:room_info_list_id", [':room_info_list_id' => $roomObj['id']])->one();
            $obj->add($postData);

            //通知app刷新
            $this->remoteInterface->refreshMachine();

            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97格子随机档位--所有档位
     */
    public function actionStart97GridAll()
    {
        $data = $this->DataStar97GridSettings->getPrefabIds();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *  明星97格子随机档位--详情
     */
    public function actionStart97GridView()
    {
        try {
            if (!isset($this->get['prefabId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $prefabId = $this->get['prefabId'];
            $data     = $this->DataStar97GridSettings->findByPrefabId($prefabId);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97格子随机档位--修改
     */
    public function actionStart97GridUpdate()
    {
        try {
            if (!isset($this->post['prefabId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $prefabId = $this->post['prefabId'];
            unset($this->post['prefabId']);
            $postData = $this->post;

            $tableColumns = $this->DataStar97GridSettings->attributes();
            foreach ($postData as $key => $value) {
                if (!in_array($key, $tableColumns)) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }
            }
            $obj = DataStar97GridSettings::find()->where("prefab_id=:prefab_id", [':prefab_id' => $prefabId])->one();
            $obj->add($postData);

            //通知app刷新
            $this->remoteInterface->refreshMachine();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97房间配置 详情
     */
    public function actionStart97RoomConfigView()
    {
        try {
            if (!isset($this->get['level']) || !isset($this->get['RoomOddsId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $level       = $this->get['level'];
            $valueInt    = $this->get['RoomOddsId'];
            $gameName    = "MXJ";
            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $gameType    = $GameObj->gameType;
            $data        = $this->DataKeyValuePairs->findByTypeIntIndex($gameType, $valueInt, $level);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97房间配置 修改
     */
    public function actionStart97RoomConfigUpdate()
    {
        try {
            $postData = $this->post;
            //连接fivepk数据库
            $connection = \Yii::$app->getDb('game_db');
            //开启事务
            $transaction = $connection->beginTransaction();
            foreach ($postData as $key => $val) {
                $updateDate = array(
                    'value_varchar' => $val,
                );
                $obj        = DataKeyValuePairs::findOne($key);
                if (empty($obj)) {
                    throw new MyException(ErrorCode::ERROR_OBJ);
                }
                $obj->add($updateDate);
            }
            $transaction->commit();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  彩金复位
     */
    public function actionSetBonusRecover()
    {
        try {
            if (Tool::isIssetEmpty($this->post['gameName']) || Tool::isIssetEmpty($this->post['ids'])) {
                $this->setMessage(ErrorCode::ERROR_PARAM);
                $this->sendJson();
            }
            $gameName       = $this->post['gameName'];
            $FivepkSeoPaman = new FivepkSeoPaman();
            $ids            = explode(',', $this->post['ids']);
            $ids            = array_map('intval', $ids);
            if (empty($ids)) {
                $this->setMessage(ErrorCode::ERROR_PARAM);
                $this->sendJson();
            }
            $data = $FivepkSeoPaman->setRecover($ids, $gameName);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97彩金复位
     */
    public function actionSetStart97Gift()
    {
        $this->post['gameName'] = 'MXJ';
//        $this->post['room']     = 2;
//        $data                   = $this->platform(__FUNCTION__, [$this]);

        try {
            if (
                Tool::isIssetEmpty($this->post['gameName'])
                || Tool::isIssetEmpty($this->post['room'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->post['gameName'];
            $room     = intval($this->post['room']);
            if ($room < 1 || $room > 5) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $MachineListStar97 = new \common\models\game\star97\MachineListStar97();
            $data              = $MachineListStar97->setGift($gameName, $room);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  明星97彩金修改
     */
    public function actionUpdateStart97Gift()
    {
        try {
            if (
                Tool::isIssetEmpty($this->post['gameName'])
                || Tool::isIssetEmpty($this->post['id'])
                || Tool::isIssetEmpty($this->post['gift'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->post['gameName'];
            $id       = intval($this->post['id']);
            $gift     = intval($this->post['gift']);
            if (empty($id) || empty($gift)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $MachineListStar97 = new \common\models\game\star97\MachineListStar97();
            $data              = $MachineListStar97->updateGift($id, $gameName, $gift);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  paman JP奖获取
     */
    public function actionGetPamanjp()
    {
        try {
            Tool::checkParam(['id'], $this->get);
            $id                              = $this->get['id'];
            $gameName                        = "PAM";
            $GameBaseObj                     = new GameBase();
            $GameObj                         = $GameBaseObj->initGameObj($gameName);
            $seoModel                        = $GameObj->getModelMachine();
            $data                            = $seoModel->findObj('auto_id', $id, "array");
            $jp_pre_win_type_rate            = json_decode($data['jp_pre_win_type_rate'], true);
            $game_type                       = $GameObj->gameType;
            $FivepkPrizeType                 = new  \common\models\FivepkPrizeType;
            $prize_types                     = Tool::arrayToSqlInStr(array_keys($jp_pre_win_type_rate));
            $where                           = "game_type=$game_type and prize_type in($prize_types) and status=10";
            $rateData                        = $FivepkPrizeType::find()->where($where)->select('prize_type,rate')->indexBy('prize_type')->asArray()->all();
            $DataRoomInfoList                = new  \common\models\DataRoomInfoList;
            $DataRoomInfoList                = $DataRoomInfoList->findBase($data['room_info_list_id']);
            $data['bet_score']               = $DataRoomInfoList['bet_score'];
            $data['rates']                   = $rateData;
            $data['jp_accumulate_add_value'] = $data['jp_accumulate_add_buff'];
            $data['jp_accumulate_buff']      = $data['jp_accumulate_total_buff'];
            unset($data['room_info_list_id']);
            unset($data['jp_accumulate_add_buff']);
            unset($data['jp_accumulate_total_buff']);
            $data = $this->Tool->clearFloatZero($data);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  paman JP奖修改
     */
    public function actionUpdatePamanjp()
    {
        try {
            if (
                !isset($this->post['jp_accumulate_count'])//累计局数
                || !isset($this->post['jp_play_count'])//押注次数限制
                || !isset($this->post['jp_play_count_rate'])//押注1/2/3/4次数限制
                || !isset($this->post['jp_pre_win_type'])//当前大奖ID 0关闭
                || !isset($this->post['jp_pre_win_type_rate'])//大奖ID和几率
                || !isset($this->post['jp_accumulate_add_value'])//JP奖累计值
                || !isset($this->post['jp_award_interval_count'])//JP奖目标局数
                || !isset($this->post['jp_open_limit_count'])//押注失败次数
                || !isset($this->post['jp_accumulate_buff'])//JP奖buff值
                || Tool::isIssetEmpty($this->post['id'])//ID
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $gameName     = "PAM";
            $id           = $this->post['id'];
            $GameBaseObj  = new GameBase();
            $GameObj      = $GameBaseObj->initGameObj($gameName);
            $MachineModel = $GameObj->getModelMachine();
            $vData        = $MachineModel->validateJP($this->post);
            if (!isset($vData['jp_pre_win_type_rate']) || !isset($vData['jp_play_count_rate'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $data = array(
                'jp_accumulate_count'      => intval($this->post['jp_accumulate_count']),
                'jp_play_count'            => $this->post['jp_play_count'],
                'jp_play_count_rate'       => json_encode($vData['jp_play_count_rate']),
                'jp_pre_win_type'          => $this->post['jp_pre_win_type'],
                'jp_pre_win_type_rate'     => json_encode($vData['jp_pre_win_type_rate']),
                'jp_accumulate_add_buff'   => $this->post['jp_accumulate_add_value'],
                'jp_award_interval_count'  => $this->post['jp_award_interval_count'],
                'jp_open_limit_count'      => $this->post['jp_open_limit_count'],
                'jp_accumulate_total_buff' => $this->post['jp_accumulate_buff'],
            );

            $machineObj = $MachineModel->findObj('auto_id', $id, 'obj');
            $machineObj->add($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  paman JP奖默认几率获取
     */
    public function actionGetPamanjpDefault()
    {
        try {
            if (
            Tool::isIssetEmpty($this->get['id'])//ID
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $GlobalConfig = new \common\models\GlobalConfig();
            $roomId       = $this->get['id'];
            $data         = $GlobalConfig->getJPValue($roomId);
            $data         = $this->Tool->clearFloatZero($data);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  paman JP奖默认几率修改
     */
    public function actionUpdatePamanjpDefault()
    {
        try {
            if (
                !isset($this->post['jp_accumulate_count'])//累计局数
                || !isset($this->post['jp_play_count'])//押注次数限制
                || !isset($this->post['jp_play_count_rate'])//押注1/2/3/4次数限制
                || !isset($this->post['jp_pre_win_type_rate'])//大奖ID和几率
                || !isset($this->post['jp_accumulate_add_buff'])//JP奖累计值
                || !isset($this->post['jp_award_interval_count'])//JP奖目标局数
                || !isset($this->post['jp_open_limit_count'])//押注失败次数
                || Tool::isIssetEmpty($this->post['id'])//ID
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $GlobalConfig = new \common\models\GlobalConfig();
            $data         = array(
                'jp_accumulate_count'     => intval($this->post['jp_accumulate_count']),
                'jp_play_count'           => $this->post['jp_play_count'],
                'jp_play_count_rate'      => ($this->post['jp_play_count_rate']),
                'jp_pre_win_type_rate'    => ($this->post['jp_pre_win_type_rate']),
                'jp_accumulate_add_buff'  => $this->post['jp_accumulate_add_buff'],
                'jp_award_interval_count' => $this->post['jp_award_interval_count'],
                'jp_open_limit_count'     => $this->post['jp_open_limit_count'],
            );

            $return = $GlobalConfig->updateJPValue($this->post['id'], $data, $this->loginId);
            if (empty($return)) {
//                $this->setMessage(ErrorCode::ERROR_SYSTEM);
                throw new MyException(ErrorCode::ERROR_SYSTEM);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  paman JP奖通过默认几率修改
     */
    public function actionUpdatePamanjpForDefault()
    {
        try {
            if (
                !isset($this->post['autoId'])//累计局数
                || Tool::isIssetEmpty($this->post['ids'])//ID
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $autoId       = $this->post['autoId'];
            $ids          = explode(",", $this->post['ids']);
            $gameName     = "PAM";
            $GameBaseObj  = new GameBase();
            $GameObj      = $GameBaseObj->initGameObj($gameName);
            $MachineModel = $GameObj->getModelMachine();
            foreach ($ids as $val) {
                $MachineObj = $MachineModel->findObj('auto_id', $val, 'obj');
                $MachineObj->setJPListFromDefault($autoId);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    //雪豹-默认机台几率
    public function actionXbDefaultOddsRate()
    {

        Tool::checkParam(['level'], $this->get);
        $SeoMachineSetRate        = new  SeoMachineSetRate();
        $SeoMachineSetRate->level = $this->get['level'];
        $data                     = $SeoMachineSetRate->getDefaultOddsRate();
        $this->setData($data);
        $this->sendJson();
    }

    //雪豹-修改默认机台几率
    public function actionXbUpdateDefaultOddsRate()
    {
        Tool::checkParam(['level'], $this->post);
        $SeoMachineSetRate        = new  SeoMachineSetRate();
        $SeoMachineSetRate->level = $this->post['level'];
        $SeoMachineSetRate->post  = $this->post;
        $data                     = $SeoMachineSetRate->updateDefaultOddsRate();
        $this->setData($data);
        $this->sendJson();
    }

    //雪豹-通过默认几率修改机台几率
    public function actionXbUpdateOddsRateForDefault()
    {
        Tool::checkParam(['machines'], $this->post);
        $SeoMachineSetRate          = new  SeoMachineSetRate();
        $SeoMachineSetRate->post    = $this->post;
        $SeoMachineSetRate->loginId = $this->loginId;
        $SeoMachineSetRate->updateRateForDefault();
        $this->sendJson();
    }

    //雪豹-机台几率
    public function actionXbOddsRate()
    {

        Tool::checkParam(['seoMachineId', 'level'], $this->get);
        $SeoMachineSetRate               = new  SeoMachineSetRate();
        $SeoMachineSetRate->seoMachineId = $this->get['seoMachineId'];
        $SeoMachineSetRate->level        = $this->get['level'];
        $data                            = $SeoMachineSetRate->showList();
        $this->setData($data);
        $this->sendJson();
    }


    //雪豹-修改机台几率
    public function actionXbUpdateOddsRate()
    {
        Tool::checkParam(['seoMachineId', 'level'], $this->post);
        $SeoMachineSetRate               = new  SeoMachineSetRate();
        $SeoMachineSetRate->seoMachineId = $this->post['seoMachineId'];
        $SeoMachineSetRate->level        = $this->post['level'];
        $SeoMachineSetRate->post         = $this->post;
        $SeoMachineSetRate->loginId      = $this->loginId;
        $SeoMachineSetRate->updateRate();
        $this->sendJson();
    }


    /**
     *   新老玩家机率详情
     */
    public function actionNewOldOddsInfo()
    {
        try {
            Tool::checkParam(['gameName', 'isDefault', 'oddsType', 'oddsTypeId'], $this->get);
            $gameName        = $this->get['gameName'];
            $isDefault       = $this->get['isDefault'];
            $oddsType        = $this->get['oddsType'];
            $oddsTypeId      = $this->get['oddsTypeId'];
            if (($isDefault == 1 && $oddsTypeId != "") || ($isDefault ==+ 2 && $oddsTypeId == "")) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $OddsModel = $GameObj->getModelOdds();
            $data          = $OddsModel->oddsInfo($isDefault, $oddsType, $oddsTypeId);
            if($gameName == "DZB"){
                //获取这个机台的buff值和触顶置不存在就为空
                $oddsCountModel = $GameObj->getModelOddsCount();
                $postData = array(
                    'isDefault' => $isDefault,
                    'oddsType' => $oddsType,
                    'oddsTypeId' => $oddsTypeId,
                );
                $data['buffList'] = $oddsCountModel->tableList($postData);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   新老玩家机率 修改
     */
    public function actionNewOldOddsUpdate()
    {
        Tool::checkParam(['gameName', 'isDefault', 'oddsType', 'oddsTypeId'], $this->post);
        $gameName   = $this->post['gameName'];
        $isDefault  = $this->post['isDefault'];
        $oddsType   = $this->post['oddsType'];
        $oddsTypeId = $this->post['oddsTypeId'];

        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $gameType    = $GameObj->gameType;
        $OddsModel = $GameObj->getModelOdds();

        unset($this->post['gameName'], $this->post['isDefault'], $this->post['oddsType'], $this->post['oddsTypeId']);
        if ($isDefault == 1) {
            //修改默认机率
            $OddsModel::updateAll($this->post, ['and', ['is_default' => $isDefault], ['odds_type' => $oddsType]]);
        } else {
            //修改玩家机率
            $oddsTypeId = explode(",", $oddsTypeId);
            $OddsModel::updateAll($this->post, ['and', ['in', 'odds_type_id', $oddsTypeId], ['is_default' => $isDefault], ['odds_type' => $oddsType]]);
            //增加一条修改玩家几率的记录
            $PlayerIsChangeModel = new PlayerIsChange();
            //先全部删除，然后在添加
            $PlayerIsChangeModel->deleteByAccountIds($gameType, $oddsTypeId);
            //添加记录
            $PlayerIsChangeModel->addByAccountIds($gameType, $oddsTypeId);
        }
        $this->sendJson();
    }

    /**
     *  初始化玩家 机率
     * @throws \yii\db\Exception
     */
    public function actionNewOldOddsInit()
    {
        Tool::checkParam(['gameName', 'type', 'oddsType'], $this->post);
        $gameName = $this->post['gameName'];
        $type     = $this->post['type'];
        $oddsType = $this->post['oddsType'];
        //开启事务
        $accountIdArr = array();
        if ($type == 2) {
            //不覆盖，获取一级修改过机率玩家的id
            $accountIdArr = $this->PlayerIsChange->getIds();
        } else {
            //清空PlayerIsChange
            $this->PlayerIsChange->clearTable();
        }
        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $OddsModel = $GameObj->getModelOdds();
        $OddsModel->initUserInfo($oddsType, $accountIdArr);

        $this->sendJson();
    }

    /**
     *  初始化 机台 机率
     * @throws \yii\db\Exception
     */
    public function actionMachineOddsInit()
    {
        try {
            Tool::checkParam(['gameName'], $this->get);
            $gameName = $this->get['gameName'];
            $machineIdArr = array();

            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
            $OddsModel       = new OddsModel();
            $selfOddsModel   = $OddsModel->getModel($gameType);
            $selfOddsModel->initMachineInfo($machineIdArr);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }


    /**
     * 公共配置表查看
     * @throws MyException
     */
    public function actionGetGlobalConfig()
    {
        Tool::checkParam(['type'], $this->get);
//        $GlobalConfig = new GlobalConfig();
        $data = GlobalConfig::getValue($this->get['type']);
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * 公共配置表修改
     * @throws MyException
     */
    public function actionUpdateGlobalConfig()
    {
        Tool::checkParam(['type', 'data'], $this->post);
//        $GlobalConfig = new GlobalConfig();
        $data = GlobalConfig::setValue($this->post['type'], $this->post['data']);
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * 赛马抽水累计值
     */
    public function actionGetAllProfitSum()
    {
        $data = GhrRedisLocusHandler::getAllProfitSum();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * 赛马修改机台分数
     */
    public function actionUpdatePoint()
    {
        try {
            Tool::checkParam(['autoId', 'playerWinPoint', 'playerPlayPoint'], $this->post);
            $playerWinPoint  = intval($this->post['playerWinPoint']);
            $playerPlayPoint = intval($this->post['playerPlayPoint']);
            $autoId          = intval($this->post['autoId']);
            if ($playerWinPoint < 1 || $playerPlayPoint < 1) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $obj  = GhrMachine::findOne(array('auto_id' => $autoId));
            $data = $obj->add([
                'player_win_point'  => $playerWinPoint,
                'player_play_point' => $playerPlayPoint
            ]);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


}

?>