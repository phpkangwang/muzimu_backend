<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/23
 * Time: 10:54
 */

namespace backend\models\services;


use backend\models\BaseModel;
use Yii;
use backend\models\AdminLog;
use common\models\DataErrorCode;
use common\models\DataGameListInfo;
use common\services\Messenger;
use common\services\ToolService;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

class MaintenanceService extends BaseModel
{
    public $game_list_info;
    public $model;

    public function init()
    {
        $this->game_list_info = ArrayHelper::map(DataGameListInfo::find()->filterWhere(['game_switch'=>0])->andFilterWhere(['>','game_number',0])->all(),'game_number','game_number');
        $this->model = new Messenger();
    }

    /**
     * 清除测试数据
     * @param $time
     * @param $seoid
     * @return mixed
     */
    public function deleteTestData($time,$seoid){
        try{
            if(empty($time)){
                throw new Exception('维护开始时间为空，不能清除测试数据！');
            }
            $game_db = Yii::$app->game_db;  //游戏数据库联系
            $db = Yii::$app->db;
            $this->model->data = $this->deleteFivepkDefault($game_db,$time,$seoid);  //删除轨迹数据
            $this->model->data += $this->deleteFivepk($game_db,$time,$seoid);       //删除测试数据
            $this->model->data += $this->deleteAdmin($db);                          //后台清除 jobs
            $this->model->data += $this->deleteBigRank($game_db,$time,$seoid);      //后台清除 大奖榜

            if(in_array(\Yii::$app->params['att'],$this->game_list_info)) {
                $this->model->data += $this->deleteAttdefault($game_db, $time, $seoid);      //att2轨迹
            }

            $this->model->message = $time;
        }catch(Exception $e){
            $this->model->status = 0;
            $this->model->message = $e->getMessage();
        }
        return $this->model;
    }

    /**
     * 清除大奖记录数据
     * @return mixed
     */
    public function deleteBigRankData(){
        try{
//            if(empty($time)){
//                throw new Exception('维护开始时间为空，不能清除测试数据！');
//            }

            $game_db = \Yii::$app->game_db;
            $this->model->data = $this->deleteAllBigRank($game_db);

        }catch(Exception $e){
            $this->model->status = 0;
            $this->model->message = $e->getMessage();
        }
        return $this->model;
    }

