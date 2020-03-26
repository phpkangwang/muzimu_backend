<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-5-29
 * Time: 18:21
 */

namespace backend\models\services;


use common\services\ToolService;

class ReportService
{
    public static function getTurnoverReport($promo_codes,$start_time,$end_time)
    {
        $sql = "
            SELECT 
            b.account_id as ID, 
            a.seoid as 推广号,
            count(*) as 操作笔数,
            sum(b.up_coin) as 总上钻,
            sum(b.down_coin) as 总下钻,
            sum(b.up_coin-b.down_coin) as 营业额,
            b.operate_time as 操作时间
            FROM
            fivepk_account AS a,
            fivepk_point AS b
            WHERE 
            a.account_id=b.account_id 
            AND a.seoid IN ('" . $promo_codes . "')
            and b.operate_time between :starttime and :endtime
        ";


        $result = ToolService::SelectPDO('game_db', $sql, [
            ":starttime" => $start_time,
            ":endtime" => $end_time,
        ]);


        return $result;
    }
}