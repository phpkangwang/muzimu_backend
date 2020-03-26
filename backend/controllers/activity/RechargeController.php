<?php

namespace backend\controllers\activity;

use backend\controllers\MyController;
use backend\models\MyException;
use backend\models\ErrorCode;
use backend\models\Tool;
use common\models\activity\recharge\RechargeGear;
use common\models\activity\recharge\RechargeGearWinType;
use Yii;


class RechargeController extends MyController
{

    /**
     *   充值档位 获取所有
     */
    public function actionRechargeGearList()
    {
        $RechargeGearModel = new RechargeGear();
        $data              = $RechargeGearModel->tableList();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *   充值档位 新增
     */
    public function actionRechargeGearAdd()
    {
        try {
            Tool::checkParam(['activityType', 'rechargeGear', 'rechargePrice'], $this->post);
            $postData['activity_type']  = $this->post['activityType'];
            $postData['recharge_gear']  = $this->post['rechargeGear'];
            $postData['recharge_price'] = $this->post['rechargePrice'];
            $id                         = isset($this->post['id']) ? $this->post['id'] : "";

            if ($id != "") {
                $obj = RechargeGear::findOne($id);
                if (empty($obj)) {
                    throw new MyException(ErrorCode::ERROR_OBJ);
                }
            } else {
                $obj = new RechargeGear();
            }

            $data = $obj->add($postData);
            $this->setData($data);
            $this->sendJson();
            return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   充值档位 删除
     */
    public function actionRechargeGearDelete()
    {
        Tool::checkParam(['id'], $this->post);
        $id                = $this->post['id'];
        $RechargeGearModel = new RechargeGear();
        $RechargeGearModel->del($id);
        $this->sendJson();
    }


    /**
     *   充值档位奖励 获取所有
     */
    public function actionRechargeGearWinTypeList()
    {
        Tool::checkParam(['gameName'], $this->get);
        $gameName        = $this->get['gameName'];
        $chineseGameName = Yii::$app->params['game'][$gameName];
        $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
        //获取所有的奖
        $prizeList    = $this->FivepkPrizeType->getPrizeTypeList($gameType);
        $newPrizeList = array();
        foreach ($prizeList as $val) {
            if ($val['parent'] == 0 && $val['is_jp'] == 2 ) {
                $newPrizeList[$val['win_type_server']] = $val['prize_name'];
            }
        }

        //获取所有的档位
        $RechargeGearModel = new RechargeGear();
        $RechargeGearObj   = $RechargeGearModel->tableList();
        $RechargeGearList  = array_column($RechargeGearObj, 'recharge_price', 'recharge_gear');

        $RechargeGearWinTypeModel = new RechargeGearWinType();
        $data                     = $RechargeGearWinTypeModel->findByGameType($gameType);
        $rs                       = array();
        foreach ($data as $val) {
            $jsonArr = json_decode($val['recharge_gear_get_win_type_rate'], true);
            foreach ($jsonArr as $key => $json) {
                $newArr = array(
                    'id'            => $val['id'],
                    'gameType'      => $val['game_type'],
                    'rechargeGear'  => $val['recharge_gear'],
                    'prizeRate'     => $key,
                    'prizeName'     => $newPrizeList[$key],
                    'prizePre'      => $json,
                    'rechargePrize' => $RechargeGearList[$val['recharge_gear']],
                    'rateScore'     => $val['rate_of_player_score']
                );
                array_push($rs, $newArr);
            }
        }

        $this->setData($rs);
        $this->sendJson();
    }

    /**
     *   充值档位奖励 新增
     */
    public function actionRechargeGearWinTypeAdd()
    {
        Tool::checkParam(['gameName', 'rechargeGear', 'prizeRate', 'prizePre', 'rateScore'], $this->post);
        $gameName        = $this->post['gameName'];
        $chineseGameName = Yii::$app->params['game'][$gameName];
        $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
        $rechargeGear    = $this->post['rechargeGear'];
        $prizeRate       = $this->post['prizeRate'];
        $prizeRateOld    = isset($this->post['prizeRateOld']) ? $this->post['prizeRateOld'] : "";
        $prizePre        = $this->post['prizePre'];
        $rateScore       = $this->post['rateScore'];

        $RechargeGearWinTypeModel = new RechargeGearWinType();
        $obj                      = $RechargeGearWinTypeModel->findByGameTypeRechargeGear($gameType, $rechargeGear);
        if ($obj != "") {
            $recharge_gear_get_win_type_rate     = $obj->recharge_gear_get_win_type_rate;
            $recharge_gear_get_win_type_rate_arr = json_decode($recharge_gear_get_win_type_rate, true);
            if ($prizeRateOld != "") {
                unset($recharge_gear_get_win_type_rate_arr[$prizeRateOld]);
            }
            $recharge_gear_get_win_type_rate_arr[$prizeRate] = $prizePre;
        } else {
            $obj                                             = new RechargeGearWinType();
            $recharge_gear_get_win_type_rate_arr[$prizeRate] = $prizePre;
        }
        $recharge_gear_get_win_type_rate_new = json_encode($recharge_gear_get_win_type_rate_arr);
        $postData                            = array(
            'game_type'                       => $gameType,
            'recharge_gear'                   => $rechargeGear,
            'recharge_gear_get_win_type_rate' => $recharge_gear_get_win_type_rate_new,
            'rate_of_player_score'            => $rateScore
        );
        $data                                = $obj->add($postData);
        $this->setData($data);
        $this->sendJson();
        return;
    }

    /**
     *   充值档位奖励 删除
     */
    public function actionRechargeGearWinTypeDelete()
    {
        Tool::checkParam(['gameName', 'rechargeGear', 'prizeRate'], $this->post);
        $gameName        = $this->post['gameName'];
        $chineseGameName = Yii::$app->params['game'][$gameName];
        $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
        $rechargeGear    = $this->post['rechargeGear'];
        $prizeRate       = $this->post['prizeRate'];

        $RechargeGearWinTypeModel = new RechargeGearWinType();
        $obj                      = $RechargeGearWinTypeModel->findByGameTypeRechargeGear($gameType, $rechargeGear);
        if ($obj != "") {
            $recharge_gear_get_win_type_rate     = $obj->recharge_gear_get_win_type_rate;
            $recharge_gear_get_win_type_rate_arr = json_decode($recharge_gear_get_win_type_rate, true);
            unset($recharge_gear_get_win_type_rate_arr[$prizeRate]);
            $recharge_gear_get_win_type_rate_new = json_encode($recharge_gear_get_win_type_rate_arr);
        } else {
            throw new MyException(ErrorCode::ERROR_OBJ);
        }
        $postData = array(
            'game_type'                       => $gameType,
            'recharge_gear'                   => $rechargeGear,
            'recharge_gear_get_win_type_rate' => $recharge_gear_get_win_type_rate_new,
        );
        $data     = $obj->add($postData);
        $this->setData($data);
        $this->sendJson();
        return;
    }


}

?>