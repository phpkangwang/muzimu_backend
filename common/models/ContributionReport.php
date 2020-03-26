<?php

namespace common\models;

use backend\models\BaseModel;
use common\models\game\FivepkPlayerInfo;
use Yii;

class ContributionReport extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_contribution_day';
    }

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
            'id'         => 'ID',
            'pop_code'   => '推广号',
            'created_at' => '日期',
        ];
    }

    /**
     *   生成竟日的贡献度
     */
    public function RecordToDay()
    {
        //获取所有用户今日的贡献度
        $day = date("Y-m-d", time());
        //插入之前先删除数据
        $this->deleteByDay($day);
        $sql = "
            insert into backend_contribution_day (
                    account_id,nick_name,pop_code,num,create_time
                )
            select account.account_id,info.nick_name,account.seoid,sum(info.today_contribution) as today_contribution,'{$day}'
            from fivepk_player_info as info
            inner join fivepk_account as account on info.account_id = account.account_id
            group by account.account_id
        ";
        Yii::$app->game_db->createCommand($sql)->query();
        return true;
    }

    /**
     *   生成昨天的数据
     */
    public function RecordToBeforeDay()
    {
        //获取昨天是一个月的哪一天
        $fullTime = date("Y-m-d", time() - 86400);

        //获取年份
//        $year      = date("Y", time());
//        $MonthFull = date("m", time());
//        $Month    = (int)$MonthFull;
//        $yearMonth = $year."-".$Month;
//        $fullTime = $day; //完整的时间2019-01-01不是2019-1-1
        //插入之前先删除数据
        $this->deleteByDay($fullTime);
        $sql = "
            insert into backend_contribution_day (
                    account_id,nick_name,pop_code,num,create_time
            )SELECT
            account.account_id,
            info.nick_name,
            account.seoid,
            info.yesterday_contribution AS contribution,
            '{$fullTime}'
            FROM
            fivepk_account AS account
            INNER JOIN fivepk_player_info AS info ON account.account_id = info.account_id
            WHERE info.yesterday_contribution !=0
        ";
        Yii::$app->game_db->createCommand($sql)->query();
        return true;
    }

    /**
     *  按天删除数据
     * @param $day
     */
    public function deleteByDay($day)
    {
        self::deleteAll(['create_time' => $day]);
    }

}
