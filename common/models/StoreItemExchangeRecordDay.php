<?php
namespace common\models;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\redis\MyRedis;
use backend\models\Tool;
use Yii;


class StoreItemExchangeRecordDay extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'store_item_exchange_record_day';
    }

    /**
     * @return null|object|\yii\db\Connection
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
            [['account_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'account_id' => '用户ID',
            'nick_name'  => '昵称',
            'prize'      => '价格',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 统计一天的数据 求结果存到表里面
     * @param $stime
     * @param $etime
     * @return bool
     */
    public function RecordToday($stime, $etime)
    {
        $nowDay      = date("Y-m-d", $stime);
        $sql = "
                insert into store_item_exchange_record_day (
                    account_id,nick_name,item_exchange_list_order,num,create_time
                )
                select account_id,nick_name,item_exchange_list_order,count(prize) as num,'{$nowDay}'
                from store_item_exchange_record
                where create_time between '{$stime}' and '{$etime}'
                group by account_id,item_exchange_list_order
        ";
        Yii::$app->game_db->createCommand($sql)->query();
        return true;
    }

    /**
     *   分页获取数据
     */
    public function page($pageNo, $pageSize, $where)
    {
        $sql = "
                select rid.*,pinfo.nick_name as nickName
                from store_item_exchange_record_day as rid
                left join fivepk_player_info as pinfo on rid.account_id = pinfo.account_id
                where $where
                order by create_time desc,id
        ";
        return Yii::$app->game_db->createCommand($sql)->queryAll();
    }

    /**
     *   分页获取数据
     */
    public function pageDay($pageNo, $pageSize, $where)
    {
        $sql = "
                select create_time,item_exchange_list_order,sum(num) as num
                from store_item_exchange_record_day as rid
                where $where
                group by create_time,item_exchange_list_order
                order by create_time desc
        ";
        $data = Yii::$app->game_db->createCommand($sql)->queryAll();
        $rs = array();
        foreach ($data as $val){
            $rs[$val['create_time']][$val['item_exchange_list_order']] = $val['num'];
        }
        return $rs;
    }

    /**
     *   统计求和
     */
    public function DataCount( $where)
    {
        $sql = "
                select item_exchange_list_order,sum(num) as num
                from store_item_exchange_record_day as rid
                where $where
                group by item_exchange_list_order
        ";
        $data = Yii::$app->game_db->createCommand($sql)->queryAll();
        $rs = array();
        foreach ($data as $val){
            $rs[$val['item_exchange_list_order']] = $val['num'];
        }
        return $rs;
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
        $this->RecordToday($stime, $etime);
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
