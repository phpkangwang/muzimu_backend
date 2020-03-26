<?php

namespace common\models\game;

use backend\models\BaseModel;
use backend\models\Factory;
use backend\models\remoteInterface\remoteInterface;
use backend\models\Tool;
use common\models\DataGameListInfo;
use common\models\game\ghr\GhrRedisLocusHandler;
use common\services\Messenger;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 *  游戏清除
 * Class AccessPoints
 * @package common\models\game
 */
class GameClear extends BaseModel
{

    public $game_list_info;
    public $model;
    public $db;
    public $time;//毫秒
    public $seoId;
    const MAX_LIMIT = 10000;//最多一次清除多少条

    public function init()
    {
        $this->game_list_info = ArrayHelper::map(DataGameListInfo::find()->andFilterWhere(['>', 'game_number', 0])->all(), 'game_number', 'game_number');
        $this->model          = new Messenger();
    }

    //清除测试数据
    public function deleteTestData($time, $seoId)
    {
        try {
            if (empty($time)) {
                throw new Exception('维护开始时间为空，不能清除测试数据！');
            }

            $this->time  = $time;//毫秒
            $this->seoId = $seoId;

            $classArr = [
                \Yii::$app->params['fire_phoenix'] => '\common\models\game\HFH'
                , \Yii::$app->params['big_plate']  => '\common\models\game\DZB'
                , \Yii::$app->params['star97']     => '\common\models\game\MXJ'
                , \Yii::$app->params['att']        => '\common\models\game\ATT'
                , \Yii::$app->params['super']      => '\common\models\game\SBB'
                , \Yii::$app->params['paman']      => '\common\models\game\PAMAN'
                , \Yii::$app->params['big_shark']  => '\common\models\game\DBS'
                , \Yii::$app->params['xb']         => '\common\models\game\BAO'
                , \Yii::$app->params['ghr']        => '\common\models\game\GHR'
                , \Yii::$app->params['byu']        => '\common\models\game\BYU'
            ];
            foreach ($classArr as $key => $value) {
                if (in_array($key, $this->game_list_info)) {
//                    echo $value.PHP_EOL;
                    $class = new $value;
                    $class->clear($time, $seoId);
                }
            }

            $this->deletePublicByPlat();

            $this->model->message = $time;
        } catch (Exception $e) {
            $this->model->status  = 0;
            $this->model->message = $e->getMessage();
        }
        return $this->model;
    }

    /**
     * 根据平台公共清除
     */
    public function deletePublicByPlat()
    {
        Factory::GameClearController()->deletePublic($this->time, $this->seoId);
    }


    /**
     * 清除大奖记录数据
     * @return mixed
     */
    public function deleteBigRankData()
    {
        try {
//            if(empty($time)){
//                throw new Exception('维护开始时间为空，不能清除测试数据！');
//            }

            $game_db           = \Yii::$app->game_db;
            $this->model->data = $this->deleteAllBigRank($game_db);

        } catch (Exception $e) {
            $this->model->status  = 0;
            $this->model->message = $e->getMessage();
        }
        return $this->model;
    }

