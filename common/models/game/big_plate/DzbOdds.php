<?php
namespace common\models\game\big_plate;

use backend\models\Tool;
use common\models\OddsChangePath;
use Yii;

//新老玩家机率
class DzbOdds extends Dzb
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'odds_dzb';
    }

    /**
     * @return \yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'prefab_royal_flush' => '大顺累积',
            'prefab_royal_flush_random' => '大顺随机',
            'prefab_royal_flush_fake' => '假大顺',

            'prefab_five_of_a_kind' => '五梅累积',
            'prefab_five_of_a_kind_random' => '五梅随机',
            'prefab_straight_flush' => '小顺累积',
            'prefab_straight_flush_random' => '小顺随机',
            'prefab_straight_flush_fake' => '假小顺',

            'prefab_four_of_a_kind_T_T' => '四梅累积',
            'prefab_four_of_a_kind_two_ten' => '四梅随机',
            'prefab_four_of_a_kind_random_level' => '四梅随机档位',
            'prefab_full_house' => '葫芦',
            'prefab_full_house_jp' => '葫芦2倍',
            'prefab_flush' => '同花',
            'prefab_flush_jp' => '同花2倍',
            'prefab_straight' => '顺子',
            'prefab_straight_jp' => '顺子2倍',
            'prefab_three_of_a_kind' => '三条',
            'prefab_three_of_a_kind_jp' => '三条2倍',
            'prefab_two_pairs' => '两对',
            'prefab_two_pairs_jp' => '两对2倍',
            'prefab_seven_better' => '一对',
            'prefab_seven_better_jp' => '一对2倍',
            'prefab_four_flush' => '四张同花',
            'prefab_four_straight' => '四张顺',
            'prefab_seven_better_keep' => '小一对',
            'prefab_joker' => '鬼牌',
            'prefab_force_seven_better' => '强制一对',
            'prefab_force_seven_better_count' => 'Prefab Force Seven Better Count',
            'prefab_compare_buff' => '比倍BUFFER',
            'prefab_compare_cut_down' => '比倍砍牌',
            'prefab_compare_cut_down_count' => 'Prefab Compare Cut Down Count',
            'prefab_compare_seven_joker' => 'Prefab Compare Seven Joker',
            'reservation_date' => 'Reservation Date',
            'prefab_random_two_times' => 'Prefab Random Two Times',
            'prefab_five_of_a_kind_double' => '五梅2倍',
            'prefab_royal_flush_double' => 'Prefab Royal Flush Double',
            'prefab_straight_flush_double' => 'Prefab Straight Flush Double',
            'prefab_four_of_a_kind_double' => '四梅2倍',
        ];
    }

    public function add($data)
    {
        try {
            //修改这个值必须 记录 修改的值
            $arr = Tool::distinctArr($data, $this->attributes, self::attributeLabels()  );
            if (!empty($arr)) {
                //只有修改机台的时候记录修改记录
                if($this->odds_type == 2){
                    //获取这个机台的名字
                    $machienModel = $this->getModelMachine();
                    $machineObj   = $machienModel::findOne($this->odds_type_id);
                    $OddsChangePathModel = new OddsChangePath();
                    $postData            = array(
                        'game_type' => $this->gameType,
                        'type'      => $OddsChangePathModel->typeMachine,
                        'type_id'   => isset( $machineObj->seo_machine_id ) ? $machineObj->seo_machine_id : "",
                        'content'   => json_encode($arr, JSON_UNESCAPED_UNICODE),
                    );
                    $OddsChangePathModel->add($postData);
                }

                foreach ($data as $key => $val) {
                    $this->$key = $val;
                }
                if ($this->save()) {
                    return $this->attributes;
                } else {
                    throw new MyException(implode(",", $this->getFirstErrors()));
                }
            }
            return true;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 通过oddstype 和 oddstypeid 查询数据
     * @param $oddsType
     * @param $oddsTypeIds
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findByTypeIds( $oddsType, $oddsTypeIds){
        return self::find()
            ->andWhere(['=', 'odds_type', $oddsType] )
            ->andWhere(['in', 'odds_type_id', $oddsTypeIds])
            ->asArray()->all();
    }

    /**
     * 通过oddstype 和 oddstypeid 查询数据
     * @param $oddsType
     * @param $oddsTypeId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findByTypeId( $oddsType, $oddsTypeId){
        return self::find()
            ->andWhere(['=', 'odds_type', $oddsType] )
            ->andWhere(['=', 'odds_type_id', $oddsTypeId])
            ->one();
    }

    /**
     * 修改多条数据
     * @param $oddsType   1新玩家 2机台 3老玩家
     * @param $oddsTypeIds
     * @param $data 修改的数据
     * @return int
     */
    public function updateMore($oddsType,$oddsTypeIds, $data)
    {
        foreach ($oddsTypeIds as $id){
            $obj = self::findByTypeId( $oddsType, $id);
            $obj->add($data);
        }
        //获取所有的机台信息
//        $objs = $this->findByTypeIds($oddsType,$oddsTypeIds);
//        $objIds = array_column($objs,'id');
//        return self::updateAll($data, ['in', 'id', $objIds]);
    }


    /**
     *   初始化用户数据
     */
    public function initUserInfo($oddsType,$accountIdArr)
    {
        $table = self::tableName();
        $isDefault = 2;
        //首先删除所有的用户数据
        if( !empty($accountIdArr) ){
            $inStr = "'".implode("','", $accountIdArr)."'";
            self::deleteAll("is_default = {$isDefault} and odds_type = :odds_type and odds_type_id not in ({$inStr})",[':odds_type'=>$oddsType]);
        }else{
            self::deleteAll("is_default = {$isDefault} and odds_type = :odds_type ",[':odds_type'=>$oddsType]);
        }

        //然后 初始化所有用户的玩家几率
        $sql = "
            insert into {$table} (
                is_default,
                odds_type,
                odds_type_id,
                prefab_royal_flush,
                prefab_royal_flush_random ,
                prefab_royal_flush_fake ,
                prefab_five_of_a_kind ,
                prefab_five_of_a_kind_random ,
                prefab_straight_flush ,
                prefab_straight_flush_random ,
                prefab_straight_flush_fake ,
                prefab_four_of_a_kind_T_T ,
                prefab_four_of_a_kind_two_ten ,
                prefab_four_of_a_kind_random_level ,
                prefab_full_house ,
                prefab_full_house_jp,
                prefab_flush,
                prefab_flush_jp ,
                prefab_straight ,
                prefab_straight_jp ,
                prefab_three_of_a_kind ,
                prefab_three_of_a_kind_jp ,
                prefab_two_pairs ,
                prefab_two_pairs_jp ,
                prefab_seven_better ,
                prefab_seven_better_jp ,
                prefab_four_flush ,
                prefab_four_straight ,
                prefab_seven_better_keep ,
                prefab_joker ,
                compare_history_cards ,
                prefab_force_seven_better,
                prefab_force_seven_better_count ,
                prefab_compare_buff ,
                prefab_compare_cut_down ,
                prefab_compare_cut_down_count ,
                prefab_compare_seven_joker ,
                prefab_random_two_times ,
                prefab_five_of_a_kind_double ,
                prefab_royal_flush_double ,
                prefab_straight_flush_double,
                prefab_four_of_a_kind_double 
                )
            select 
                '{$isDefault}',
                odds_dzb.odds_type,
                fivepk_account.account_id as odds_type_id,
                odds_dzb.prefab_royal_flush,
                odds_dzb.prefab_royal_flush_random ,
                odds_dzb.prefab_royal_flush_fake ,
                odds_dzb.prefab_five_of_a_kind ,
                odds_dzb.prefab_five_of_a_kind_random ,
                odds_dzb.prefab_straight_flush ,
                odds_dzb.prefab_straight_flush_random ,
                odds_dzb.prefab_straight_flush_fake ,
                odds_dzb.prefab_four_of_a_kind_T_T ,
                odds_dzb.prefab_four_of_a_kind_two_ten ,
                odds_dzb.prefab_four_of_a_kind_random_level ,
                odds_dzb.prefab_full_house ,
                odds_dzb.prefab_full_house_jp,
                odds_dzb.prefab_flush,
                odds_dzb.prefab_flush_jp ,
                odds_dzb.prefab_straight ,
                odds_dzb.prefab_straight_jp ,
                odds_dzb.prefab_three_of_a_kind ,
                odds_dzb.prefab_three_of_a_kind_jp ,
                odds_dzb.prefab_two_pairs ,
                odds_dzb.prefab_two_pairs_jp ,
                odds_dzb.prefab_seven_better ,
                odds_dzb.prefab_seven_better_jp ,
                odds_dzb.prefab_four_flush ,
                odds_dzb.prefab_four_straight ,
                odds_dzb.prefab_seven_better_keep ,
                odds_dzb.prefab_joker ,
                odds_dzb.compare_history_cards ,
                odds_dzb.prefab_force_seven_better,
                odds_dzb.prefab_force_seven_better_count ,
                odds_dzb.prefab_compare_buff ,
                odds_dzb.prefab_compare_cut_down ,
                odds_dzb.prefab_compare_cut_down_count ,
                odds_dzb.prefab_compare_seven_joker ,
                odds_dzb.prefab_random_two_times ,
                odds_dzb.prefab_five_of_a_kind_double ,
                odds_dzb.prefab_royal_flush_double ,
                odds_dzb.prefab_straight_flush_double,
                odds_dzb.prefab_four_of_a_kind_double 
            from fivepk_account
            LEFT JOIN odds_dzb on odds_dzb.is_default = 1 and odds_type = '{$oddsType}'
        ";
        if( !empty($accountIdArr) ){
            $inStr = "'".implode("','", $accountIdArr)."'";
            $sql .= "  where fivepk_account.account_id not in ({$inStr})";
        }
        $this->Tool->myLog("sql is:".$sql);
        Yii::$app->game_db->createCommand($sql)->query();

        //还要初始化buff值
        $oddsCountModel = $this->getModelOddsCount();
        $oddsCountModel->initUserInfo($oddsType,$accountIdArr);
        return true;
    }

    /**
     *   初始化  机台 数据  修改原始数据版本
     */
    public function initMachineInfo($machineIdArr){
        $table = self::tableName();
        $oddsType = 2;
        //因为每个场次的默认机率都是不一样的，所以这里得把所有的机台按照场次区分开来插入
        $MahineArr = array();
        $DzbMachineModel = new DzbMachine();
        $DzbMachineObj   = $DzbMachineModel->find()->andWhere(['in', 'auto_id', $machineIdArr])->asArray()->all();
        foreach ($DzbMachineObj as $val)
        {
            $arr = explode("_", $val['room_info_list_id'] );
            $level = end($arr);
            $MahineArr[$level] = isset( $MahineArr[$level] ) ? $MahineArr[$level] : array();
            array_push($MahineArr[$level], $val['auto_id']);
        }

        foreach ($MahineArr as $key=>$LevelMachineIdArr){
            //需要初始化的机台的id
            $inStr = "'".implode("','", $LevelMachineIdArr)."'";
            //然后 初始化所有用户的玩家几率
            $sql = "
            update {$table} 
            INNER JOIN
            (select
                prefab_royal_flush,
                prefab_royal_flush_random ,
                prefab_royal_flush_fake ,
                prefab_five_of_a_kind ,
                prefab_five_of_a_kind_random ,
                prefab_straight_flush ,
                prefab_straight_flush_random ,
                prefab_straight_flush_fake ,
                prefab_four_of_a_kind_T_T ,
                prefab_four_of_a_kind_two_ten ,
                prefab_four_of_a_kind_random_level ,
                prefab_full_house ,
                prefab_full_house_jp,
                prefab_flush,
                prefab_flush_jp ,
                prefab_straight ,
                prefab_straight_jp ,
                prefab_three_of_a_kind ,
                prefab_three_of_a_kind_jp ,
                prefab_two_pairs ,
                prefab_two_pairs_jp ,
                prefab_seven_better ,
                prefab_seven_better_jp ,
                prefab_four_flush ,
                prefab_four_straight ,
                prefab_seven_better_keep ,
                prefab_joker ,
                compare_history_cards ,
                prefab_force_seven_better,
                prefab_force_seven_better_count ,
                prefab_compare_buff ,
                prefab_compare_cut_down ,
                prefab_compare_cut_down_count ,
                prefab_compare_seven_joker ,
                prefab_random_two_times ,
                prefab_five_of_a_kind_double ,
                prefab_royal_flush_double ,
                prefab_straight_flush_double,
                prefab_four_of_a_kind_double
            from {$table}
            where is_default = 1 and odds_type = {$oddsType} and odds_type_id = {$key})t
            set 
                odds_dzb.prefab_royal_flush = t.prefab_royal_flush,
                odds_dzb.prefab_royal_flush_random = t.prefab_royal_flush_random ,
                odds_dzb.prefab_royal_flush_fake = t.prefab_royal_flush_fake ,
                odds_dzb.prefab_five_of_a_kind = t.prefab_five_of_a_kind,
                odds_dzb.prefab_five_of_a_kind_random = t.prefab_five_of_a_kind_random,
                odds_dzb.prefab_straight_flush = t.prefab_straight_flush,
                odds_dzb.prefab_straight_flush_random = t.prefab_straight_flush_random,
                odds_dzb.prefab_straight_flush_fake = t.prefab_straight_flush_fake,
                odds_dzb.prefab_four_of_a_kind_T_T = t.prefab_four_of_a_kind_T_T,
                odds_dzb.prefab_four_of_a_kind_two_ten = t.prefab_four_of_a_kind_two_ten,
                odds_dzb.prefab_four_of_a_kind_random_level = t.prefab_four_of_a_kind_random_level,
                odds_dzb.prefab_full_house = t.prefab_full_house,
                odds_dzb.prefab_full_house_jp = t.prefab_full_house_jp,
                odds_dzb.prefab_flush = t.prefab_flush,
                odds_dzb.prefab_flush_jp = t.prefab_flush_jp,
                odds_dzb.prefab_straight = t.prefab_straight,
                odds_dzb.prefab_straight_jp = t.prefab_straight_jp,
                odds_dzb.prefab_three_of_a_kind = t.prefab_three_of_a_kind,
                odds_dzb.prefab_three_of_a_kind_jp = t.prefab_three_of_a_kind_jp,
                odds_dzb.prefab_two_pairs = t.prefab_two_pairs,
                odds_dzb.prefab_two_pairs_jp = t.prefab_two_pairs_jp,
                odds_dzb.prefab_seven_better = t.prefab_seven_better,
                odds_dzb.prefab_seven_better_jp = t.prefab_seven_better_jp,
                odds_dzb.prefab_four_flush = t.prefab_four_flush,
                odds_dzb.prefab_four_straight = t.prefab_four_straight,
                odds_dzb.prefab_seven_better_keep = t.prefab_seven_better_keep,
                odds_dzb.prefab_joker = t.prefab_joker,
                odds_dzb.compare_history_cards = t.compare_history_cards,
                odds_dzb.prefab_force_seven_better = t.prefab_force_seven_better,
                odds_dzb.prefab_force_seven_better_count = t.prefab_force_seven_better_count,
                odds_dzb.prefab_compare_buff = t.prefab_compare_buff,
                odds_dzb.prefab_compare_cut_down = t.prefab_compare_cut_down,
                odds_dzb.prefab_compare_cut_down_count = t.prefab_compare_cut_down_count,
                odds_dzb.prefab_compare_seven_joker = t.prefab_compare_seven_joker,
                odds_dzb.prefab_random_two_times = t.prefab_random_two_times,
                odds_dzb.prefab_five_of_a_kind_double = t.prefab_five_of_a_kind_double,
                odds_dzb.prefab_royal_flush_double = t.prefab_royal_flush_double,
                odds_dzb.prefab_straight_flush_double = t.prefab_straight_flush_double,
                odds_dzb.prefab_four_of_a_kind_double = t.prefab_four_of_a_kind_double
            where odds_dzb.odds_type_id in ({$inStr})
        ";
            $this->Tool->myLog("sql is:".$sql);
            Yii::$app->game_db->createCommand($sql)->query();
        }

        return true;
    }

