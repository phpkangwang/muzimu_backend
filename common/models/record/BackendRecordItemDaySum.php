<?php
namespace common\models\record;

use backend\models\BaseModel;
use backend\models\Tool;
use common\models\DataGameListInfo;
use common\models\DataRoomInfoList;
use common\models\FivepkPrizeType;
use phpDocumentor\Reflection\Types\Boolean;
use Yii;

class BackendRecordItemDaySum extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_record_item_day_sum';
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
            'id'           => 'ID',
            'num'          => '当天数量',
            'sum_day'      => '总数量',
            'create_time'  => '修改时间',
        ];
    }



    /**
     * 统计一天的数据 求结果存到表里面
     * @param $time   2019-03-03
     * @return bool
     */
    public function RecordToday($time)
    {
        $nowDay      = date("Y-m-d", $time);
        $beforeStime = date("Y-m-d", $time - 24*60*60);
        //找到昨天总数量
        $beforeStimeSql = "select * from backend_record_item_day_sum where create_time = '{$beforeStime}'";
        $beforeStimeObj = Yii::$app->game_db->createCommand($beforeStimeSql)->queryOne();
        $beforeStimeSumDay = isset($beforeStimeObj['sum_day']) ? $beforeStimeObj['sum_day'] : 0;

        //存入所有玩家每天一共有多少奖券到数据库
        $sumSql = "
                insert into backend_record_item_day_sum (
                    type,num,sum_day,create_time
                )
                select 2,t.num,{$beforeStimeSumDay}+t.num as sum_day,create_time from 
                    (select sum(num) as num, create_time
                    from backend_record_item_day
                    where create_time = '{$nowDay}'
                    ) t
        ";
        Yii::$app->game_db->createCommand($sumSql)->query();

        //每个玩家每天一共有多少奖券到数据库
        $accountSql = "
                insert into backend_record_item_day_sum (
                    type,account_id,num,sum_day,create_time
                )
                select 1,t.account_id,t.num,IFNULL(rid.sum_day,0)+t.num as ridsum,t.create_time from 
                    (select account_id,sum(num) as num, create_time
                    from backend_record_item_day
                    where create_time = '{$nowDay}'
                    group by account_id
                    ) t
                left join (select * from backend_record_item_day_sum where create_time = '{$beforeStime}' ) rid on rid.account_id = t.account_id
        ";
        Yii::$app->game_db->createCommand($accountSql)->query();
        return true;
    }

    /**
     *  重新统计当天的数据
     *  因为数据需要实时的，所以首先删除今天的数据，然后重新生成今天的数据
     * @param $stime
     * @param $etime
     * @return array
     */
    public function RecordTodayInit()
    {
        $today = time();
        $stime = strtotime( date("Y-m-d 00:00:00", $today) ) ;
        $etime = strtotime( date("Y-m-d 23:59:59", $today) ) ;
        $nowDay      = date("Y-m-d", $stime);
        //删除今天的数据
        $this->deleteByDay($nowDay);

        //重新添加今天的数据
        $this->RecordToday($stime);
        return true;
    }

    /**
     *  按天删除数据
     * @param $day
     */
    public function deleteByDay($day){
        self::deleteAll(['create_time'=>$day]);
    }
}