    /**
     *
     * @param $db
     * @return int
     */
    public function deleteAllBigRank($db)
    {
        $row             = 0;
        $big_sql         = "DELETE FROM fivepk_big_rank_big_plate";
        $row             += $db->createCommand($big_sql)->execute();
        $firephoenix_sql = "DELETE FROM fivepk_big_rank_firephoenix";
        $row             += $db->createCommand($firephoenix_sql)->execute();
        $star97_sql      = "DELETE FROM fivepk_big_rank_star97";
        $row             += $db->createCommand($star97_sql)->execute();

        if (in_array(\Yii::$app->params['att'], $this->game_list_info)) {
            $bigshark_sql = "DELETE FROM fivepk_big_rank_att2";
            $row          += $db->createCommand($bigshark_sql)->execute();
        }
        if (in_array(\Yii::$app->params['super'], $this->game_list_info)) {
            $super_big_boss_sql = "DELETE FROM fivepk_big_rank_super_big_boss";
            $row                += $db->createCommand($super_big_boss_sql)->execute();
        }

        if (in_array(\Yii::$app->params['paman'], $this->game_list_info)) {
            $this->clearPamanBigRankRun($db);
        }

        if (in_array(\Yii::$app->params['big_shark'], $this->game_list_info)) {
            try {
                $sql = "DELETE FROM fivepk_big_rank_bigshark";
                $db->createCommand($sql)->execute();
            } catch (\Exception $e) {
            }
        }

        if (in_array(\Yii::$app->params['xb'], $this->game_list_info)) {
            try {
                $sql = "DELETE FROM fivepk_big_rank_snow_leopard";
                $db->createCommand($sql)->execute();
            } catch (\Exception $e) {
            }
        }

        if (in_array(\Yii::$app->params['ghr'], $this->game_list_info)) {
            try {
                $sql = "DELETE FROM gold_horse_race_big_rank";
                $db->createCommand($sql)->execute();
            } catch (\Exception $e) {
            }
        }

        return $row;
    }

    /**
     * 清除paman大奖榜
     */

    public static function clearPamanBigRank()
    {
        $game_db         = \Yii::$app->game_db;
        $remoteInterface = new remoteInterface();
        $remoteInterface->refreshGameCache();
        self::clearPamanBigRankRun($game_db);
    }


    //清除paman大奖榜
    private static function clearPamanBigRankRun($db)
    {
        try {
            $sql = "update fivepk_seo_paman set big_rank=''";
            $db->createCommand($sql)->execute();
        } catch (\Exception $e) {
        }
        try {
            $sql = "DELETE FROM fivepk_big_rank_paman";
            $db->createCommand($sql)->execute();
        } catch (\Exception $e) {
        }
    }

    //执行sql
    public function createCommand($sql)
    {
        return $this->db->createCommand($sql)->execute();
    }

}

/**
 *  游戏清除
 * Class AccessPoints
 * @package common\models\game
 */
class PublicDelete
{

    public $db;//数据库链接
    public $time;//毫秒时间
    public $seoid;//代理号
    public $gameType;//游戏类型
    public $timeSec;//秒时间

    public $pathIdsString = '';
    public $defaultIdsString = '';
    const MAX_LIMIT = 10000;//最多一次清除多少条


    //删除上机轨迹表 最后删除
    public function deleteFivepkPath()
    {

        if (!$this->selectPathIds()) {
            return '';
        }
        $sql = "DELETE FROM fivepk_path WHERE id in({$this->pathIdsString}) limit " . self::MAX_LIMIT;
        return $this->createCommand($sql);
    }

    //五pk类游戏轨迹表 最后删除
    public function deleteFivepkDefaultFivepk()
    {
        if (!$this->selectDefaultIds()) {
            return '';
        }

        $sql = "DELETE FROM fivepk_default_fivepk WHERE id in({$this->defaultIdsString}) limit " . self::MAX_LIMIT;
        return $this->createCommand($sql);
    }

    //游戏轨迹公共数据表 最后删除
    public function deleteFivepkDefault()
    {
//        $sql = "DELETE FROM fivepk_default WHERE fivepk_default_fivepk_id in({$this->defaultIdsString}) limit ".self::MAX_LIMIT;
        return $this->deleteTableInDefaultId('fivepk_default');
    }

    //删除五pk类游戏轨迹比倍表
    public function deleteDefaultFivepkCompare()
    {
//        $sql = "DELETE FROM fivepk_default_fivepk_compare WHERE fivepk_default_fivepk_id in({$this->defaultIdsString}) limit ".self::MAX_LIMIT;
        return $this->deleteTableInDefaultId('fivepk_default_fivepk_compare');
    }

    //正宗大四梅倍数
    public function deleteFivepkFourOfAKindJaRate()
    {
        return $this->deleteTableInPathId('fivepk_four_of_a_kind_ja_rate');
    }

