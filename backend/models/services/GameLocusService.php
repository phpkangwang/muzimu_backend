<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/24
 * Time: 13:44
 */

namespace backend\models\services;

use Yii;
use backend\models\BaseModel;
use common\models\FivepkDefault;
use common\models\FivepkPrizeType;
use common\models\game\att2\FivepkDefaultAtt2;
use common\models\game\big_shark\FivepkDefaultBigShark;
use common\models\game\fire_unicorn\FivepkDefaultFireunicorn;
use common\models\game\FivepkDefaultFivepk;
use common\models\game\star97\Star97Default;
use yii\data\Pagination;

class GameLocusService extends BaseModel
{
    public function SbbLocus($param){
        $accountId = $param['account_id'];
        $machineId = $param['machine_auto_id'];
        $prizeType = $param['prize_type'];
        $plusCardsType = $param['plus_cards_type'];
        $groups    = $param['seoid'];
        $random    = $param['random'];
        $prizeList = $param['prizeList'];
        $bigAward  = $param['bigAward'];
        $stime     = strtotime($param['star_last_time']) *1000;
        $etime     = strtotime($param['end_last_time']) *1000;

        $pageNo   = $param['pageNo'];
        $pageSize = $param['pageSize'];

        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;

        $inStr = "'".implode("','", $groups)."'";
        $where = " and a.seoid in ({$inStr}) ";
        if( $accountId != ""){
            $where .= " and sbb.account_id = '{$accountId}'";
        }
        if( $machineId != ""){
            $where .= " and dsbb.machine_auto_id = '{$machineId}'";
        }
        if( $prizeType != ""){
            $where .= " and dsbb.data_prize_type_id like '%{$prizeType}%'";
        }
        if( $bigAward == 2){
            $arrBigAward = array();
            foreach ($prizeList as $val){
                $str = " dsbb.data_prize_type_id like '%{$val}%'";
                array_push($arrBigAward, $str);
            }
            $strBigAward = implode(" OR ",$arrBigAward);
            $where .= " and ( {$strBigAward} )";
        }
        if( $plusCardsType != ""){
            $where .= " and dsbb.plus_cards_type = '{$plusCardsType}'";
        }
        if( $random != ""){
            $where .= " and dsbb.random = '{$random}'";
        }

        if( $stime != "" && $etime != ""){
            $where .= " and dsbb.last_time >= '{$stime}' and dsbb.last_time < '{$etime}'";
        }

        $select = " dsbb.*,sbb.account_id,sbb.coin,sbb.credit,sbb.bet,sbb.win,i.nick_name";

        $sql = " select {$select} from fivepk_default_fivepk_super_big_boss dsbb,fivepk_default_super_big_boss sbb,fivepk_account a,fivepk_player_info i
                 where dsbb.id = sbb.fivepk_default_fivepk_super_big_boss_id and sbb.account_id = a.account_id and a.account_id = i.account_id {$where}
                 order by dsbb.id desc limit {$limit} offset {$offset} 
        ";
        $data = Yii::$app->game_db->createCommand($sql)->queryAll();
        return $data;
    }
    public function SbbLocusCount($param){
        $accountId = $param['account_id'];
        $machineId = $param['machine_auto_id'];
        $prizeType = $param['prize_type'];
        $plusCardsType = $param['plus_cards_type'];
        $groups    = $param['seoid'];
        $random    = $param['random'];
        $prizeList = $param['prizeList'];
        $bigAward  = $param['bigAward'];
        $stime     = strtotime($param['star_last_time']) *1000;
        $etime     = strtotime($param['end_last_time']) *1000;

        $inStr = "'".implode("','", $groups)."'";
        $where = " and a.seoid in ({$inStr}) ";
        if( $accountId != ""){
            $where .= " and sbb.account_id = '{$accountId}'";
        }
        if( $machineId != ""){
            $where .= " and dsbb.machine_auto_id = '{$machineId}'";
        }
        if( $prizeType != ""){
            $where .= " and dsbb.data_prize_type_id like '%{$prizeType}%'";
        }
        if( $bigAward == 2){
            $arrBigAward = array();
            foreach ($prizeList as $val){
                $str = " dsbb.data_prize_type_id like '%{$val}%'";
                array_push($arrBigAward, $str);
            }
            $strBigAward = implode(" OR ",$arrBigAward);
            $where .= " and ( {$strBigAward} )";
        }
        if( $plusCardsType != ""){
            $where .= " and dsbb.plus_cards_type = '{$plusCardsType}'";
        }
        if( $random != ""){
            $where .= " and dsbb.random = '{$random}'";
        }

        if( $stime != "" && $etime != ""){
            $where .= " and dsbb.last_time >= '{$stime}' and dsbb.last_time < '{$etime}'";
        }

        $select = " dsbb.*,sbb.account_id,sbb.coin,sbb.credit,sbb.bet,sbb.win";

        $sql = " select {$select} from fivepk_default_fivepk_super_big_boss dsbb,fivepk_default_super_big_boss sbb,fivepk_account a
                 where dsbb.id = sbb.fivepk_default_fivepk_super_big_boss_id and sbb.account_id = a.account_id {$where}
        ";
        return Yii::$app->game_db->createCommand($sql)->queryAll();
    }

