<?php

namespace common\models\game\snow_leopard;

use Yii;

//新老玩家机率
class BaoOdds extends Bao
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'odds_snow_leopard';
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
     * 初始化用户数据
     * @param $oddsType
     * @param $accountIdArr
     */
    public function initUserInfo($oddsType, $accountIdArr)
    {
        $table     = self::tableName();
        $isDefault = 2;
        //首先删除所有的用户数据
        if (!empty($accountIdArr)) {
            $inStr = "'" . implode("','", $accountIdArr) . "'";
            self::deleteAll("is_default = {$isDefault} and odds_type = :odds_type and odds_type_id not in ({$inStr})", [':odds_type' => $oddsType]);
        } else {
            self::deleteAll("is_default = {$isDefault} and odds_type = :odds_type ", [':odds_type' => $oddsType]);
        }

        $arr     =
            [
                'mechine_peak_score_section',
                'mechine_peak_value_and_win_score',
                'big_bar_peak_value_section',
                'middle_bar_peak_value_section',
                'small_bar_peak_value_section',
                'without_bar_peak_value_section',
                'vole_peak_value_section',
                'big_bar_peak_value_and_accum_value',
                'middle_bar_peak_value_and_accum_value',
                'small_bar_peak_value_and_accum_value',
                'without_bar_peak_value_and_accum_value',
                'vole_peak_value_and_accum_value'
            ];
        $fields  = implode(',', $arr);
        $fields2 = $table . '.' . implode(",$table.", $arr);
        //然后 初始化所有用户的玩家几率
        $sql = "
            insert into {$table} (
                is_default,
                odds_type,
                odds_type_id,
                {$fields}
                )
            select 
                '{$isDefault}',
                {$table}.odds_type,
                fivepk_account.account_id as odds_type_id,
                {$fields2}
            from fivepk_account
            LEFT JOIN {$table} on {$table}.is_default = 1 and odds_type = '{$oddsType}'
        ";

        if (!empty($accountIdArr)) {
            $inStr = "'" . implode("','", $accountIdArr) . "'";
            $sql   .= "  where fivepk_account.account_id not in ({$inStr})";
        }
        $this->Tool->myLog("sql is:" . $sql);
        Yii::$app->game_db->createCommand($sql)->query();
    }

}
