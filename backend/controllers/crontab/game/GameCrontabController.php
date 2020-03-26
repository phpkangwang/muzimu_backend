<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/21
 * Time: 15:07
 */

namespace backend\controllers\crontab\game;

use backend\controllers\crontab\CrontabController;
use common\models\DataGameListInfo;
use Yii;


/**
 *  定时任务工作流程介绍:
 *  每隔一段时间($crontabTime)从redis里面读取轨迹，存到轨迹同时日，周，月表中 (要确保三个表的自增id是一致的)，
 *  每个一段时间统计一次prize表，然后每天0点把前一天的prize数据删除掉再重新统计一遍，可以减少数据量
 *
 * Class HfhController
 * @package backend\controllers\crontab\game
 */
class GameCrontabController extends CrontabController
{
    private $GameCrontabList = array();

    public function __construct($id, $module, array $config = [])
    {
        $this->initGameList();
        parent::__construct($id, $module, $config);
    }

    /**
     *   所有有的新轨迹放入循环队列
     */
    public function initGameList()
    {
        $DataGameListInfo = new DataGameListInfo();
        $openGame = $DataGameListInfo->getOpenGame();
        foreach ($openGame as $game){
            $rs = $this->GameNameToContr($game['shortGame']);
            if($rs != "" ){
                array_push($this->GameCrontabList,$rs);
            }
        }

    }

    private function GameNameToContr($gameName)
    {
        switch ($gameName){
            case "HFH":
                return new HfhController($gameName);
            case "DZB":
                return new DzbController($gameName);
            case "DBS":
                return new DbsController($gameName);
            case "ATT2":
                return new AttController($gameName);
            case "MXJ":
                return new MxjController($gameName);
            case "SBB":
                return new SbbController($gameName);
            case "PAM":
                return new PamController($gameName);
            case "BAO":
                return new BaoController($gameName);
            case "GHR":
                return new GhrController($gameName);
            case "HFHH":
                return new HfhhController($gameName);
            case "BYU":
                return new ByuController($gameName);
            default:
                return "";
        }
        return "";
    }

    /**
     *   所有游戏轨迹 从Redis 进入数据库
     */
    public function actionTransferMinute()
    {
        $time = time();
        foreach ($this->GameCrontabList as $gameCrontab){
            $gameCrontab->TransferMinute($time);
            echo $gameCrontab->gameObj->gameName."执行TransferMinute完毕</br>";
        }
    }

    /**
     *   每天执行前一天的数据  第二天删除前一天的数据
     */
    public function actionRecordToBeforeDay()
    {
        set_time_limit(3600);
        $day = Yii::$app->request->get('day');
        $day = $day != "" ? $day : date('Y-m-d H:i', time() - 86400);
        foreach ($this->GameCrontabList as $gameCrontab){
            $gameCrontab->RecordToBeforeDay($day);
        }
    }

    public function actionLocusInfo()
    {
        $gameName = Yii::$app->request->get('gameName');
        $rs = $this->GameNameToContr($gameName);
        $rs->LocusInfo();
    }

    /**
     *   重新把这个游戏的轨迹计算到prizeday表
     */
    public function actionInitPrizeDay()
    {
        set_time_limit(3600);
        $day = Yii::$app->request->get('day');
        $day = $day != "" ? $day : date('Y-m-d H:i', time() - 86400);
        $gameName = Yii::$app->request->get('gameName');
        $rs = $this->GameNameToContr($gameName);
        $rs->prizeDay($day);
        echo "success";
    }
}