    //大字板和火凤凰 游戏轨迹查询
    public function defaultLocus($param =[] ,$pageSize = 500){
        $accountId = $param['account_id'];
        $machineId = $param['machine_auto_id'];
        $prizeType = $param['prize_type'];
        $plusCardsType = $param['plus_cards_type'];
        $groups    = $param['seoid'];
        $random    = $param['random'];
        $prizeList = $param['prizeList'];
        $bigAward  = $param['bigAward'];
        $stime     = strtotime($param['star_last_time']) *1000;
        $etime     = strtotime($param['end_last_time']) *1000;

        $pageNo   = $param['pageNo'];
        $pageSize = $param['pageSize'];

        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;

        $inStr = "'".implode("','", $groups)."'";
        $where = " and a.seoid in ({$inStr}) ";
        if( $accountId != ""){
            $where .= " and fd.account_id = '{$accountId}'";
        }

        if( $machineId != ""){
            $where .= " and fdf.machine_auto_id = '{$machineId}'";
        }

        if( $prizeType != ""){
            $where .= " and fdf.data_prize_type_id = '{$prizeType}'";
        }

        if( $bigAward == 2){
            $bigAwardInStr = "'".implode("','", $prizeList)."'";
            $where = " and fdf.data_prize_type_id in ({$bigAwardInStr}) ";
        }

        if( $plusCardsType != ""){
            $where .= " and fdf.plus_cards_type = '{$plusCardsType}'";
        }

        if( $random != ""){
            $where .= " and fdf.random = '{$random}'";
        }

        if( $stime != "" && $etime != ""){
            $where .= " and fdf.last_time >= '{$stime}' and fdf.last_time < '{$etime}'";
        }

        $select = " fdf.*,fd.account_id,fd.coin,fd.credit,fd.bet,fd.win";

        $sql = " select {$select} from fivepk_default fd,fivepk_default_fivepk fdf,fivepk_account a
                 where fd.fivepk_default_fivepk_id = fdf.id and fd.account_id = a.account_id {$where}
                 order by fdf.id desc limit {$limit} offset {$offset} 
        ";
        echo $sql;die;
        return Yii::$app->game_db->createCommand($sql)->queryAll();



        //统计总数
        //$count_query = FivepkDefault::find();
        //$count_query = $this->filterQueryConditions($count_query,$conditions,1);

        //$query = FivepkDefault::find()->joinWith('fivepkDefaultFivepk.compare');
        //$query = $this->filterQueryConditions($query,$conditions);

        $pagination = new Pagination([
            'totalCount' => $count_query->count(1),
            'pageSize' => $pageSize,
            'pageParam' => 'page',
            'pageSizeParam' => 'per-page'
        ]);
        //dt($count_query->createCommand()->getRawSql());
        $query = $query->orderBy('id DESC');
        $rows = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        $result = ['data'=>$rows,'pagination'=>$pagination];

        return $result;
    }