    /**
     * 删除轨迹数据
     * @param $db
     * @param $time
     * @param $seoid
     * @return int
     */
    protected function deleteFivepkDefault($db,$time,$seoid){
        $row = 0;
        $compare_sql = "DELETE fivepk_default_fivepk_compare FROM  fivepk_default, fivepk_default_fivepk_compare
            WHERE fivepk_default.fivepk_default_fivepk_id = fivepk_default_fivepk_compare.fivepk_default_fivepk_id
            AND	fivepk_default_fivepk_compare.last_time > '{$time}' AND fivepk_default.seoid = '{$seoid}'";
        $row += $db->createCommand($compare_sql)->execute();

        $fivepk_sql = "DELETE fivepk_default_fivepk FROM fivepk_default, fivepk_default_fivepk
            WHERE fivepk_default.fivepk_default_fivepk_id = fivepk_default_fivepk.id
            AND	fivepk_default_fivepk.last_time > '{$time}' AND fivepk_default.seoid = '{$seoid}'";
        $row += $db->createCommand($fivepk_sql)->execute();

        $default_sql = "DELETE FROM fivepk_default WHERE fivepk_default.last_time >  '{$time}' AND fivepk_default.seoid = '{$seoid}' ";
        $row += $db->createCommand($default_sql)->execute();

        //明星97 游戏轨迹
        $default_star97_sql = "DELETE fivepk_default_star97 FROM star97_default, fivepk_default_star97
            WHERE star97_default.star_default_id = fivepk_default_star97.id
            AND	star97_default.last_time >  '{$time}' AND star97_default.seoid = '{$seoid}'";
        $row += $db->createCommand($default_star97_sql)->execute();

        $star97_sql = "DELETE FROM star97_default WHERE star97_default.last_time >  '{$time}' AND star97_default.seoid = '{$seoid}'";
        $row += $db->createCommand($star97_sql)->execute();

        //paman游戏轨迹
        $paman_sql = "DELETE FROM paman_default WHERE paman_default.last_time >  '{$time}' AND paman_default.seoid = '{$seoid}'";
        $row += $db->createCommand($paman_sql)->execute();


        if(in_array(\Yii::$app->params['big_shark'],$this->game_list_info)) {
            $bigshark_compare_sql = "DELETE fivepk_default_fivepk_compare_bigshark FROM  fivepk_default_bigshark, fivepk_default_fivepk_compare_bigshark
            WHERE fivepk_default_bigshark.fivepk_default_fivepk_id = fivepk_default_fivepk_compare_bigshark.fivepk_default_fivepk_id
            AND	fivepk_default_fivepk_compare_bigshark.last_time > '{$time}' AND fivepk_default_bigshark.seoid = '{$seoid}'";
            $row += $db->createCommand($bigshark_compare_sql)->execute();


            $bigshark_fivepk_sql = "DELETE fivepk_default_fivepk_bigshark FROM fivepk_default_bigshark, fivepk_default_fivepk_bigshark
            WHERE fivepk_default_bigshark.fivepk_default_fivepk_id = fivepk_default_fivepk_bigshark.id
            AND	fivepk_default_fivepk_bigshark.last_time > '{$time}' AND fivepk_default_bigshark.seoid = '{$seoid}'";
            $row += $db->createCommand($bigshark_fivepk_sql)->execute();

            $bigshark_default_sql = "DELETE FROM fivepk_default_bigshark WHERE fivepk_default_bigshark.last_time >  '{$time}' AND fivepk_default_bigshark.seoid = '{$seoid}' ";
            $row += $db->createCommand($bigshark_default_sql)->execute();
        }

        if(in_array(\Yii::$app->params['fire_unicorn'],$this->game_list_info)) {
            $compare_fireunicorn_sql = "DELETE fivepk_default_fivepk_compare_fireunicorn FROM  fivepk_default_fireunicorn, fivepk_default_fivepk_compare_fireunicorn
            WHERE fivepk_default_fireunicorn.fivepk_default_fivepk_id = fivepk_default_fivepk_compare_fireunicorn.fivepk_default_fivepk_id
            AND	fivepk_default_fivepk_compare_fireunicorn.last_time > '{$time}' AND fivepk_default_fireunicorn.seoid = '{$seoid}'";
            $row += $db->createCommand($compare_fireunicorn_sql)->execute();


            $fivepk_fireunicorn_sql = "DELETE fivepk_default_fivepk_fireunicorn FROM fivepk_default_fireunicorn, fivepk_default_fivepk_fireunicorn
            WHERE fivepk_default_fireunicorn.fivepk_default_fivepk_id = fivepk_default_fivepk_fireunicorn.id
            AND	fivepk_default_fivepk_fireunicorn.last_time > '{$time}' AND fivepk_default_fireunicorn.seoid = '{$seoid}'";
            $row += $db->createCommand($fivepk_fireunicorn_sql)->execute();

            $default_fireunicorn_sql = "DELETE FROM fivepk_default_fireunicorn WHERE fivepk_default_fireunicorn.last_time >  '{$time}' AND fivepk_default_fireunicorn.seoid = '{$seoid}' ";
            $row += $db->createCommand($default_fireunicorn_sql)->execute();

        }

        if(in_array(\Yii::$app->params['super'],$this->game_list_info)) {
//            $compare_super_big_boss_sql = "DELETE fivepk_default_fivepk_compare_super_big_boss FROM  fivepk_default_super_big_boss, fivepk_default_fivepk_compare_super_big_boss
//            WHERE fivepk_default_super_big_boss.fivepk_default_fivepk_id = fivepk_default_fivepk_compare_super_big_boss.fivepk_default_fivepk_id
//            AND	fivepk_default_fivepk_compare_super_big_boss.last_time > '{$time}' AND fivepk_default_super_big_boss.seoid = '{$seoid}'";
//            $row += $db->createCommand($compare_super_big_boss_sql)->execute();


            $fivepk_super_big_boss_sql = "DELETE fivepk_default_fivepk_super_big_boss FROM fivepk_default_super_big_boss, fivepk_default_fivepk_super_big_boss
            WHERE fivepk_default_super_big_boss.fivepk_default_fivepk_id = fivepk_default_fivepk_super_big_boss.id
            AND	fivepk_default_fivepk_super_big_boss.last_time > '{$time}' AND fivepk_default_super_big_boss.seoid = '{$seoid}'";
            $row += $db->createCommand($fivepk_super_big_boss_sql)->execute();

            $default_super_big_boss_sql = "DELETE FROM fivepk_default_super_big_boss WHERE fivepk_default_super_big_boss.last_time >  '{$time}' AND fivepk_default_super_big_boss.seoid = '{$seoid}' ";
            $row += $db->createCommand($default_super_big_boss_sql)->execute();

        }



        return $row;
    }

