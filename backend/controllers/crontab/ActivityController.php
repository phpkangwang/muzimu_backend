<?php
namespace backend\controllers\crontab;

use backend\models\Prototype;
use backend\models\redis\MyRedis;
use backend\models\remoteInterface\remoteInterface;
use common\models\activity\limit\Limit;
use common\models\activity\limit\LimitTime;
use common\models\activity\redPacket\RedPacket;
use common\models\activity\redPacket\RedPacketTime;
use common\models\DataGameListInfo;
use common\models\DataRoomInfoList;
use common\models\pay\platform\PayAbstract;
use common\models\pay\platform\PayManagement;
use common\models\RecordDiamond;
use yii\web\Controller;
use Yii;
use backend\models\Tool;

/**
 *  所有的定时统计都写在这里面
 * Class LogController
 * @package backend\controllers
 */
class ActivityController extends CrontabController
{
    public function actionRedPackNotice()
    {
        //获取所有开启的游戏
        $DataGameListInfoModel = new DataGameListInfo();
        $OpenGame = $DataGameListInfoModel->getOpenGame();

        //发送三条数据
        $time = date("G:i",time());
        //查找所有的红包来袭活动的开启时间
        $TimeModel = new RedPacketTime();
        $activityTimeObjs = $TimeModel->tableList();

        foreach ($activityTimeObjs as $activityTimeObj){
            $stime =[
                date("G:i",(strtotime($activityTimeObj['start_time']))-120),
                date("G:i", (strtotime($activityTimeObj['start_time'])-110)),
                date("G:i", (strtotime($activityTimeObj['start_time']))),
            ];
            if( in_array($time, $stime) ){
                //发送公告
                $ActivityObj = RedPacket::findOne($activityTimeObj['activity_id']);
                //如果这个游戏的红包来袭关了就不发公告
                if($ActivityObj->is_open == 0){
                    continue;
                }

                //获取游戏名称和场次名称
                $gameName = "";
                foreach ($OpenGame as $val) {
                    if ($val['game_number'] == $ActivityObj['game_type']) {
                        $gameName = $val['game_name'];
                    }
                }
                //获取房间场次名称
                $DataRoomInfoListModel = new DataRoomInfoList();
                $DataRoomInfoListObj = $DataRoomInfoListModel->findByGameIndex($ActivityObj['game_type'], $ActivityObj['room_index']);
                $roomName = $DataRoomInfoListObj['name'];
                $content = "红包来袭送钻活动,将于".$activityTimeObj['start_time']."-".$activityTimeObj['end_time'].
                    "在".$gameName.$roomName."准时开启,祝君好运!";
                //发送公告
                $remoteInterfaceModel = new remoteInterface();
                $remoteInterfaceModel->sendNotice($content);
            }
        }
        echo "success";
    }

    public function actionLimitNotice()
    {
        //获取所有开启的游戏
        $DataGameListInfoModel = new DataGameListInfo();
        $OpenGame = $DataGameListInfoModel->getOpenGame();

        //发送三条数据
        $time = date("G:i",time());
        //查找所有的红包来袭活动的开启时间
        $TimeModel = new LimitTime();
        $activityTimeObjs = $TimeModel->tableList();

        foreach ($activityTimeObjs as $activityTimeObj){
            $stime =[
                date("G:i",strtotime($activityTimeObj['start_time']) - 120),
                date("G:i",(strtotime($activityTimeObj['start_time']) - 60)),
                date("G:i",(strtotime($activityTimeObj['start_time']))),
            ];
            if( in_array($time, $stime) ){
                //发送公告
                $ActivityObj = Limit::findOne($activityTimeObj['activity_id']);

                //如果这个游戏的红包来袭关了就不发公告
                if($ActivityObj->is_open == 0){
                    continue;
                }

                //获取游戏名称和场次名称
                $gameName = "";
                foreach ($OpenGame as $val) {
                    if ($val['game_number'] == $ActivityObj['game_type']) {
                        $gameName = $val['game_name'];
                    }
                }
                //获取房间场次名称
                $DataRoomInfoListModel = new DataRoomInfoList();
                $DataRoomInfoListObj = $DataRoomInfoListModel->findByGameIndex($ActivityObj['game_type'], $ActivityObj['room_index']);
                $roomName = $DataRoomInfoListObj['name'];
                $content = "全盘水果奖励翻倍活动,将于".$activityTimeObj['start_time'].
                    "在".$gameName.$roomName."准时开启,祝君好运!";
                //发送公告
                $remoteInterfaceModel = new remoteInterface();
                $remoteInterfaceModel->sendNotice($content);
            }
        }
        echo "success";
    }

    /**
     *   初始化昨天的红包来袭送钻报表
     */
    public function actionRedPackRecordToBeforeDay()
    {
        set_time_limit(3600);  //设置超时时间
        $day = Yii::$app->request->get('day');
        if( $day == ""){
            $day = date('Y-m-d', time()-86400);
        }

        //初始化这一天的红包来袭数据
        $RecordDiamondModel = new  RecordDiamond();
        $RecordDiamondModel->iniReportDiamond($day);
        echo "success";
    }

    /**
     *   定时彩金复位
     */
    public function actionBonusRecover()
    {
        set_time_limit(3600);  //设置超时时间
        $gameNames = ['PAM'];
        foreach ($gameNames as $gameName){
            $chineseGameName = Yii::$app->params['game'][$gameName];
            $seoModel   = Yii::$app->params[$chineseGameName]['seoModel'];
            //获取所有的机台id
            $seoModelObj    = new $seoModel;
            $MachineList    = $seoModelObj->tableList();
            $MachineIds = array_column($MachineList,'auto_id');
            $seoModelObj->setRecover($MachineIds,$gameName);
        }
        return true;
    }

    /**
     *   明星97定时彩金复位
     */
    public function actionMx97BonusRecover()
    {
        set_time_limit(3600);  //设置超时时间
        $gameType = 6;
        //获取所有的机台id
        $DataRoomInfoList = new DataRoomInfoList();
        $roomList         = $DataRoomInfoList->findByGame($gameType);
        $seoModelObj      = new \common\models\game\star97\MachineListStar97();
        foreach ($roomList as $value) {
            $seoModelObj->setGift('MXJ', $value['room_index']);
        }

        //游戏缓存刷新
        $remoteInterface = new  remoteInterface();
        $remoteInterface->refreshGameCache();

        echo 'ok';

    }

    /**
     * 支付回调 修改订单加钻
     */
    public function actionCallBackRun()
    {
        $redis    = new MyRedis();
        $dataList = $redis->HGTALL(PayAbstract::UP_PAY_STATUS_REDIS);

        $PayManagement = new PayManagement();
        $Prototype     = new Prototype($PayManagement);;
        $hashKey = '';

        if (!is_array($dataList) || empty($dataList)) {
            echo 'empty';
            return false;
        }

        foreach ($dataList as $key => $data) {

            //hash里第一个是key第二个是值
            if (!Tool::isEven($key)) {
                //只处理一次 先删除以防报错下次再次执行
                $redis->delCacheHash(PayAbstract::UP_PAY_STATUS_REDIS, $hashKey);
                $data = json_decode($data, true);
                if (isset($data['order_no']) && isset($data['trade_no'])) {
                    $class          = $Prototype->deepCopy();
                    $class->tradeNo = $data['trade_no'];
                    $class->isQueue = 1;
                    $status         = $class->upPayStatus([
                        'operateName' => 'system',
                        'ordernumber' => $data['order_no'],
                        'sysnumber'   => $data['trade_no'],
                    ]);
                }
            }

            $hashKey = $data;
        }

    }

}