    /**
     * 游戏轨迹查询 分页查询数据
     * @param $conditions
     * @param $pageNo
     * @param $pageSize
     * @return array
     */
    public function defaultLocusPage($param){
        $accountId = $param['accountId'];
        $machineId = $param['$machineId'];
        $prizeType = $param['prize_type'];
        $plusCardsType = $param['plus_cards_type'];
        $groups    = $param['seoid'];
        $random    = $param['random'];
        $prizeList = $param['prizeList'];
        $bigAward  = $param['bigAward'];
        $stime     = strtotime($param['star_last_time']) *1000;
        $etime     = strtotime($param['end_last_time']) *1000;

        $pageNo   = $param['pageNo'];
        $pageSize = $param['pageSize'];

        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;

        $inStr = "'".implode("','", $groups)."'";
        $where = " and a.seoid in ({$inStr}) ";
        if( $accountId != ""){
            $where .= " and fd.account_id = '{$accountId}'";
        }

        if( $machineId != ""){
            $where .= " and fdf.machine_auto_id = '{$machineId}'";
        }

        if( $prizeType != ""){
            $where .= " and fdf.data_prize_type_id = '{$prizeType}'";
        }

        if( $bigAward == 2){
            $bigAwardInStr = "'".implode("','", $prizeList)."'";
            $where = " and fdf.data_prize_type_id in ({$bigAwardInStr}) ";
        }

        if( $plusCardsType != ""){
            $where .= " and fdf.plus_cards_type = '{$plusCardsType}'";
        }

        if( $random != ""){
            $where .= " and fdf.random = '{$random}'";
        }

        if( $stime != "" && $etime != ""){
            $where .= " and fdf.last_time >= '{$stime}' and fdf.last_time < '{$etime}'";
        }

        $select = " fdf.*,fd.account_id,fd.coin,fd.credit,fd.bet,fd.win";

        $sql = " select {$select} from fivepk_default fd,fivepk_default_fivepk fdf,fivepk_account a
                 where fd.fivepk_default_fivepk_id = fdf.id and fd.account_id = a.account_id {$where}
                 order by fdf.id desc limit {$limit} offset {$offset} 
        ";
        echo $sql;die;
        return Yii::$app->game_db->createCommand($sql)->queryAll();
    }

    /**
     * 游戏轨迹查询 总条数
     * @param $conditions
     * @param $pageNo
     * @param $pageSoze
     * @return array
     */
    public function defaultLocusCount($conditions){
        //统计总数
        $count_query = FivepkDefault::find();
        return $this->filterQueryConditions($count_query,$conditions,1)->count(1);
    }