    //四梅连庄表
    public function deleteFivepkFourOfAKindTwoTenContinue()
    {
        return $this->deleteTableInPathId('fivepk_four_of_a_kind_two_ten_continue');
    }

    //-------------下面是公用方法---------------//


    /**
     *   删除大奖榜
     */
    public function deleteFivepkBigRank($tableName)
    {
        $stime = date('Y-m-d H:i:s', ($this->time / 1000));
        $sql   = "delete $tableName from $tableName,fivepk_account 
                where $tableName.account_id = fivepk_account.account_id
                and  $tableName.last_time > '{$stime}'
                and fivepk_account.seoid = '{$this->seoid}'";
        return $this->createCommand($sql);
    }

    //查询符合条件的pathId
    public function selectPathIds()
    {
        if (empty($this->pathIdsString)) {
            $sql                 = "
            select p.id from fivepk_path p 
            INNER JOIN fivepk_account a on p.account_id=a.account_id
            where 
            a.seoid = '{$this->seoid}'
            and p.game_type = '{$this->gameType}'
            and p.enter_time > '{$this->time}'
            ";
            $data                = $this->db->createCommand($sql)->queryAll();
            $ids                 = array_column($data, 'id');
            $this->pathIdsString = "'" . implode("','", $ids) . "'";
        }
        return $this->pathIdsString;
    }

    //通过表名和pathId删除
    public function deleteTableInPathId($tableName)
    {
        if (!$this->selectPathIds()) {
            return '';
        }
        $sql = "DELETE FROM {$tableName} WHERE fivepk_path_id in({$this->pathIdsString})  limit " . self::MAX_LIMIT;
        return $this->createCommand($sql);
    }

    //查询符合条件的defaultId
    public function selectDefaultIds()
    {
        if (empty($this->defaultIdsString)) {
            $sql                    = "select fivepk_default_fivepk_id from fivepk_default  where 
            seoid = '{$this->seoid}'
            and game_type = '{$this->gameType}'
            and last_time > '{$this->time}'
            ";
            $data                   = $this->db->createCommand($sql)->queryAll();
            $ids                    = array_column($data, 'fivepk_default_fivepk_id');
            $this->defaultIdsString = "'" . implode("','", $ids) . "'";
        }
        return $this->defaultIdsString;
    }

    //通过表名和defaultId删除
    public function deleteTableInDefaultId($tableName)
    {
        if (!$this->selectDefaultIds()) {
            return '';
        }
        $sql = "DELETE FROM {$tableName} WHERE fivepk_default_fivepk_id in({$this->defaultIdsString})  limit " . self::MAX_LIMIT;
        return $this->createCommand($sql);
    }

    //设置数据链接
    public function setDb(&$db)
    {
        $this->db = $db;
    }

    //执行sql
    public function createCommand($sql)
    {
        return $this->db->createCommand($sql)->execute();
    }


    //轨迹
    public function deleteLocus($name)
    {
        $sql = "
        DELETE FROM backend_locus_{$name}_day WHERE create_time >{$this->timeSec};
        DELETE FROM backend_locus_{$name}_month WHERE create_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }

    //所有奖
    public function deletePrize($name)
    {
        $sql = "
        DELETE FROM backend_prize_{$name}_day WHERE update_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }

    //分数统计
    public function deleteCompare($name)
    {
        $sql = "
        DELETE FROM backend_compare_{$name} WHERE create_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }


}


/**  火凤凰
 * Class HFH
 * @package common\models\game
 */
class HFH extends PublicDelete
{

