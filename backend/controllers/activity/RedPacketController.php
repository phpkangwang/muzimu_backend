<?php

namespace backend\controllers\activity;

use backend\models\Tool;
use common\models\activity\redPacket\RedPacket;
use common\models\activity\redPacket\RedPacketTime;
use common\models\DataGameListInfo;
use common\models\RecordDiamond;
use Yii;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;

class RedPacketController extends MyController
{

    /**
     *   获取所有
     */
    public function actionRedPacketList()
    {
        try {
            if (!isset($this->get['gameName']) || !isset($this->get['roomId'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->get['gameName'];
            $roomId   = $this->get['roomId'];

            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];

            //获取活动配置
            $list = $this->RedPacket->findByGameRoom($gameType, $roomId);
            //获取时间
            $activityId = $list['id'];
            $time       = $this->RedPacketTime->findByActivityId($activityId);
            $data       = array(
                'list' => $list == "" ? array() : $list,
                'time' => $time
            );
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   新增
     */
    public function actionRedPacketAdd()
    {
        try {
            if (!isset($this->post['gameName']) || !isset($this->post['roomId'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->post['gameName'];
            $roomId   = $this->post['roomId'];
            $id       = isset($this->post['id']) ? $this->post['id'] : "";
            unset($this->post['gameName']);
            unset($this->post['roomId']);

            $RedPacketObj = new RedPacket();
            $tableColumns = $RedPacketObj->attributes();
            $postData     = $this->post;
            foreach ($postData as $key => $value) {
                if (!in_array($key, $tableColumns)) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }
            }

            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType        = Yii::$app->params[$chineseGameName]['gameType'];

            $postData['game_type']  = $gameType;
            $postData['room_index'] = $roomId;

            if ($id != "") {
                $obj = RedPacket::findOne($id);
                if (empty($obj)) {
                    throw new MyException(ErrorCode::ERROR_OBJ);
                }
            } else {
                $obj = new RedPacket();
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
     *   删除
     */
    public function actionRedPacketDelete()
    {
        try {
            if (!isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = $this->get['id'];
            $this->RedPacket->del($id);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   获取所有
     */
    public function actionRedPacketTimeList()
    {
        try {
            if (!isset($this->get['activityId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $activityId = $this->get['activityId'];
            $data       = $this->RedPacketTime->findByActivityId($activityId);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   新增
     */
    public function actionRedPacketTimeAdd()
    {
        try {
            if (!isset($this->post['activityId']) || !isset($this->post['stime']) || !isset($this->post['etime'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $activityId = $this->post['activityId'];
            $stime      = $this->post['stime'];
            $etime      = $this->post['etime'];

            $postData = array(
                'activity_id'  => $activityId,
                'start_time'   => $stime,
                'end_time'     => $etime,
                'is_crontab'   => 1,
                'time_crontab' => date("Y-m-d", time())
            );

            if (isset($this->post['id'])) {
                $id  = $this->post['id'];
                $obj = RedPacketTime::findOne($id);
                if (empty($obj)) {
                    throw new MyException(ErrorCode::ERROR_OBJ);
                }
                //修改要重置所有的
                $this->RedPacketTime->setIsCrontab();
                $this->LimitTime->setIsCrontab();
            } else {
                $obj = new RedPacketTime();
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
     *   删除
     */
    public function actionRedPacketTimeDelete()
    {
        try {
            if (!isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = $this->get['id'];
            $this->RedPacketTime->del($id);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   获取某个游戏的送钻报表
     */
    public function actionRedPacketDiamondReport()
    {
        $day = date('Y-m-d', time());
        $rs  = RecordDiamond::getRedPacketDiamondReport($day);
        $this->setData($rs);
        $this->sendJson();
    }


}

?>