    protected function deleteAttdefault($db,$time,$seoid){
        $row = 0;

        $compare_sql = "DELETE fivepk_default_fivepk_compare_att2 FROM  fivepk_default_att2, fivepk_default_fivepk_compare_att2
            WHERE fivepk_default_att2.fivepk_default_fivepk_id = fivepk_default_fivepk_compare_att2.fivepk_default_fivepk_id
            AND	fivepk_default_fivepk_compare_att2.last_time > '{$time}' AND fivepk_default_att2.seoid = '{$seoid}'";
        $row += $db->createCommand($compare_sql)->execute();

        $fivepk_sql = "DELETE fivepk_default_fivepk_att2 FROM fivepk_default_att2, fivepk_default_fivepk_att2
            WHERE fivepk_default_att2.fivepk_default_fivepk_id = fivepk_default_fivepk_att2.id
            AND	fivepk_default_fivepk_att2.last_time > '{$time}' AND fivepk_default_att2.seoid = '{$seoid}'";
        $row += $db->createCommand($fivepk_sql)->execute();

        $default_sql = "DELETE FROM fivepk_default_att2 WHERE fivepk_default_att2.last_time >  '{$time}' AND fivepk_default_att2.seoid = '{$seoid}' ";
        $row += $db->createCommand($default_sql)->execute();

        return $row;
    }

    /**
     * 删除其余测试数据
     * @param $db
     * @param $time
     * @param $seoid
     * @return int
     */
    protected function deleteFivepk($db,$time,$seoid){
        $row = 0;

        $tables = ['','fivepk_four_of_a_kind_ja_rate','fivepk_four_of_a_kind_two_ten_continue','fivepk_prize_att2',
            'fivepk_prize_big_plate','fivepk_prize_firephoenix','fivepk_prize_gold_crown','fivepk_prize_star97',
            'fivepk_compare_att2','fivepk_compare_big_plate','fivepk_compare_firephoenix','fivepk_compare_gold_crown',
        ];
        if(in_array(\Yii::$app->params['fire_unicorn'],$this->game_list_info)){
            $tables += ['fivepk_four_of_a_kind_ja_rate_fireunicorn','fivepk_four_of_a_kind_two_ten_continue_fireunicorn','fivepk_compare_fireunicorn','fivepk_prize_fireunicorn',];
        }
        if(in_array(\Yii::$app->params['big_shark'],$this->game_list_info)){
            $tables += ['fivepk_four_of_a_kind_ja_rate_bigshark','fivepk_prize_bigshark','fivepk_compare_bigshark',];
        }
        if(in_array(\Yii::$app->params['super'],$this->game_list_info)){
            $tables += ['fivepk_prize_super_big_boss'];
        }

        foreach($tables as $value){
            $delete = empty($value)?'fivepk_path':$value;
            $table = empty($value)?'':','.$value;
            $where = empty($value)?'': 'AND '.$value.'.fivepk_path_id = fivepk_path.id';

            $path_sql = "DELETE {$delete} FROM fivepk_path,fivepk_account {$table}
            WHERE fivepk_path.account_id = fivepk_account.account_id  $where
            AND fivepk_path.enter_time > {$time}
            AND fivepk_account.seoid = '{$seoid}'";
            $row += $db->createCommand($path_sql)->execute();
        }
        return $row;
    }