    /**
     * 清除数据
     * @param $time int 清除时间 毫秒
     * @param $seoId string 代理号
     */
    public function clear($time, $seoId)
    {
        $this->setDb(Yii::$app->game_db);
        $this->time     = $time;
        $this->timeSec  = $time / 1000;
        $this->seoid    = $seoId;
        $this->gameType = &Yii::$app->params['fire_phoenix'];

        $this->deleteFivepkBigRankFirephoenix();//火凤凰大奖榜
        $this->deleteBackendCompare();//分数统计
        $this->deleteBackendPrizeDay();//所有奖

        //----下面是公共的---//

        $this->deleteFivepkPath();//删除上机轨迹表
        $this->deleteFivepkDefaultFivepk();//五pk类游戏轨迹表

    }


    //轨迹
    public function deleteFivepkDefaultFivepk()
    {
        $sql = "
        DELETE FROM backend_locus_hfh_day WHERE create_time >{$this->timeSec};
        DELETE FROM backend_locus_hfh_month WHERE create_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }

    //分数统计
    public function deleteBackendCompare()
    {
        $sql = "
        DELETE FROM backend_compare_hfh WHERE create_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }

    //所有奖
    public function deleteBackendPrizeDay()
    {
        $sql = "
        DELETE FROM backend_prize_hfh_day WHERE update_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }


    //火凤凰大奖榜
    public function deleteFivepkBigRankFirephoenix()
    {
        return $this->deleteFivepkBigRank('fivepk_big_rank_firephoenix');
    }

}

/**  DBS
 * Class DBS
 * @package common\models\game
 */
class DBS extends PublicDelete
{

    public $db;

    /**
     * 清除数据
     * @param $time int 清除时间 毫秒
     * @param $seoId string 代理号
     */
    public function clear($time, $seoId)
    {
        $this->setDb(Yii::$app->game_db);
        $this->time     = $time;
        $this->timeSec  = $time / 1000;
        $this->seoid    = $seoId;
        $this->gameType = &Yii::$app->params['big_shark'];
        $name           = 'dbs';
        $this->deleteLocus($name);
        $this->deletePrize($name);
        $this->deleteCompare($name);

        //----下面是公共的---//

        $this->deleteFivepkPath();//删除上机轨迹表

    }


    //五pk类游戏轨迹表 最后删除
    public function deleteFivepkDefaultFivepk()
    {
        if (!$this->selectDefaultIds()) {
            return '';
        }

        $sql = "DELETE FROM fivepk_default_fivepk_bigshark WHERE id in({$this->defaultIdsString}) limit " . self::MAX_LIMIT;
        return $this->createCommand($sql);
    }

    //游戏轨迹公共数据表 最后删除
    public function deleteFivepkDefault()
    {
//        $sql = "DELETE FROM fivepk_default WHERE fivepk_default_fivepk_id in({$this->defaultIdsString}) limit ".self::MAX_LIMIT;
        return $this->deleteTableInDefaultId('fivepk_default_bigshark');
    }

    //删除五pk类游戏轨迹比倍表
    public function deleteDefaultFivepkCompare()
    {
//        $sql = "DELETE FROM fivepk_default_fivepk_compare WHERE fivepk_default_fivepk_id in({$this->defaultIdsString}) limit ".self::MAX_LIMIT;
        return $this->deleteTableInDefaultId('fivepk_default_fivepk_compare_bigshark');
    }

    //正宗大四梅倍数
    public function deleteFivepkFourOfAKindJaRate()
    {
        return $this->deleteTableInPathId('fivepk_four_of_a_kind_ja_rate_bigshark');
    }

    //查询符合条件的defaultId
    public function selectDefaultIds()
    {
        if (empty($this->defaultIdsString)) {
            $sql = "select fivepk_default_fivepk_id from fivepk_default_bigshark  where 
            seoid = '{$this->seoid}'
            and game_type = '{$this->gameType}'
            and last_time > '{$this->time}'
            ";
//            $data = $this->db->createCommand($sql)->queryAll();
            $data                   = Yii::$app->game_db->createCommand($sql)->queryAll();
            $ids                    = array_column($data, 'fivepk_default_fivepk_id');
            $this->defaultIdsString = "'" . implode("','", $ids) . "'";
        }
        return $this->defaultIdsString;
    }

