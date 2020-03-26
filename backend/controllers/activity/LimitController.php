<?php
namespace backend\controllers\activity;

use common\models\activity\limit\Limit;
use common\models\activity\limit\LimitTime;
use Yii;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;

class LimitController extends MyController
{

    /**
     *   获取所有
     */
    public function actionLimitList()
    {
        try {
            if ( !isset($this->get['gameName']) || !isset($this->get['roomId'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->get['gameName'];
            $roomId   = $this->get['roomId'];

            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType  = Yii::$app->params[$chineseGameName]['gameType'];

            //获取活动配置
            $list = $this->Limit->findByGameRoom($gameType, $roomId);
            //获取时间
            $activityId = $list['id'];
            $time = $this->LimitTime->findByActivityId($activityId);
            $data = array(
                'list' => $list == "" ? array() : $list,
                'time' => $time
            );
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   新增
     */
    public function actionLimitAdd()
    {
        try{
            if ( !isset($this->post['gameName']) || !isset($this->post['roomId']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $gameName = $this->post['gameName'];
            $roomId   = $this->post['roomId'];
            $id       = isset( $this->post['id'] ) ? $this->post['id'] : "";
            unset($this->post['gameName']);
            unset($this->post['roomId']);

            $LimitObj = new Limit();
            $tableColumns = $LimitObj->attributes();
            $postData     = $this->post;
            foreach ($postData as $key => $value){
                if( !in_array($key, $tableColumns)){
                    throw new MyException( ErrorCode::ERROR_PARAM );
                }
            }

            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType  = Yii::$app->params[$chineseGameName]['gameType'];

            $postData['game_type']  = $gameType;
            $postData['room_index'] = $roomId;

            if( $id != "" ){
                $id = $this->post['id'];
                $obj = Limit::findOne($id);
                if(empty($obj)){
                    throw new MyException( ErrorCode::ERROR_OBJ);
                }
            }else{
                $obj = new Limit();
            }
            $data = $obj->add($postData);
            $this->setData($data);
            $this->sendJson();
            return;
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   删除
     */
    public function actionLimitDelete()
    {
        try {
            if ( !isset($this->get['id'])  ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = $this->get['id'];
            $this->Limit->del($id);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   获取所有
     */
    public function actionLimitTimeList()
    {
        try {
            if ( !isset($this->get['activityId']) ){
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $activityId = $this->get['activityId'];
            $data = $this->LimitTime->findByActivityId($activityId);
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   新增
     */
    public function actionLimitTimeAdd()
    {
        try{
            if ( !isset($this->post['activityId']) || !isset($this->post['stime']) || !isset($this->post['pretime']) ){
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $activityId    = $this->post['activityId'];
            $stime         = $this->post['stime'];
            $pretime       = $this->post['pretime'];

            $postData = array(
                'activity_id'    => $activityId,
                'start_time'     => $stime,
                'pre_start_time' => $pretime,
                'is_crontab'     => 1,
                'time_crontab'   => date("Y-m-d", time())
            );

            if( isset($this->post['id']) ){
                $id = $this->post['id'];
                $obj = LimitTime::findOne($id);
                if(empty($obj)){
                    throw new MyException( ErrorCode::ERROR_OBJ);
                }
                //修改要重置所有的
                $this->RedPacketTime->setIsCrontab();
                $this->LimitTime->setIsCrontab();
            }else{
                $obj = new LimitTime();
            }

            $data = $obj->add($postData);
            $this->setData($data);
            $this->sendJson();
            return;
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   删除
     */
    public function actionLimitTimeDelete()
    {
        try {
            if ( !isset($this->get['id'])  ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = $this->get['id'];
            $this->LimitTime->del($id);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


}

?>