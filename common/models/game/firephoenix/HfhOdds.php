<?php

namespace common\models\game\firephoenix;

use Yii;

//新老玩家机率
class HfhOdds extends Hfh
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'odds_hfh';
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
            'id' => 'ID',
        ];
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
                    prefab_five_bars ,
                    prefab_five_bars_count ,
                    prefab_royal_flush ,
                    prefab_royal_flush_fake ,
                    prefab_royal_flush_count ,
                    prefab_five_of_a_kind ,
                    prefab_five_of_a_kind_count ,
                    prefab_five_of_a_kind_compare ,
                    prefab_five_of_a_kind_compare_record ,
                    prefab_straight_flush ,
                    prefab_straight_flush_fake ,
                    prefab_straight_flush_count ,
                    prefab_four_of_a_kind_joker_two ,
                    prefab_four_of_a_kind_Joker_count_two ,
                    prefab_four_of_a_kind_Joker_two_fourteen_two ,
                    prefab_four_of_a_kind_Joker_two_fourteen_record ,
                    prefab_four_of_a_kind_J_A ,
                    prefab_four_of_a_kind_J_A_count ,
                    prefab_four_of_a_kind_ja ,
                    prefab_four_of_a_kind_T_T ,
                    prefab_four_of_a_kind_T_T_count ,
                    prefab_four_of_a_kind_two_ten ,
                    prefab_four_of_a_kind_two_ten_two ,
                    prefab_four_of_a_kind_two_ten_continue ,
                    prefab_four_of_a_kind_two_ten_continue_count ,
                    prefab_four_of_a_kind_two_ten_continue_record ,
                    prefab_four_of_a_kind_two_ten_continue_rate ,
                    prefab_full_house ,
                    prefab_flush ,
                    prefab_straight ,
                    prefab_three_of_a_kind ,
                    prefab_two_pairs ,
                    prefab_seven_better ,
                    prefab_four_flush ,
                    prefab_four_straight ,
                    prefab_seven_better_keep ,
                    prefab_joker ,
                    machine_auto ,
                    compare_history_cards ,
                    prefab_force_seven_better ,
                    prefab_force_seven_better_count,
                    prefab_compare_buff ,
                    prefab_compare_cut_down ,
                    prefab_compare_cut_down_count ,
                    prefab_compare_seven_joker 
                )
            select 
                '{$isDefault}',
                odds.odds_type,
                fivepk_account.account_id as odds_type_id,
                prefab_five_bars ,
                odds.prefab_five_bars_count ,
                odds.prefab_royal_flush ,
                odds.prefab_royal_flush_fake ,
                odds.prefab_royal_flush_count ,
                odds.prefab_five_of_a_kind ,
                odds.prefab_five_of_a_kind_count ,
                odds.prefab_five_of_a_kind_compare ,
                odds.prefab_five_of_a_kind_compare_record ,
                odds.prefab_straight_flush ,
                odds.prefab_straight_flush_fake ,
                odds.prefab_straight_flush_count ,
                odds.prefab_four_of_a_kind_joker_two ,
                odds.prefab_four_of_a_kind_Joker_count_two ,
                odds.prefab_four_of_a_kind_Joker_two_fourteen_two ,
                odds.prefab_four_of_a_kind_Joker_two_fourteen_record ,
                odds.prefab_four_of_a_kind_J_A ,
                odds.prefab_four_of_a_kind_J_A_count ,
                odds.prefab_four_of_a_kind_ja ,
                odds.prefab_four_of_a_kind_T_T ,
                odds.prefab_four_of_a_kind_T_T_count ,
                odds.prefab_four_of_a_kind_two_ten ,
                odds.prefab_four_of_a_kind_two_ten_two ,
                odds.prefab_four_of_a_kind_two_ten_continue ,
                odds.prefab_four_of_a_kind_two_ten_continue_count ,
                odds.prefab_four_of_a_kind_two_ten_continue_record ,
                odds.prefab_four_of_a_kind_two_ten_continue_rate ,
                odds.prefab_full_house ,
                odds.prefab_flush ,
                odds.prefab_straight ,
                odds.prefab_three_of_a_kind ,
                odds.prefab_two_pairs ,
                odds.prefab_seven_better ,
                odds.prefab_four_flush ,
                odds.prefab_four_straight ,
                odds.prefab_seven_better_keep ,
                odds.prefab_joker ,
                odds.machine_auto ,
                odds.compare_history_cards ,
                odds.prefab_force_seven_better ,
                odds.prefab_force_seven_better_count,
                odds.prefab_compare_buff ,
                odds.prefab_compare_cut_down ,
                odds.prefab_compare_cut_down_count ,
                odds.prefab_compare_seven_joker
            from fivepk_account
            LEFT JOIN {$table} as odds on odds.is_default = 1 and odds_type = '{$oddsType}'
        ";
        if( !empty($accountIdArr) ){
            $inStr = "'".implode("','", $accountIdArr)."'";
            $sql .= "  where fivepk_account.account_id not in ({$inStr})";
        }

        Yii::$app->game_db->createCommand($sql)->query();
    }
}