    //大白鲨获奖
    public function deleteFivepkPrizeBigShark()
    {
        return $this->deleteTableInPathId('fivepk_prize_bigshark');
    }

    //大白鲨比倍记录表
    public function deleteFivepkCompareBigShark()
    {
        return $this->deleteTableInPathId('fivepk_compare_bigshark');
    }

    //大白鲨大奖榜
    public function deleteFivepkBigRankBigShark()
    {
        return $this->deleteFivepkBigRank('fivepk_big_rank_bigshark');
    }

}

/**  大字板
 */
class DZB extends PublicDelete
{
    /**
     * 清除数据
     * @param $time int 清除时间 毫秒
     * @param $seoId string 代理号
     */
    public function clear($time, $seoId)
    {
        $this->setDb(Yii::$app->game_db);
        $this->time     = $time;
        $this->timeSec  = $time / 1000;
        $this->seoid    = $seoId;
        $this->gameType = &Yii::$app->params['big_plate'];

        $this->deleteFivepkBigRankBigPlate();//大字版大奖榜
        $this->deleteBackendCompare();//分数统计
        $this->deleteBackendPrizeDay();//所有奖

        //----下面是公共的---//
        $this->deleteFivepkPath();//删除上机轨迹表
        $this->deleteFivepkDefaultFivepk();//五pk类游戏轨迹表
    }

    //轨迹
    public function deleteFivepkDefaultFivepk()
    {
        $sql = "
        DELETE FROM backend_locus_dzb_day WHERE create_time >{$this->timeSec};
        DELETE FROM backend_locus_dzb_month WHERE create_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }

    //分数统计
    public function deleteBackendCompare()
    {
        $sql = "
        DELETE FROM backend_compare_dzb WHERE create_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }

    //所有奖
    public function deleteBackendPrizeDay()
    {
        $sql = "
        DELETE FROM backend_prize_dzb_day WHERE update_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }

    //大字版大奖榜
    public function deleteFivepkBigRankBigPlate()
    {
        return $this->deleteFivepkBigRank('fivepk_big_rank_big_plate');
    }

}

/**  明星97
 */
class MXJ extends PublicDelete
{
    /**
     * 清除数据
     * @param $time int 清除时间 毫秒
     * @param $seoId string 代理号
     */
    public function clear($time, $seoId)
    {
        $this->setDb(Yii::$app->game_db);
        $this->time     = $time;
        $this->timeSec  = $time / 1000;
        $this->seoid    = $seoId;
        $this->gameType = &Yii::$app->params['star97'];

        $this->deleteFivepkBigRankStar97();

        $name = 'mxj';

        $this->deleteLocus($name);
        $this->deletePrize($name);

//        $this->deleteCompare($name);

        //----下面是公共的---//
        $this->deleteFivepkPath();//删除上机轨迹表

    }

    //删除明星97 游戏轨迹
    public function deleteDefaultStar97()
    {
        $default_star97_sql = "DELETE fivepk_default_star97 FROM star97_default, fivepk_default_star97
            WHERE star97_default.star_default_id = fivepk_default_star97.id
            AND	star97_default.last_time >  '{$this->time}' AND star97_default.seoid = '{$this->seoid}'";
        $this->createCommand($default_star97_sql);
        $star97_sql = "DELETE FROM star97_default WHERE star97_default.last_time >  '{$this->time}' AND star97_default.seoid = '{$this->seoid}'  limit " . self::MAX_LIMIT;
        return $this->createCommand($star97_sql);
    }

    //明星97获奖
    public function deleteFivepkPrizeStar97()
    {
        return $this->deleteTableInPathId('fivepk_prize_star97');
    }

    //明星97大奖榜
    public function deleteFivepkBigRankStar97()
    {
        return $this->deleteFivepkBigRank('fivepk_big_rank_star97');
    }

}


/**  ATT
 */
