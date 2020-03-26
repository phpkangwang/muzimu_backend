<?php
namespace backend\controllers\crontab;

use backend\models\redis\MyRedis;
use backend\models\remoteInterface\remoteInterface;
use backend\models\Tool;
use Yii;

/**
 *  所有的定时统计都写在这里面
 * Class LogController
 * @package backend\controllers
 */
class NoticeController extends CrontabController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
        ];
    }

    /**
     *    通过定时任务发送系统公告
     */
    public function actionNoticeSendToCrontab()
    {
        //定时检查通告是否发送完成
        $sScore = 0;
        $eScore = time();
        $reidskey = "FivepkNotice";
        $redis = new MyRedis();
        //获取redis集合中小于这个时间的所有数据
        $datas = $redis->ZRANGEBYSCORE($reidskey, $sScore, $eScore);
        $Tool = new Tool();
        $Tool->myLog("定时发送公告任务开始了,队列的内容是:".json_encode($datas,JSON_UNESCAPED_UNICODE));
        $remoteInterfaceObj = new remoteInterface();
        foreach ($datas as $val) {
            $arr = json_decode($val, true);
            $notice = $arr['notice'];
            //推送客户端通知
            $Tool->myLog("开始发送公告了，公告内容是:".$notice);
            $remoteInterfaceObj->sendNotice($notice);
            $redis->ZREM($reidskey, $val);
        }
        return true;
    }


}
