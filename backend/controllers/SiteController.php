<?php

namespace backend\controllers;

use backend\models\redis\MyRedis;
use backend\models\remoteInterface\remoteInterface;
use backend\models\Tool;
use common\models\DataGameListInfo;
use common\models\game\att2\AttPrizeDay;
use common\models\game\big_plate\BigPlatePrizeDay;
use common\models\game\big_shark\BigSharkPrizeDay;
use common\models\game\firephoenix\FirephoenixPrizeDay;
use common\models\game\FivepkDayContribution;
use common\models\game\paman\FivepkPlayerPamanSetting;
use common\models\game\sbb\SbbPrizeDay;
use common\models\game\star97\Star97PrizeDay;
use common\models\HitsReport;
use common\models\HitsReportExtend;
use common\services\IpArea;
use common\services\JgPush;
use Yii;
use yii\web\Controller;
use common\models\LoginForm;

/**
 * Site controller
 */
class SiteController extends Controller
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
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     *   转移旧的人气报表
     */
    public function actionOldHitReport()
    {
        set_time_limit(3600);  //设置超时时间
        $rs        = array();
        $obj       = HitsReport::find()->where('game_type <> 1 and game_type <> 3')->joinWith('extend')->asArray()->all();
        $valuesArr = array();
        foreach ($obj as $key => $val) {
            $values                             = "";
            $rs[$key]['create_time']            = $val['create_time'];
            $rs[$key]['game_type']              = $val['game_type'];
            $rs[$key]['people']                 = $val['new_player'];
            $rs[$key]['profit_json']['合计']      = $val['profit'];  //盈利
            $rs[$key]['people_game_json']['合计'] = $val['gaming_player']; //游戏人数
            $rs[$key]['play_num_json']['合计']    = $val['total_play']; //总玩局数
            $rs[$key]['odds_json']['合计']        = $val['odds']; //游戏几率
            $rs[$key]['award_json']['合计']       = $val['winning']; //中奖率
            foreach ($val['extend'] as $k => $v) {
                $rs[$key]['odds_type']      = "";  //盈利
                $rs[$key]['profit_json'][$v['room_type']]      = $v['profit'];  //盈利
                $rs[$key]['people_game_json'][$v['room_type']] = $v['gaming_player']; //游戏人数
                $rs[$key]['play_num_json'][$v['room_type']]    = $v['total_play']; //总玩局数
                $rs[$key]['odds_json'][$v['room_type']]        = $v['odds']; //游戏几率
                $rs[$key]['award_json'][$v['room_type']]       = $v['winning']; //中奖率
            }

            $rs[$key]['profit_json']      = json_encode($rs[$key]['profit_json'], JSON_UNESCAPED_UNICODE);  //盈利
            $rs[$key]['people_game_json'] = json_encode($rs[$key]['people_game_json'], JSON_UNESCAPED_UNICODE); //游戏人数
            $rs[$key]['play_num_json']    = json_encode($rs[$key]['play_num_json'], JSON_UNESCAPED_UNICODE); //总玩局数
            $rs[$key]['odds_json']        = json_encode($rs[$key]['odds_json'], JSON_UNESCAPED_UNICODE); //游戏几率
            $rs[$key]['award_json']       = json_encode($rs[$key]['award_json'], JSON_UNESCAPED_UNICODE); //中奖率
            if (!empty($rs[$key])) {
                $values      .= " ('" . implode("','", $rs[$key]) . "') ";
                $valuesArr[] = $values;
            }
        }
        $keys      = array_keys($rs[0]);
        $columnStr = implode(",", $keys);
        $valuesStr = implode(" , ", $valuesArr);
        $sql       = "insert into backend_record_hits ($columnStr) values {$valuesStr}";
        Yii::$app->game_db->createCommand($sql)->query();
    }


    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionClearRedis()
    {
        Yii::$app->redis->select(Yii::$app->params['redisCommonDatabase']);
        Yii::$app->redis->flushdb();
        echo "redis clear success!!!";
    }

    public function actionFlushRedis()
    {
        Yii::$app->redis->FLUSHALL();
        echo "redis flush success!!!";
    }

    public function actionRedisInfo()
    {
        $key      = Yii::$app->request->get("key");
        $type     = Yii::$app->request->get("type");
        $dataBase = Yii::$app->request->get("dataBase");
        $redis    = new MyRedis();
        if ($type == "add") {
            if ($dataBase != "") {
                Yii::$app->redis->select($dataBase);
                echo Yii::$app->redis->get($key);
                die;
            }
            echo $redis->get($key);
            die;
        } else if ($type == "zadd") {
            if ($dataBase != "") {
                Yii::$app->redis->select($dataBase);
                echo Yii::$app->redis->ZRANGE($key, 0, -1);
                die;
            }
            $data = $redis->ZRANGE($key, 0, -1);
            print_r($data);
            die;
        }
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    public function actionYzm()
    {
        $yzmId        = Yii::$app->request->get('yzmId');
        $code         = Tool::getYzmCode();
        $MyRedisModel = new MyRedis();
        $MyRedisModel->set("yzmCode" . $yzmId, $code);
        Tool::getYzmImage($code);
    }

    public function actionJsonp()
    {
        $jsoncallback = htmlspecialchars($_REQUEST['jsonpcallback']);
        $arr          = array('name' => "wangk", 'id' => 20);
        echo $jsoncallback . "(" . json_encode($arr) . ")";
    }

    //下载log
    public function actionGetLog()
    {
        $fileName = Yii::$app->request->get('fileName');
        $file     = Yii::getAlias("@myLog") . DIRECTORY_SEPARATOR . $fileName;
        header('Content-Type:application/force-download');
        header('Content-Disposition:attachment;filename='.$fileName);
        readfile($file);
    }


    public function actionTest3()
    {
        $type = Yii::$app->request->get('type');
        $key  = Yii::$app->request->get('key');

        $redis    = new MyRedis();
        $redisKey = $key;
        $time     = time();
        if ($type) {
            $etime = $time * 1000;
            $datas = $redis->ZRANGEBYSCORE($redisKey, 1570788160000, $etime);
            print_r($datas);
            die();
        }

        //删除reids的值
        $redis->ZREMRANGEBYSCORE('locusGhr', 1570788160000, 2570788160000);


    }


    public function actionTestLocus()
    {
        $accountArr = [11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40];
        $machineArr = [399,400,401,402,403,404,405,406,407,408,418,419,420,421,422,423];
        $MyRedisModel = new MyRedis();
        $key = "locusAtt";
        $time = time();
        for ($i=0;$i<1;$i++){
            $accountId = array_rand($accountArr);
            $machineId = array_rand($machineArr);
            $unique_str = $time.rand(100,999).$i;
            $val1 = '{"machine_auto_id":'.$machineId.',"pop_code":"XO","create_time":1566370286445,"first_hand_cards":"19, 9, 48, 45, 2","first_prize_id":28,"room_index":2,"bet":100,"account_id":'.$accountId.',"unique_str":"'.$unique_str.'","prize_id":28,"keep_cards":"0, 3, 1, 2","win_score":200,"prize_out_id":1,"machine_play_count":953,"credit":364132,"win":200,"prize_two_id":28,"coin":906}';
            $val2 = '{"unique_str":"'.$unique_str.'","create_time":1566370288705,"prize_id":28,"keep_cards":"0,1,2,3","win_score":200,"prize_out_id":1,"win":200,"prize_two_id":28,"second_hand_cards":"19, 9, 48, 45, 17"}';
            $val3 = '{"unique_str":"'.$unique_str.'","create_time":1566370291218,"bonus_score":100,"is_complete":1,"win":200}';
            $MyRedisModel->ZADD($key,$time*1000+1+3*$i,$val1);
            $MyRedisModel->ZADD($key,$time*1000+2+3*$i,$val2);
            $MyRedisModel->ZADD($key,$time*1000+3+3*$i,$val3);
        }
    }


    public function actionInitUser()
    {
        $accountId  = Yii::$app->request->get('accountId');
        $key        = Yii::$app->request->get('key');
        $val        = Yii::$app->request->get('val');
        $sql = "update fivepk_player_info set {$key} = '{$val}'  where account_id = '{$accountId}'";
        Yii::$app->game_db->createCommand($sql)->query();
        echo "success";
    }

    public function actionInitReservation()
    {
        $remoteInterfaceObj = new remoteInterface();
        $remoteInterfaceObj->setReservationTime(1);
        echo "success";
    }
}