class ATT extends PublicDelete
{
    /**
     * 清除数据
     * @param $stime int 清除时间 毫秒
     * @param $seoId string 代理号
     */
    public function clear($stime, $seoId)
    {

        $this->setDb(Yii::$app->game_db);
        $this->time     = $stime;
        $this->timeSec  = $stime / 1000;
        $this->seoid    = $seoId;
        $this->gameType = &Yii::$app->params['att'];

        $name = 'att';

        $this->deleteLocus($name);
        $this->deletePrize($name);

        $this->deleteCompare($name);


        //删除大奖榜
        self::FivepkBigRankAtt2();

        //----下面是公共的---//
        $this->deleteFivepkPath();//删除上机轨迹表


    }


    /**
     *  删除轨迹1
     * @return  mixed
     */
    public function FivepkDefaultAtt2()
    {
        $sql = "delete from fivepk_default_att2 where seoid = '{$this->seoid}' and last_time > '{$this->time}' limit 10000";
        return $this->createCommand($sql);
    }

    /**
     *  删除轨迹2
     * @param $ids  array  fivepk_default_fivepk_att2 表id
     * @return  mixed
     */
    public function FivepkDefaultFivepkAtt2($ids)
    {
        if (empty($ids)) {
            return '';
        }
        $inStr = "'" . implode("','", $ids) . "'";
        $sql   = "delete from fivepk_default_fivepk_att2 where id in ({$inStr}) limit 10000";
        return $this->createCommand($sql);
    }

    /**
     *   删除比备
     * @param $ids array fivepk_default_fivepk_id
     * @return  mixed
     */
    public function FivepkDefaultFivepkCompareAtt2($ids)
    {
        if (empty($ids)) {
            return '';
        }
        $inStr = "'" . implode("','", $ids) . "'";
        $sql   = "delete from fivepk_default_fivepk_compare_att2 where fivepk_default_fivepk_id in ({$inStr}) limit 10000";
        return $this->createCommand($sql);
    }

    /**
     *   删除prize表
     * @param $ids array  fivepk_path_id
     * @return  mixed
     */
    public function FivepkPrizeAtt2()
    {
        if (empty($this->pathIdsString)) {
            return '';
        }
        $sql = "delete from fivepk_prize_att2 where fivepk_path_id in ({$this->pathIdsString}) limit 10000";
        return $this->createCommand($sql);
    }

    /**
     *   删除大奖榜
     * @return  mixed
     */
    public function FivepkBigRankAtt2()
    {
        $stime = date("Y-m-d H:i:s", $this->time / 1000);
        $sql   = "delete fivepk_big_rank_att2 from fivepk_big_rank_att2,fivepk_account 
                where fivepk_big_rank_att2.account_id = fivepk_account.account_id
                and  fivepk_big_rank_att2.last_time > '{$stime}'
                and fivepk_account.seoid = '{$this->seoid}'";
        return $this->createCommand($sql);
    }
}

/**  超级大亨
 */
class SBB extends PublicDelete
{
    public function clear($stime, $seoId)
    {

        $this->time    = $stime;
        $this->seoid   = $seoId;
        $this->timeSec = $stime / 1000;
        $this->setDb(Yii::$app->game_db);
        $this->gameType = &Yii::$app->params['super'];


        $name = 'sbb';
        $this->deleteLocus($name);
        $this->deletePrize($name);
//        $this->deleteCompare($name);

        //删除大奖榜
        self::FivepkBigRankSuperBigBoss($stime, $seoId);

        //----下面是公共的---//
        $this->deleteFivepkPath();//删除上机轨迹表

    }

    /**
     *  删除轨迹1
     * @param $stime  关服时间  毫秒
     * @param $seoId
     */
    public function FivepkDefaultSuperBigBoss($stime, $seoId)
    {
        $sql = "delete from fivepk_default_super_big_boss where seoid = '{$seoId}' and last_time > '{$stime}' limit 10000";
        $this->createCommand($sql);
    }

