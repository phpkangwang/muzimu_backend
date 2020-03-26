<?php
namespace console\controllers;

use backend\models\redis\MyRedis;
use backend\models\socket\MySocket;
use Yii;
use yii\console\Controller;
use PHPSocketIO\SocketIO;

/**
 *  定时任务
 */
class TaskController extends Controller
{
    /**
     *   定时发送-----系统公告
     */
    public function actionFivepkNotice()
    {
            $sScore = 0;
            $eScore = time();
            $reidskey = "FivepkNotice";
            //获取redis集合中小雨这个时间的所有数据
            $myRedis = new MyRedis();
            $datas = $myRedis->ZRANGE($reidskey, $sScore ,$eScore);
            foreach ($datas as $val)
            {
                $arr = json_decode($val, true);
                $url = $arr['url'];
                $notice = $arr['notice'];
                echo $url;die;
            }
    }


}/* class 结束 */
?>
