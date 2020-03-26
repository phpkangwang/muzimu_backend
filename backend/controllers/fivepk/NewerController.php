<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-11-20
 * Time: 13:58
 */

namespace backend\controllers\fivepk;


use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\services\PlayerService;
use common\models\game\DataFivepkNewer;
use common\models\game\DataFivepkNewerContributionRate;
use common\models\game\DataFivepkNewerWinType;
use common\models\game\FivepkPlayerNewer;
use Yii;

class NewerController extends MyController
{

    /**
     *  游戏新人奖
     * @param null $room_type
     * @param null $share
     * @return mixed
     */
    public function actionGameFreshReward()
    {
        try {
            if ( !isset($this->get['gameName']) || !isset($this->get['level']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $gameName = $this->get['gameName'];
            $level    = $this->get['level'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType = Yii::$app->params[$chineseGameName]['gameType'];
            $share    = isset($this->get['share']) ? $this->get['share'] : "";

            $params = array(
                'level' => $level,
                'share' => $share
            );
            $data = $this->DataFivepkNewer->tableList($gameType, $params);


            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  游戏新人奖添加-修改
     */
    public function actionGameFreshRewardAdd()
    {
        try {
            if ( !isset($this->post['shareCount']) || !isset($this->post['level']) || !isset($this->post['rate'])
                || !isset($this->post['newerSwitch']) || !isset($this->post['newerContributionRateId']) || !isset($this->post['winTypeId']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }

            $id          = isset( $this->post['id'] ) ? $this->post['id'] : "";
            $gameName    = $this->post['gameName'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType    = Yii::$app->params[$chineseGameName]['gameType'];

            $shareCount              = $this->post['shareCount'];
            $level                   = $this->post['level'];
            $rate                    = $this->post['rate'];
            $newerSwitch             = $this->post['newerSwitch'] == 1 ? 1 : 0;
            $newerContributionRateId = $this->post['newerContributionRateId'];
            $winTypeId               = $this->post['winTypeId'];
            $postData = array(
                'game_type'          => $gameType,
                'share_count'        => $shareCount,
                'room_index'         => $level,
                'rate'               => $rate,
                'newer_switch'       => $newerSwitch,
                'newer_contribution_rate_id' => $newerContributionRateId,
                'win_type_id'        => $winTypeId,
            );
            if( !empty($id) )
            {
                //修改
                $obj = DataFivepkNewer::findOne($id);
                $obj->add($postData);
            }else{
                //新增
                $this->DataFivepkNewer->add($postData);
            }
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    //新人奖列表-删除
    public function actionGameFreshRewardDelete()
    {
        try {
            if ( !isset($this->post['ids']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $ids     = $this->post['ids'];
            $ids     = explode(',', $ids);
            DataFivepkNewer::DeleteAll(['id'=>$ids]);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   新人奖列表
     */
    public function actionFreshAwardList()
    {
        try {
            if ( !isset($this->get['gameName']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $gameName = $this->get['gameName'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType = Yii::$app->params[$chineseGameName]['gameType'];
            $data = $this->DataFivepkNewerWinType->tableList($gameType);
            foreach ( $data as $key => $val)
            {
                $data[$key]['update_time'] = date("Y-m-d H:i:s",$val['update_time']/1000);
            }
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  新人奖列表添加-修改
     */
    public function actionFreshAwardAdd()
    {
        try {
            if ( !isset($this->post['gameName']) || !isset($this->post['winType']) || !isset($this->post['winTypeRate'])
                || !isset($this->post['limitCount']) || !isset($this->post['comment']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }

            $id          = isset( $this->post['id'] ) ? $this->post['id'] : "";
            $gameName    = $this->post['gameName'];
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $gameType    = Yii::$app->params[$chineseGameName]['gameType'];
            $winType     = $this->post['winType'];
            $winTypeRate = $this->post['winTypeRate'];
            $limitCount  = $this->post['limitCount'];
            $comment     = $this->post['comment'];
            $postData = array(
                'win_type' => $winType,
                'win_type_rate' => $winTypeRate,
                'limit_count' => $limitCount,
                'comment' => $comment,
                'game_type' => $gameType,
                'update_time' => $this->time*1000,
            );
            if( !empty($id) )
            {
                //修改
                $obj = DataFivepkNewerWinType::findOne($id);
                $obj->add($postData);
            }else{
                //新增
                $postData['create_time'] = $this->time*1000;
                $this->DataFivepkNewerWinType->add($postData);
            }
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    //新人奖列表-删除
    public function actionFreshAwardDelete()
    {
        try {
            if ( !isset($this->post['ids']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $ids     = $this->post['ids'];
            $ids     = explode(',', $ids);
            DataFivepkNewerWinType::DeleteAll(['id'=>$ids]);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   门槛列表
     */
    public function actionDoorSillList()
    {
        $data = $this->DataFivepkNewerContributionRate->tableList();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *  门槛列表添加-修改
     */
    public function actionDoorSillAdd()
    {
        try {
            if ( !isset($this->post['preNewerContributionRate']) || !isset($this->post['sufNewerContributionRate']) || !isset($this->post['preNewerGap'])
                || !isset($this->post['sufNewerGap'])  ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }

            $id                       = isset( $this->post['id'] ) ? $this->post['id'] : "";
            $preNewerContributionRate = $this->post['preNewerContributionRate'];
            $sufNewerContributionRate = $this->post['sufNewerContributionRate'];
            $preNewerGap              = $this->post['preNewerGap'];
            $sufNewerGap              = $this->post['sufNewerGap'];
            $postData = array(
                'pre_newer_contribution_rate' => $preNewerContributionRate,
                'suf_newer_contribution_rate' => $sufNewerContributionRate,
                'pre_newer_gap' => $preNewerGap,
                'suf_newer_gap' => $sufNewerGap,
                'update_time' => $this->time*1000,
            );
            if( !empty($id) )
            {
                //修改
                $obj = DataFivepkNewerContributionRate::findOne($id);
                $obj->add($postData);
            }else{
                //新增
                $postData['create_time'] = $this->time*1000;
                $this->DataFivepkNewerContributionRate->add($postData);
            }
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    //门槛列表-删除
    public function actionDoorSillDelete()
    {
        try {
            if ( !isset($this->post['ids']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $ids     = $this->post['ids'];
            $ids     = explode(',', $ids);
            DataFivepkNewerContributionRate::DeleteAll(['id'=>$ids]);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


}