    //大字板和火凤凰查询条件
    public function filterQueryConditions($query,$conditions,$count = 0)
    {
        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                if ('' !== $value && null !=$value) {
                    switch ($key) {
                        case 'account_id':
                            $query->andFilterWhere([$key=>trim($value)]);
                            break;
                        case 'machine_auto_id':
                            if($count == 1){
                                $query->joinWith('fivepkDefaultFivepk.compare');
                            }
                            $query->andFilterWhere([$key=>$value]);

                            break;
                        case 'data_prize_type_id':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultFivepk');
                            }
                            //处理中奖牌型 大四梅 五梅
                            $FivepkPrizeTypeObj = new FivepkPrizeType();
                            $value = $FivepkPrizeTypeObj->getAllSon($value);
                            $query->andFilterWhere(['fivepk_default_fivepk.'.$key=>$value]);
                            break;
                        case 'game_type':
                            $query->andFilterWhere(['fivepk_default.'.$key=>$value]);
                            break;
                        case 'star_last_time':
                            $query->andFilterWhere(['>=', 'fivepk_default.last_time',trim(strtotime($value)*1000)]);
                            break;
                        case 'end_last_time':
                            $query->andFilterWhere(['<=','fivepk_default.last_time',trim(strtotime($value)*1000)]);
                            break;
                        case 'seoid':
                            $query->andFilterWhere([$key=>$value]);
                            break;
                        case 'awards_prize_type':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultFivepk');
                            }
                            $query->andFilterWhere(['data_prize_type_id'=>$value]);
                            break;
                        case 'random':
                            if($count == 1){
                                $query->joinWith('fivepkDefaultFivepk');
                            }
                            $query->andFilterWhere(['fivepk_default_fivepk.'.$key=>$value]);
                            break;

                    }
                }
            }
        }
        return $query;
    }

    /**
     * 明星97游戏轨迹
     * @param array $conditions
     * @return array
     */
    public function star97LocusPage( $conditions =[] , $pageNo, $pageSize){
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;

        $query = Star97Default::find()->joinWith(['fivepkDefaultStar97.machineList']);
        $query = $this->filterStar97QueryConditions($query,$conditions);
        $query = $query->orderBy('id DESC');
        $data = $query
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();
        
        return $data;
    }

    /**
     * 游戏轨迹查询 总条数
     * @param $conditions
     * @param $pageNo
     * @param $pageSoze
     * @return array
     */
    public function star97LocusCount($conditions){
        //统计总数
        $count_query = Star97Default::find();
        return $this->filterStar97QueryConditions($count_query,$conditions,1)->count(1);
    }

    /**
     * 明星97 轨迹查询条件
     * @param $query
     * @param $conditions
     * @param int $count
     * @return mixed
     */
    public function filterStar97QueryConditions( $query,$conditions,$count = 0 ){
        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                if ('' !== $value && null !=$value) {
                    switch ($key) {
                        case 'account_id':
                            $query->andFilterWhere(['star97_default.'.$key=>trim($value)]);
                            break;
                        case 'machine_auto_id':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultStar97');
                            }
                            $query->andFilterWhere([$key=>$value]);
                            break;
                        case 'win_type':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultStar97');
                            }
                                $query->andFilterWhere(['fivepk_default_star97.win_type'=>$value]);
                            break;
                        case 'prize_type':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultStar97');
                            }
                            $query->andFilterWhere(['fivepk_default_star97.prize_type'=>$value]);
                            break;
                        case 'awards_prize_type':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultStar97');
                            }
                            $query->andFilterWhere(['fivepk_default_star97.prize_type'=>$value]);
                            break;
                        case 'star_last_time':
                            $query->andFilterWhere(['>=', 'star97_default.last_time',trim(strtotime($value)*1000)]);
                            break;
                        case 'end_last_time':
                            $query->andFilterWhere(['<=','star97_default.last_time',trim(strtotime($value)*1000)]);
                            break;
                        case 'seoid':
                            $query->andFilterWhere([$key=>$value]);
                            break;
                        case 'random':
                            $query->joinWith('fivepkDefaultStar97');
                            $query->andFilterWhere(['fivepk_default_star97.'.$key=>$value]);
                            break;
                    }
                }
            }
        }
        return $query;
    }

    /**
     * 火麒麟
     * @param array $conditions
     * @return array
     */
    public function fireUnicornLocus($conditions = []){

        $count_query = FivepkDefaultFireunicorn::find();
        $count_query = $this->filterUnicornQueryConditions($count_query,$conditions,1);

        $query = FivepkDefaultFireunicorn::find()->joinWith(['fivepkDefaultFivepkFireUnicorn.compare']);
        $query = $this->filterUnicornQueryConditions($query,$conditions);

        $pagination = new Pagination([
            'totalCount' => $count_query->count(1),
            'pageSize' => '500',
            'pageParam' => 'page',
            'pageSizeParam' => 'per-page'
        ]);

        $query = $query->orderBy('id DESC');
        $rows = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        $result = ['data'=>$rows,'pagination'=>$pagination];

        return $result;
    }

    /**
     * 明星97游戏轨迹
     * @param array $conditions
     * @return array
     */
    public function fireUnicornLocusPage( $conditions =[] , $pageNo, $pageSize){
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;

        $query = FivepkDefaultFireunicorn::find()->joinWith(['fivepkDefaultFivepkFireUnicorn.compare']);
        $query = $this->filterUnicornQueryConditions($query,$conditions);

        $query = $query->orderBy('id DESC');
        $data = $query
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();

        return $data;
    }

    /**
     * 游戏轨迹查询 总条数
     * @param $conditions
     * @param $pageNo
     * @param $pageSoze
     * @return array
     */
    public function fireUnicornLocusCount($conditions){
        //统计总数
        $count_query = FivepkDefaultFireunicorn::find();
        return $this->filterUnicornQueryConditions($count_query,$conditions,1)->count(1);
    }

    public function filterUnicornQueryConditions($query,$conditions,$count = 0){

        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                if ('' !== $value && null !=$value) {
                    switch ($key) {
                        case 'account_id':
                            $query->andFilterWhere([$key=>trim($value)]);
                            break;
                        case 'machine_auto_id':
                            if($count == 1){
                                $query->joinWith('fivepkDefaultFivepkFireUnicorn');
                            }
                            $query->andFilterWhere([$key=>$value]);

                            break;
                        case 'data_prize_type_id':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultFivepkFireUnicorn');
                            }
                            //处理中奖牌型 大四梅 五梅
                            $FivepkPrizeTypeObj = new FivepkPrizeType();
                            $value = $FivepkPrizeTypeObj->getAllSon($value);
                            $query->andFilterWhere(['fivepk_default_fivepk_fireunicorn.'.$key=>$value]);
                            break;
                        case 'game_type':
                            $query->andFilterWhere(['fivepk_default_fireunicorn.'.$key=>$value]);
                            break;
                        case 'star_last_time':
                            $query->andFilterWhere(['>=', 'fivepk_default_fireunicorn.last_time',trim(strtotime($value)*1000)]);
                            break;
                        case 'end_last_time':
                            $query->andFilterWhere(['<=','fivepk_default_fireunicorn.last_time',trim(strtotime($value)*1000)]);
                            break;
                        case 'seoid':
                            $query->andFilterWhere([$key=>$value]);
                            break;
                        case 'awards_prize_type':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultFivepkFireUnicorn');
                            }
                            $query->andFilterWhere(['data_prize_type_id'=>$value]);
                            break;
                        case 'random':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultFivepkFireUnicorn');
                            }
                            $query->andFilterWhere(['fivepk_default_fivepk_fireunicorn.'.$key=>$value]);
                            break;

                    }
                }
            }
        }
        return $query;
    }


    public function bigSharkLocus($conditions){

        $count_query = FivepkDefaultBigShark::find();
        $count_query = $this->bigSharkQueryConditions($count_query,$conditions,1);

        $query = FivepkDefaultBigShark::find()->joinWith(['fivepkDefaultFivepkBigShark.compare']);
        $query = $this->bigSharkQueryConditions($query,$conditions);

        $pagination = new Pagination([
            'totalCount' => $count_query->count(1),
            'pageSize' => '500',
            'pageParam' => 'page',
            'pageSizeParam' => 'per-page'
        ]);

        $query = $query->orderBy('id DESC');
        $rows = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        $result = ['data'=>$rows,'pagination'=>$pagination];

        return $result;
    }

    /**
     * 游戏轨迹查询 分页查询数据
     * @param $conditions
     * @param $pageNo
     * @param $pageSize
     * @return array
     */
    public function bigSharkLocusPage($conditions, $pageNo, $pageSize){
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;

        $query = FivepkDefaultBigShark::find()->joinWith(['fivepkDefaultFivepkBigShark.compare']);
        $query = $this->bigSharkQueryConditions($query,$conditions);

        $query = $query->orderBy('id DESC');
        $data = $query
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();
        return $data;
    }

    /**
     * 游戏轨迹查询 总条数
     * @param $conditions
     * @param $pageNo
     * @param $pageSoze
     * @return array
     */
    public function bigSharkLocusCount($conditions){
        //统计总数
        $count_query = FivepkDefaultBigShark::find();
        return $this->bigSharkQueryConditions($count_query,$conditions,1)->count(1);
    }

    public function bigSharkQueryConditions($query,$conditions,$count= 0){

        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                if ('' !== $value && null !=$value) {
                    switch ($key) {
                        case 'account_id':
                            $query->andFilterWhere([$key=>trim($value)]);
                            break;
                        case 'machine_auto_id':
                            if($count == 1){
                                $query->joinWith('fivepkDefaultFivepkBigShark');
                            }
                            $query->andFilterWhere([$key=>$value]);

                            break;
                        case 'data_prize_type_id':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultFivepkBigShark');
                            }
                            //处理中奖牌型 大四梅 五梅
                            $FivepkPrizeTypeObj = new FivepkPrizeType();
                            $value = $FivepkPrizeTypeObj->getAllSon($value);
                            $query->andFilterWhere(['fivepk_default_fivepk_bigshark.'.$key=>$value]);
                            break;
                        case 'game_type':
                            $query->andFilterWhere(['fivepk_default_bigshark.'.$key=>$value]);
                            break;
                        case 'star_last_time':
                            $query->andFilterWhere(['>=', 'fivepk_default_bigshark.last_time',trim(strtotime($value)*1000)]);
                            break;
                        case 'end_last_time':
                            $query->andFilterWhere(['<=','fivepk_default_bigshark.last_time',trim(strtotime($value)*1000)]);
                            break;
                        case 'seoid':
                            $query->andFilterWhere([$key=>$value]);
                            break;
                        case 'awards_prize_type':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultFivepkBigShark');
                            }
                            $query->andFilterWhere(['data_prize_type_id'=>$value]);
                            break;
                        case 'random':
                            if($count == 1) {
                                $query->joinWith('fivepkDefaultFivepkBigShark');
                            }
                            $query->andFilterWhere(['fivepk_default_bigshark.'.$key=>$value]);
                            break;
                    }
                }
            }
        }
        return $query;

    }


    /**
     * 游戏轨迹查询 分页查询数据
     * @param $conditions
     * @param $pageNo
     * @param $pageSize
     * @return array
     */
    public function AttLocusPage($conditions, $pageNo, $pageSize){
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;

        $query = FivepkDefaultAtt2::find()->joinWith('fivepkDefaultFivepkAtt2.compare');
        $query = $this->filterAttQueryConditions($query,$conditions);

        $query = $query->orderBy('id DESC');
        $data = $query
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();

        return $data;
    }

    /**
     * 游戏轨迹查询 总条数
     * @param $conditions
     * @param $pageNo
     * @param $pageSoze
     * @return array
     */
    public function AttLocusCount($conditions){
        //统计总数
        $count_query = FivepkDefaultAtt2::find()->joinWith('fivepkDefaultFivepkAtt2.compare');
        return $this->filterAttQueryConditions($count_query,$conditions,1)->count(1);
    }

    /**
     * 查询条件 过滤
     * @param $query
     * @param array $conditions
     * @return mixed
     */
    public function filterAttQueryConditions($query,$conditions=[])
    {
        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                if ('' !== $value) {
                    switch ($key) {
                        case 'account_id':
                            $query->andFilterWhere(['fivepk_default_att2.'.$key=>trim($value)]);
                            break;
                        case 'machine_auto_id':
                            $query->andFilterWhere([$key=>$value]);
                            break;
                        //case 'win_type':  $query->andFilterWhere(['fivepk_default_fivepk_att2.data_prize_type_id'=>trim($value)]);break;
                        case 'star_last_time':$query->andFilterWhere(['>=','fivepk_default_att2.last_time',strtotime($value)*1000]); break;
                        case 'end_last_time':  $query->andFilterWhere(['<=','fivepk_default_att2.last_time',strtotime($value)*1000]); break;
                        case 'awards_prize_type':
                            $query->andFilterWhere(['fivepk_default_fivepk_att2.data_prize_type_id'=>$value]);
//                            $query->andFilterWhere(['>','prize_type',trim($value)])
//                            ->andFilterWhere(['!=','prize_type' ,19]);
                            break;
                        case 'game_type':
                            $query->andFilterWhere(['fivepk_default_att2.game_type'=>$value]);
                            break;
                        case 'data_prize_type_id':
                            $FivepkPrizeTypeObj = new FivepkPrizeType();
                            $value = $FivepkPrizeTypeObj->getAllSon($value);
                            $query->andFilterWhere(['fivepk_default_fivepk_att2.data_prize_type_id'=>($value)]);break;
                            break;
                        case 'random':
                            $query->andFilterWhere(['fivepk_default_fivepk_att2.random'=>$value]);
                            break;
                        default:
                            //$query->andFilterWhere([$key=>$value]);
                            break;
                    }
                }
            }
        }

        return $query;
    }

}