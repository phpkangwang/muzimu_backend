<?php
namespace backend\controllers\platform\thwj\backend\controllers\fivepk;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use common\models\DataGameListInfo;
use common\models\game\base\GameBase;
use common\models\game\snow_leopard\FivepkSeoSnowLeopard;
use Yii;

/**
 * @desc 机台管理
 * @package backend\controllers
 */
class MachineController extends BaseModel
{

    /**
     * 游戏信息更新
     * @param $post
     * @throws \yii\db\Exception
     */
    public function GameUpdate($post)
    {
        try{
            if( !isset( $post['game_name'] )        ||
                !isset( $post['score'] )            ||
                !isset( $post['game_notice'])       ||
                !isset( $post['game_res_url'] )     ||
                !isset( $post['game_index'] )       ||
                !isset( $post['game_version'])      ||
                !isset( $post['game_version_id'] )  ||
                !isset( $post['game_switch'])       ||
                !isset( $post['activity_switch'] )  ||
                !isset( $post['id'])
            ){
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $id = $post['id'];
            $postData = array();
            $game_white_ip = isset( $post['game_white_ip'] ) ? $post['game_white_ip'] : "";
            if( !empty($game_white_ip)){
                $postData['game_white_ip'] = $game_white_ip;
            }
            $game_server_ip = isset( $post['game_server_ip'] ) ? $post['game_server_ip'] : "";
            if( !empty($game_server_ip)){
                $postData['game_server_ip'] = $game_server_ip;
            }
            $game_server_port = isset( $post['game_server_port'] ) ? $post['game_server_port'] : "";
            if( !empty($game_server_port)){
                $postData['game_server_port'] = $game_server_port;
            }
            $postData['game_name']        = $post['game_name'];
            $postData['score']            = $post['score'];
            $postData['coin']             = isset( $post['coin'] ) ? $post['coin'] : 0;
            $postData['game_notice']      = $post['game_notice'];
            $postData['game_res_url']     = $post['game_res_url'];
            $postData['activity_switch']  = $post['activity_switch'];
            $postData['game_index']       = $post['game_index'];
            $postData['game_version']     = $post['game_version'];
            $postData['game_version_id']  = $post['game_version_id'];
            $postData['game_switch']      = $post['game_switch'];
            $switch = $post['game_switch'];
            $DataGameListInfoObj = DataGameListInfo::findOne($id);
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            if( $DataGameListInfoObj->game_switch != $switch ){
                if( $switch == $this->DataGameListInfo->gameSwitchOpen && $DataGameListInfoObj->game_number == 0){
                    FivepkSeoSnowLeopard::lightWinRecord(2);
                    //说明是开服
                    $postData['last_open_time']  = time();
                    $last_close_time             = $DataGameListInfoObj['last_close_time'];
                    //上次关服时间存在
//                    if( !empty($last_close_time) ){
//                        //清除测试数据
//                        $this->MaintenanceService->deleteTestData($last_close_time, 'XO');
//                    }
                    $DataGameListInfoObj->add($postData);
                    $this->remoteInterface->setReservationTime($postData['last_open_time'] - $last_close_time);
                }else if( $switch == $this->DataGameListInfo->gameSwitchClose ){
                    FivepkSeoSnowLeopard::lightWinRecord(1);
                    //关服
                    $postData['last_close_time']  = time();
                    $DataGameListInfoObj->add($postData);
                }else{
                    $DataGameListInfoObj->add($postData);
                }
            }else{
                $DataGameListInfoObj->add($postData);
            }
            if( empty($DataGameListInfoObj) ){
                throw new MyException( ErrorCode::ERROR_OBJ );
            }
            $this->remoteInterface->refreshMachine();
            $tr->commit();
            return;
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   删除机台
     */
    public function deleteMachine($gameName, $ids)
    {
        $GameBaseObj = new GameBase();
        $GameObj     = $GameBaseObj->initGameObj($gameName);
        $gameType          = $GameObj->gameType;

        $ids = explode(",", $ids);
        $seoModel = $GameObj->getModelMachine();
        $seoModelObjs = $seoModel::find()
            ->where(['in','auto_id' , $ids])
            ->asArray()
            ->all();
        $seoModelObjs = $this->Tool->ArrSort($seoModelObjs, "order_id", "desc");
        foreach ($seoModelObjs as $seoModelObj){
            $this->remoteInterface->deleteMachine($gameType, $seoModelObj['seo_machine_id']);
        }
    }

}