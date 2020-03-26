<?php
namespace backend\controllers\crontab;

use common\models\ContributionReport;
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
class ContributionRecordController extends CrontabController
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
     *   把记录生成当天的统计记录  当天任何时候都可以运行
     */
   public function actionRecordToDay()
   {
       $obj = new ContributionReport();
       $obj->RecordToday();
       echo "success";
       return ;
   }

    /**
     *   生成前一天的统计记录  每天0点过后运行 目前设置 00:00执行
     */
    public function actionRecordToBeforeDay()
    {
        $obj = new ContributionReport();
        $obj->RecordToBeforeDay();
        echo "success";
        return ;
    }


}