//    /**
//     *   初始化  机台 数据  删除原始数据版本
//     */
//    public function initMachineInfo($machineIdArr)
//    {
//        $table = self::tableName();
//        $isDefault = 2;
//        //首先删除 指定机台 数据
//        if( !empty( $machineIdArr ) ){
//            $inStr = "'".implode("','", $machineIdArr)."'";
//            self::deleteAll("is_default = {$isDefault} and odds_type = 2 and odds_type_id in ({$inStr})");
//        }else{
//            self::deleteAll("is_default = {$isDefault} and odds_type = 2");
//        }
//        //因为每个场次的默认机率都是不一样的，所以这里得把所有的机台按照场次区分开来插入
//        $MahineArr = array();
//        $DzbMachineModel = new DzbMachine();
//        $DzbMachineObj   = $DzbMachineModel->find()->andWhere(['in', 'auto_id', $machineIdArr])->asArray()->all();
//        foreach ($DzbMachineObj as $val)
//        {
//            $arr = explode("_", $val['room_info_list_id'] );
//            $level = end($arr);
//            $MahineArr[$level] = isset( $MahineArr[$level] ) ? $MahineArr[$level] : array();
//            array_push($MahineArr[$level], $val['auto_id']);
//        }
//
//        foreach ($MahineArr as $key=>$LevelMachineIdArr){
//            //需要初始化的机台的id
//            $inStr = "'".implode("','", $LevelMachineIdArr)."'";
//            //然后 初始化所有用户的玩家几率
//            $sql = "
//            insert into {$table} (
//                is_default,
//                odds_type,
//                odds_type_id,
//                prefab_royal_flush,
//                prefab_royal_flush_random ,
//                prefab_royal_flush_fake ,
//                prefab_five_of_a_kind ,
//                prefab_five_of_a_kind_random ,
//                prefab_straight_flush ,
//                prefab_straight_flush_random ,
//                prefab_straight_flush_fake ,
//                prefab_four_of_a_kind_T_T ,
//                prefab_four_of_a_kind_two_ten ,
//                prefab_four_of_a_kind_random_level ,
//                prefab_full_house ,
//                prefab_full_house_jp,
//                prefab_flush,
//                prefab_flush_jp ,
//                prefab_straight ,
//                prefab_straight_jp ,
//                prefab_three_of_a_kind ,
//                prefab_three_of_a_kind_jp ,
//                prefab_two_pairs ,
//                prefab_two_pairs_jp ,
//                prefab_seven_better ,
//                prefab_seven_better_jp ,
//                prefab_four_flush ,
//                prefab_four_straight ,
//                prefab_seven_better_keep ,
//                prefab_joker ,
//                compare_history_cards ,
//                prefab_force_seven_better,
//                prefab_force_seven_better_count ,
//                prefab_compare_buff ,
//                prefab_compare_cut_down ,
//                prefab_compare_cut_down_count ,
//                prefab_compare_seven_joker ,
//                prefab_random_two_times ,
//                prefab_five_of_a_kind_double ,
//                prefab_royal_flush_double ,
//                prefab_straight_flush_double,
//                prefab_four_of_a_kind_double
//                )
//            select
//                '{$isDefault}',
//                odds_dzb.odds_type,
//                machine.auto_id as odds_type_id,
//                odds_dzb.prefab_royal_flush,
//                odds_dzb.prefab_royal_flush_random ,
//                odds_dzb.prefab_royal_flush_fake ,
//                odds_dzb.prefab_five_of_a_kind ,
//                odds_dzb.prefab_five_of_a_kind_random ,
//                odds_dzb.prefab_straight_flush ,
//                odds_dzb.prefab_straight_flush_random ,
//                odds_dzb.prefab_straight_flush_fake ,
//                odds_dzb.prefab_four_of_a_kind_T_T ,
//                odds_dzb.prefab_four_of_a_kind_two_ten ,
//                odds_dzb.prefab_four_of_a_kind_random_level ,
//                odds_dzb.prefab_full_house ,
//                odds_dzb.prefab_full_house_jp,
//                odds_dzb.prefab_flush,
//                odds_dzb.prefab_flush_jp ,
//                odds_dzb.prefab_straight ,
//                odds_dzb.prefab_straight_jp ,
//                odds_dzb.prefab_three_of_a_kind ,
//                odds_dzb.prefab_three_of_a_kind_jp ,
//                odds_dzb.prefab_two_pairs ,
//                odds_dzb.prefab_two_pairs_jp ,
//                odds_dzb.prefab_seven_better ,
//                odds_dzb.prefab_seven_better_jp ,
//                odds_dzb.prefab_four_flush ,
//                odds_dzb.prefab_four_straight ,
//                odds_dzb.prefab_seven_better_keep ,
//                odds_dzb.prefab_joker ,
//                odds_dzb.compare_history_cards ,
//                odds_dzb.prefab_force_seven_better,
//                odds_dzb.prefab_force_seven_better_count ,
//                odds_dzb.prefab_compare_buff ,
//                odds_dzb.prefab_compare_cut_down ,
//                odds_dzb.prefab_compare_cut_down_count ,
//                odds_dzb.prefab_compare_seven_joker ,
//                odds_dzb.prefab_random_two_times ,
//                odds_dzb.prefab_five_of_a_kind_double ,
//                odds_dzb.prefab_royal_flush_double ,
//                odds_dzb.prefab_straight_flush_double,
//                odds_dzb.prefab_four_of_a_kind_double
//            from fivepk_seo_big_plate as machine
//            LEFT JOIN odds_dzb on odds_dzb.is_default = 1 and odds_dzb.odds_type = 2 and odds_dzb.odds_type_id = '{$key}'
//            where machine.auto_id in ({$inStr})
//        ";
//            $this->Tool->myLog("sql is:".$sql);
//            Yii::$app->game_db->createCommand($sql)->query();
//        }
//
//        return true;
//    }

}