    /**
     *  删除轨迹2
     * @param $ids  fivepk_default_fivepk_super_big_boss_id 表id
     */
    public function FivepkDefaultFivepkSuperBigBoss($ids)
    {
        if (empty($ids)) {
            return '';
        }
        $inStr = "'" . implode("','", $ids) . "'";
        $sql   = "delete from fivepk_default_fivepk_super_big_boss where id in ({$inStr}) limit 10000";
        $this->createCommand($sql);
    }


    /**
     *   删除prize表
     * @param $ids  fivepk_path_id
     */
    public function FivepkPrizeSuperBigBoss()
    {
        if (empty($this->pathIdsString)) {
            return '';
        }
        $sql = "delete from fivepk_prize_super_big_boss where fivepk_path_id in ({$this->pathIdsString}) limit 10000";
        $this->createCommand($sql);
    }

    /**
     *   删除大奖榜
     * @param $stime  关服时间  毫秒
     * @param $seoId
     */
    public function FivepkBigRankSuperBigBoss($stime, $seoId)
    {
        $stime = date("Y-m-d H:i:s", $stime / 1000);
        $sql   = "delete fivepk_big_rank_super_big_boss from fivepk_big_rank_super_big_boss,fivepk_account 
                where fivepk_big_rank_super_big_boss.account_id = fivepk_account.account_id
                and  fivepk_big_rank_super_big_boss.last_time > '{$stime}'
                and fivepk_account.seoid = '{$seoId}'";
        $this->createCommand($sql);
    }
}

/**  paman
 */
class PAMAN extends PublicDelete
{

    public function clear($stime, $seoId)
    {
        $this->time  = $stime;
        $this->seoid = $seoId;
        $this->setDb(Yii::$app->game_db);
        $this->gameType = &Yii::$app->params['paman'];

        $this->selectPathIds();
        //删除轨迹1
        self::PamanDefault($stime, $seoId);
        //删除prize表
        self::PamanPrize();
        //删除大奖榜
        self::FivepkBigRankPaman($stime, $seoId);

        //----下面是公共的---//
        $this->deleteFivepkPath();//删除上机轨迹表
    }

    /**
     *  删除轨迹1
     * @param $stime  关服时间  毫秒
     * @param $seoId
     */
    public function PamanDefault($stime, $seoId)
    {
        $sql = "DELETE paman_default FROM paman_default 
INNER JOIN
( SELECT pd.id from paman_default pd
inner join fivepk_account fa on pd.account_id = fa.account_id
where fa.seoid = '$seoId' and pd.last_time > '$stime'  LIMIT 10000
) b
on b.id=paman_default.id";
        $this->createCommand($sql);
    }


    /**
     *   删除prize表
     * @param $ids  fivepk_path_id
     */
    public function PamanPrize()
    {
        if (empty($this->pathIdsString)) {
            return '';
        }
        $sql = "delete from paman_prize where fivepk_path_id in ({$this->pathIdsString}) limit 10000";
        $this->createCommand($sql);
    }

    /**
     *   删除大奖榜
     * @param $stime  关服时间  毫秒
     * @param $seoId
     */
    public function FivepkBigRankPaman($stime, $seoId)
    {
        $stime = date("Y-m-d H:i:s", $stime / 1000);
        $sql   = "delete fivepk_big_rank_paman from fivepk_big_rank_paman,fivepk_account
                where fivepk_big_rank_paman.account_id = fivepk_account.account_id
                and  fivepk_big_rank_paman.last_time > '{$stime}'
                and fivepk_account.seoid = '{$seoId}'";
        $this->createCommand($sql);
    }
}


/**  雪豹
 * Class
 * @package common\models\game
 */
class BAO extends PublicDelete
{