    /**
     * 清除大奖榜数据
     * @param $db
     * @param $time
     * @param $seoid
     */
    protected  function deleteBigRank($db,$time,$seoid){
        $row = 0;
        //时间戳转换为时间
        $time = date('Y-m-d H:i:s',($time / 1000) );
        $tables = ['fivepk_big_rank_big_plate','fivepk_big_rank_firephoenix','fivepk_big_rank_star97'];
        if(in_array(\Yii::$app->params['fire_unicorn'],$this->game_list_info)){
            $tables = array_merge($tables,['fivepk_big_rank_fireunicorn']);
        }
        if(in_array(\Yii::$app->params['big_shark'],$this->game_list_info)){
            $tables = array_merge($tables,['fivepk_big_rank_bigshark']);
        }
        if(in_array(\Yii::$app->params['att'],$this->game_list_info)){
            $tables = array_merge($tables,['fivepk_big_rank_att2']);
        }
        if(in_array(\Yii::$app->params['super'],$this->game_list_info)){
            $tables = array_merge($tables,['fivepk_big_rank_super_big_boss']);
        }
        if(in_array(\Yii::$app->params['paman'],$this->game_list_info)){
            $tables = array_merge($tables,['fivepk_big_rank_paman']);
        }

        foreach($tables as $value){

            $path_sql = "DELETE $value FROM {$value},fivepk_account
            WHERE {$value}.account_id = fivepk_account.account_id
            AND {$value}.last_time > '{$time}'
            AND fivepk_account.seoid = '{$seoid}'";
            $row +=$db->createCommand($path_sql)->execute();
        }
        return $row;
    }

    protected function deleteAdmin($db){
        $jobs_sql = "DELETE FROM jobs";
        $row = $db->createCommand($jobs_sql)->execute();
        return $row;
    }

    /**
     *
     * @param $db
     * @return int
     */
    protected  function deleteAllBigRank($db){
        $row = 0;

        $big_sql = "DELETE FROM fivepk_big_rank_big_plate";
        $row += $db->createCommand($big_sql)->execute();

        $firephoenix_sql = "DELETE FROM fivepk_big_rank_firephoenix";
        $row += $db->createCommand($firephoenix_sql)->execute();

        $star97_sql = "DELETE FROM fivepk_big_rank_star97";
        $row += $db->createCommand($star97_sql)->execute();

        if(in_array(\Yii::$app->params['fire_unicorn'],$this->game_list_info)) {
            $fireunicorn_sql = "DELETE FROM fivepk_big_rank_fireunicorn";
            $row += $db->createCommand($fireunicorn_sql)->execute();
        }
        if(in_array(\Yii::$app->params['big_shark'],$this->game_list_info)) {
            $bigshark_sql = "DELETE FROM fivepk_big_rank_bigshark";
            $row += $db->createCommand($bigshark_sql)->execute();
        }

        if(in_array(\Yii::$app->params['att'],$this->game_list_info)) {
            $bigshark_sql = "DELETE FROM fivepk_big_rank_att2";
            $row += $db->createCommand($bigshark_sql)->execute();
        }

        if(in_array(\Yii::$app->params['super'],$this->game_list_info)) {
            $super_big_boss_sql = "DELETE FROM fivepk_big_rank_super_big_boss";
            $row += $db->createCommand($super_big_boss_sql)->execute();
        }

        return $row;
    }


}