<?php

namespace backend\controllers\crontab;

use backend\models\Tool;
use common\models\DataGameListInfo;
use common\models\DataRoomInfoList;
use common\models\FgRecordGame;
use common\models\FgRecordPlayer;
use common\models\FivepkPath;
use common\models\game\FivepkAccount;
use common\models\game\FivepkDayContribution;
use common\models\game\FivepkMonthContribution;
use common\models\HitsReport;
use common\models\HitsReportExtend;
use common\models\GameCountDay;
use Yii;

/**
 *  所有的定时统计都写在这里面
 * Class LogController
 * @package backend\controllers
 */
class ReportController extends CrontabController
{

    /**
     *   第二天凌晨添加前一天的活跃人数
     */
    public function actionPlayerActivityNum()
    {
        $time              = time();
        $sDay              = date("Y-m-d", $time - 24 * 60 * 60);
        $eDay              = date("Y-m-d 23:59:59", $time - 24 * 60 * 60);
        $num               = FivepkAccount::find()->filterWhere(['between', 'last_login_time', $sDay, $eDay])->count();
        $GameCountDayModel = new GameCountDay();
        $postData          = array(
            'activity_player_num' => $num,
            'create_time'         => $sDay
        );
        $GameCountDayModel->deleteByDay($sDay);
        $GameCountDayModel->add($postData);
        return $sDay . " success";
    }

    /**
     *   fg玩家记录
     */
    public function actionFgRecordDay()
    {
        $day               = Yii::$app->request->get('day');
        $day               = $day != "" ? $day : date('Y-m-d', time() - 86400);
        $FgRecordPlayerObj = new FgRecordPlayer();
        $FgRecordPlayerObj->RecordPlayerDay($day);
        $FgRecordGameObj = new FgRecordGame();
        $FgRecordGameObj->RecordGameDay($day);
        return $day . "success";
    }

    /**
     *   每日贡献度报表
     */
    public function actionDayContribution()
    {
        //每天凌晨之后执行
        $day = Yii::$app->request->get('day');
        $day = $day != "" ? $day : date('Y-m-d', time() - 86400);
        FivepkDayContribution::dayContribution($day);
        return $day . "success";
    }

    /**
     *   每月贡献度报表
     */
    public function actionMonthContribution()
    {
        //每月1号执行
        $date = Yii::$app->request->get('day');
        $date = $date != "" ? $date : date('Y-m', time() - 86400);
        FivepkMonthContribution::monthContribution($date);
        return $date . "success";
    }


}
