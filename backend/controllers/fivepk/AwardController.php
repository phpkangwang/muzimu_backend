<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-5
 * Time: 18:27
 */

namespace backend\controllers\fivepk;

use backend\models\services\ConfigService;
use common\models\FivepkPrizeOut;
use common\models\FivepkPrizeType;
use common\models\game\base\GameBase;
use Yii;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;

class AwardController extends MyController
{

    /**
     *   获取所有的出奖类型
     */
    public function actionPrizeOutList()
    {
        try {
            if (!isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName        = $this->get['gameName'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
            $data            = $this->FivepkPrizeOut->findByGameType($gameType);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   新增修改的出奖类型
     */
    public function actionPrizeOutAdd()
    {
        try {
            if (!isset($this->post['value']) || !isset($this->post['gameName']) || !isset($this->post['name']) ||
                !isset($this->post['sort']) || !isset($this->post['status'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $postData = array();
            if (isset($this->post['id'])) {
                $autoId = $this->post['id'];
                $obj    = FivepkPrizeOut::findOne($autoId);
                if (empty($obj)) {
                    throw new MyException(ErrorCode::ERROR_OBJ);
                }
            } else {
                $obj                    = new FivepkPrizeOut();
                $postData['created_at'] = $this->time;
            }
            $gameName               = $this->post['gameName'];
            $chineseGameName        = Yii::$app->params['game'][$gameName];
            $gameType               = Yii::$app->params[$chineseGameName]['gameType'];
            $postData['value']      = $this->post['value'];
            $postData['game_type']  = $gameType;
            $postData['name']       = $this->post['name'];
            $postData['sort']       = $this->post['sort'];
            $postData['status']     = $this->post['status'];
            $postData['operator']   = $this->loginInfo['name'];
            $postData['updated_at'] = $this->time;
            $data                   = $obj->add($postData);
            $this->setData($data);
            $this->sendJson();
            return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   删除的出奖类型
     */
    public function actionPrizeOutDelete()
    {
        try {
            if (!isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = $this->get['id'];
            $this->FivepkPrizeOut->del($id);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   获取所有的奖
     */
    public function actionPrizeList()
    {
        try {
            if (!isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName        = $this->get['gameName'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
            $data            = $this->FivepkPrizeType->findByGameType($gameType);
            foreach ($data as $key => $val) {
                $data[$key]['updated_at'] = date('Y-m-d H:i:s', $val['updated_at']);
                $data[$key]['created_at'] = date('Y-m-d H:i:s', $val['created_at']);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   新增修改的奖
     */
    public function actionPrizeAdd()
    {
        try {
            if (!isset($this->post['gameName']) || !isset($this->post['sort']) ||
                !isset($this->post['parent']) || !isset($this->post['big_award'])
                || !isset($this->post['prize_name'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $postData = array();
            if (isset($this->post['id'])) {
                $id  = $this->post['id'];
                $obj = FivepkPrizeType::findOne($id);
                if (empty($obj)) {
                    throw new MyException(ErrorCode::ERROR_OBJ);
                }
            } else {
                $obj                    = new FivepkPrizeType();
                $postData['created_at'] = $this->time;
            }
            $gameName        = $this->post['gameName'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];

            $postData['game_type']  = $gameType;
            $postData['prize_name'] = $this->post['prize_name'];
            $postData['sort']       = $this->post['sort'];
            $postData['rate']       = $this->post['rate'];
            $postData['status']     = $this->post['status'];
            $postData['parent']     = $this->post['parent'];
            $postData['big_award']  = $this->post['big_award'];
            $postData['operator']   = $this->loginInfo['name'];
            $postData['updated_at'] = $this->time;
            $data                   = $obj->add($postData);
            $this->setData($data);
            $this->sendJson();
            return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   删除的奖
     */
    public function actionPrizeDelete()
    {
        try {
            if (!isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = $this->get['id'];
            $this->FivepkPrizeType->del($id);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   根据游戏名称获取奖项列表
     */
    public function actionGetPrizeParentList()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['bigAward'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->get['gameName'];
            $bigAward = $this->get['bigAward'] == 2 ? 2 : 1;

            $GameBaseObj = new GameBase();
            $GameObj     = $GameBaseObj->initGameObj($gameName);
            $gameType    = $GameObj->gameType;

            $data = $this->FivepkPrizeType->getPrizeTypeParentList($gameType, $bigAward);

            foreach ($data as $key => $val) {
                //去掉乌龙
                if ($val['prize_name'] == "乌龙") {
                    unset($data[$key]);
                    continue;
                }
                $data[$key]['prize_name'] = $this->Tool->clearNameX($val['prize_name']);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   根据明星97的奖项
     */
    public function actionGetPrizeItem()
    {
        try {
            if (!isset($this->get['bigAward'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $data = Yii::$app->params['mxjPrizeItem'];
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   根据游戏名称获取出奖列表
     */
    public function actionGetPrizeOutList()
    {
        try {
            if (!isset($this->get['gameName'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName        = $this->get['gameName'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
            $data            = $this->FivepkPrizeOut->findByGameType($gameType);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

}