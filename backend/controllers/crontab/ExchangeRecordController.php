<?php
namespace backend\controllers\crontab;

use backend\models\Account;
use backend\models\ErrorCode;
use backend\models\Factory;
use backend\models\MyException;
use backend\models\rbac\AccountRelation;
use backend\models\redis\MyRedis;
use common\models\game\FivepkPlayerInfo;
use common\models\StoreItemExchangeRecordDay;
use common\services\ToolService;
use yii\web\Controller;
use Yii;

/**
 *  所有的定时统计都写在这里面
 * Class LogController
 * @package backend\controllers
 */
class ExchangeRecordController extends CrontabController
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
     *   把记录生成当天的统计记录  当天任何时候都可以运行
     */
    public function actionRecordToDay()
    {
        //$time  = time();
        //$stime = strtotime( date("Y-m-d 00:00:00", $time) ) ;
        //$etime = strtotime( date("Y-m-d 23:59:59", $time) ) ;
        //从5月1号开始到5月30号的数据到数据库

        $obj = new StoreItemExchangeRecordDay();
        for ($i = 0; $i < 40; $i++) {
            $stime = strtotime("2019-5-1 00:00:00");
            $etime = strtotime("2019-5-1 23:59:59");
            $stime += $i * 24 * 60 * 60;
            $etime += $i * 24 * 60 * 60;
            $obj->RecordToday($stime, $etime);
        }

        echo "success";
        return;
    }

    /**
     *   生成前一天的统计记录  每天0点过后运行 目前设置 00:00执行
     */
    public function actionRecordToBeforeDay()
    {
        $day = Yii::$app->request->get('day');
        $day = $day != "" ? $day : date('Y-m-d',time()-86400);
        $sday = $day;
        $eday = $day." 23:59:59";
        $stime = strtotime($sday);
        $etime = strtotime($eday);
        $obj = new StoreItemExchangeRecordDay();
        //删除今天的数据
        $obj->deleteByDay($sday);
        $obj->RecordToday($stime, $etime);
        echo "success";
        return;
    }

    /**
     *   签到--签到记录  每分钟执行一次
     */
    public function actionBackendSignData()
    {
        $redisKey  = "activitySignData";
        $time = time();
        $redis = new MyRedis();
        //取出定义分钟数的数据
        $etime = $time * 1000;
        $datas = $redis->ZRANGEBYSCORE($redisKey, 0 ,$etime);

        if(empty($datas)){
            return "no data";
        }
        $insertKey = array();
        $insertArr = array();
        foreach ($datas as $key=>$val){
            $val = json_decode($val,true);
            //插入到独立用户id中
//            $redis->ZADD($redisKey."_".$val['account_id'],$time+$key, $datas[$key]);
            //为了保证字段的顺序
            foreach ($val as $k=>$v){
                if( $k == "updated_time" ){
                    $val["update_time"] = $v/1000;
                    unset($val['updated_time']);
                }
                if( $k == "created_time" ){
                    $val["create_time"] = $v/1000;
                    unset($val['created_time']);
                }
            }
            $insertKey = " (".implode(",",array_keys($val)).") ";
            $val=array_map('addslashes',$val);
            $insertArr[] = " ('".implode("','",array_values($val))."') ";
        }
        $insertStr = implode(" , ",$insertArr);
        $columnStr = $insertKey;
        $sql = "insert into backend_sign_data {$columnStr} values {$insertStr}";
        Yii::$app->game_db->createCommand($sql)->query();
        //删除reids的值
        $redis->ZREMRANGEBYSCORE($redisKey, 0 ,$etime);
        return true;
    }


    /**
     * 排行榜放奖记录
     */
    public function actionRankAwardAccount()
    {
        $redisKey = "rankAwardAccount";
        $time = time();
        $redis = new MyRedis();
        //取出定义分钟数的数据
        $etime = $time * 1000;
        $datas = $redis->ZRANGEBYSCORE($redisKey, 0, $etime);

        if (empty($datas)) {
            return "no data";
        }
        $columns = array('account_id', 'ranking_type', 'order', 'create_time', 'award_type', 'award_num');
        $rows = array();
        foreach ($datas as $val) {
            $val = json_decode($val, true);

            $values = array(
                $val['account_id'],
                $val['ranking_type'],
                $val['order'],
                date("Y-m-d",$val['create_time']/1000),
            );

            if (isset($val['arward_json'])) {
                $awardArr = json_decode($val['arward_json'], true);
                unset($val['arward_json']);
                foreach ($awardArr as $awardKey => $awardArrValue) {
                    $valuesFromType = $values;
                    array_push($valuesFromType, $awardKey, $awardArrValue);
                    array_push($rows, $valuesFromType);
                }

            }

        }

        $class = new \common\models\activity\rank\RankAwardAccount();
        $commitBool = $class::getDb()->createCommand()->batchInsert($class::tableName(), $columns, $rows)->execute();
        if (!$commitBool) {
            echo "error";
            return false;
        }
        //删除reids的值
        $redis->ZREMRANGEBYSCORE($redisKey, 0, $etime);
        echo "success";
        return true;
    }


//    /**
//     *   报表-留存率  凌晨执行一次
//     */
//    public function actionRunStatRemainChannel(){
//        Factory::Timer()->runStatRemainChannel();
//        echo 'success';
//    }


    /**
     *   CpayRecord 订单提醒
     */
    public function actionCpayRecordRemind()
    {
        $accountIdArr = array();
        $redisKey = "CpayRecordRemind";
        $redis = new MyRedis();
        $time  = time();
        $datas = $redis->ZRANGE($redisKey, 0, $time*1000);
        $accountModel = new Account();
        $accountRelationModel = new AccountRelation();
        foreach ($datas as $key => $val)
        {
            $jsonArr =  json_decode($val, true);
            $popCode = $jsonArr['popCode'];
            $AccountObj = $accountModel->findByPopCode($popCode);
            $accountIdArr = $accountRelationModel->findAllParent($AccountObj['id'], $hasSelf = true);
        }
        $zdlObjs = $accountModel->getZDLFromStrIds($accountIdArr);
        $zdlIds  = array_column($zdlObjs,'id');
        $zdlId   = $zdlIds[0];
        $zdlSonIds = $accountRelationModel->findAllSon($zdlId, $hasSelf = true);

        $newIds = array_merge($accountIdArr,$zdlSonIds);
        $redis->ZREMRANGEBYSCORE($redisKey, 0 ,$time*1000);
        return json_encode(array('accountId' => $newIds));
    }
}