    /**
     * 清除数据
     * @param $time int 清除时间 毫秒
     * @param $seoId string 代理号
     */
    public function clear($time, $seoId)
    {
        $this->setDb(Yii::$app->game_db);
        $this->time     = $time;
        $this->timeSec  = $time / 1000;
        $this->seoid    = $seoId;
        $this->gameType = 11;

        $this->deleteFivepkBigRankBao();//大奖榜
        $this->deleteBackendCompare();//分数统计
        $this->deleteBackendPrizeDay();//所有奖

        //----下面是公共的---//

        $this->deleteFivepkPath();//删除上机轨迹表
        $this->deleteFivepkDefaultFivepk();//五pk类游戏轨迹表

    }


    //轨迹
    public function deleteFivepkDefaultFivepk()
    {
        $sql = "
        DELETE FROM backend_locus_bao_day WHERE create_time >{$this->timeSec};
        DELETE FROM backend_locus_bao_month WHERE create_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }

    //比倍分数统计
    public function deleteBackendCompare()
    {
//        $sql="
//        DELETE FROM backend_compare_hfh WHERE create_time >{$this->timeSec}
//        ";
//        return $this->createCommand($sql);
    }

    //所有奖
    public function deleteBackendPrizeDay()
    {
        $sql = "
        DELETE FROM backend_prize_bao_day WHERE update_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }


    //大奖榜
    public function deleteFivepkBigRankBao()
    {
        return $this->deleteFivepkBigRank('fivepk_big_rank_snow_leopard');
    }

}


/**  赛马
 * Class
 * @package common\models\game
 */
class GHR extends PublicDelete
{

    /**
     * 清除数据
     * @param $time int 清除时间 毫秒
     * @param $seoId string 代理号
     */
    public function clear($time, $seoId)
    {
        $this->setDb(Yii::$app->game_db);
        $this->time     = $time;
        $this->timeSec  = $time / 1000;
        $this->seoid    = $seoId;
        $this->gameType = 12;

        $this->deleteFivepkBigRankGhr();//大奖榜
        $this->deleteBackendPrizeDay();//所有奖
        GhrRedisLocusHandler::cleanTestProfitSum();;//抽水累计

        //----下面是公共的---//


        $this->deleteFivepkPath();//删除上机轨迹表
        $this->deleteFivepkDefaultFivepk();//五pk类游戏轨迹表

    }


    //轨迹
    public function deleteFivepkDefaultFivepk()
    {
        $sql = "
        DELETE FROM backend_locus_ghr_day WHERE create_time >{$this->timeSec};
        DELETE FROM backend_locus_ghr_month WHERE create_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }


    //所有奖
    public function deleteBackendPrizeDay()
    {
        $sql = "
        DELETE FROM backend_prize_ghr_day WHERE update_time >{$this->timeSec}
        ";
        return $this->createCommand($sql);
    }


    //大奖榜
    public function deleteFivepkBigRankGhr()
    {
        $tableName = 'gold_horse_race_big_rank';
        $stime     = date('Y-m-d H:i:s', ($this->time / 1000));
        $sql       = "delete $tableName from $tableName
                where 
                 $tableName.rank_time > '{$stime}'
                ";
        return $this->createCommand($sql);
//        return $this->deleteFivepkBigRank('gold_horse_race_big_rank');
    }

}



/**  赛马
 * Class
 * @package common\models\game
 */
class BYU extends PublicDelete
{

    /**
     * 清除数据
     * @param $time int 清除时间 毫秒
     * @param $seoId string 代理号
     */
    public function clear($time, $seoId)
    {
        $this->setDb(Yii::$app->game_db);
        $this->time     = $time;
        $this->timeSec  = $time / 1000;
        $this->seoid    = $seoId;
        $this->gameType = 13;


        //----下面是公共的---//


        $this->deleteFivepkPath();//删除上机轨迹表
        $this->deleteFivepkDefaultFivepk();//五pk类游戏轨迹表

    }


    //轨迹
    public function deleteFivepkDefaultFivepk()
    {
        $sql = "
        DELETE FROM backend_locus_byu_day WHERE create_time >{$this->timeSec};
        ";
        return $this->createCommand($sql);
    }


}



