<?php
namespace backend\controllers\crontab;

use common\models\DataGameListInfo;
use common\models\DataRoomInfoList;
use common\models\FivepkPrizeType;
use common\models\game\firephoenix\firephoenixSelf;
use common\models\record\BackendRecordItem;
use common\models\record\BackendRecordItemDay;
use common\models\record\BackendRecordItemDaySum;
use yii\web\Controller;
use Yii;

/**
 *  所有的定时统计都写在这里面
 * Class LogController
 * @package backend\controllers
 */
class BackendRecordItemController extends CrontabController
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
     *   获取每分钟的 商品 的信息 暂时只有奖券
     */
   public function actionMinuteRecord()
   {
       //获取所有的开启的游戏
       $DataGameListInfoObj = new DataGameListInfo();
       $opemGames = $DataGameListInfoObj->getOpenGame();
       //获取那些游戏有奖券
       $FivepkPrizeTypeObj = new FivepkPrizeType();
       $FivepkPrizeTypeObjs = $FivepkPrizeTypeObj->find()->where('prize_name = "奖券"')->asArray()->all();
       //循环每个游戏的轨迹，获取记录
       $time  = time();
       $etime = $time * 1000;
       $stime = ($time-60) * 1000;
       foreach ($FivepkPrizeTypeObjs as $prizeObj)
       {
           foreach ($opemGames as $game){
               if($prizeObj['game_type'] == $game['game_number']){
                   $chineseGameName = $game['game_name'];
                   $selfModel = Yii::$app->params[$chineseGameName]['selfModel'];
                   $obj = new $selfModel();
                   $obj->getPrizeHistory($stime, $etime, $prizeObj['id']);
               }
           }

       }
   }

    /**
     *    初始化所有奖券的统计
     */
   public function actionInitMinuteRecord()
   {
       //获取所有的开启的游戏
       $DataGameListInfoObj = new DataGameListInfo();
       $opemGames = $DataGameListInfoObj->getOpenGame();
       //获取那些游戏有奖券
       $FivepkPrizeTypeObj = new FivepkPrizeType();
       $FivepkPrizeTypeObjs = $FivepkPrizeTypeObj->find()->where('prize_name = "奖券"')->asArray()->all();

       $time = Yii::$app->request->get('time');
       $stime = strtotime($time);
       $etime = time();

       //删除这个表这段时间的数据
       $BackendRecordItemObj = new BackendRecordItem();
       $BackendRecordItemObj->deleteByTime($stime, $etime);

       //循环每个游戏的轨迹，获取记录
       $stime = $stime * 1000;
       $etime = $etime * 1000;



       foreach ($FivepkPrizeTypeObjs as $prizeObj)
       {
           foreach ($opemGames as $game){
               if($prizeObj['game_type'] == $game['game_number']){
                   $chineseGameName = $game['game_name'];
                   $selfModel = Yii::$app->params[$chineseGameName]['selfModel'];
                   $obj = new $selfModel();
                   $obj->getPrizeHistory($stime, $etime, $prizeObj['id']);
               }
           }

       }
   }

    /**
     *   把记录生成当天的统计记录  当天任何时候都可以运行
     */
   public function actionRecordToDay()
   {
       $time = time();
       //$stime = strtotime( date("Y-m-d 00:00:00", $time) ) ;
       //$etime = strtotime( date("Y-m-d 23:59:59", $time) ) ;
       echo "<pre>";
       for ($i=0;$i<40;$i++){
           $stime = strtotime("2019-6-1 00:00:00");
           $etime = strtotime("2019-6-1 23:59:59");
           $stime += $i*24*60*60;
           $etime += $i*24*60*60;
           $obj   = new BackendRecordItemDay();
           $obj->RecordToday($stime, $etime);
       }

//       $stime = strtotime("2019-5-2 00:00:00");
//       $etime = strtotime("2019-5-2 23:59:59");
//       $obj   = new BackendRecordItemDay();
//       $obj->RecordToday($stime,$etime);
       echo "success";
       return ;
   }

    /**
     *   生成前一天的统计记录  每天0点过后运行 目前设置 00:00执行
     */
    public function actionRecordToBeforeDay()
    {
        $tr = Yii::$app->game_db->beginTransaction();
        $time = time();
        $stime = strtotime( date("Y-m-d 00:00:00", $time) ) - 24*60*60 ;
        $etime = strtotime( date("Y-m-d 23:59:59", $time) ) - 24*60*60;
        $day   = date("Y-m-d",$stime);
        $BackendRecordItemDayObj = new BackendRecordItemDay();
        $BackendRecordItemDayObj->deleteByDay($day);
        $BackendRecordItemDayObj->RecordToday($stime,$etime);
        //记录每天一共送出多少奖券
        $BackendRecordItemDaySumObj = new BackendRecordItemDaySum();
        $BackendRecordItemDaySumObj->deleteByDay($day);
        $BackendRecordItemDaySumObj->RecordToday($stime);
        $tr->commit();
        echo "success";
        return ;
    }


}
