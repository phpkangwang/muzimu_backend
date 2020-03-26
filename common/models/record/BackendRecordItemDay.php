<?php
namespace common\models\record;

use backend\models\BaseModel;
use backend\models\Tool;
use common\models\DataGameListInfo;
use common\models\DataRoomInfoList;
use common\models\FivepkPrizeType;
use phpDocumentor\Reflection\Types\Boolean;
use Yii;

class BackendRecordItemDay extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'backend_record_item_day';
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
            'id'               => 'ID',
            'account_id'       => '用户ID',
            'game_type'        => '游戏类型',
            'room_index'       => '房间等级',//1体验场 2初级场
            'item_type'        => '商品类型',// store_item_list_data 的 item_type
            'num'              => '获取数量',
            'create_time'      => '修改时间',
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
                insert into backend_record_item_day (
                    account_id,game_type,room_index,item_type,num,create_time
                )
                select account_id,game_type,room_index,item_type,sum(num) as sum,'{$nowDay}'
                from backend_record_item
                where create_time between '{$stime}' and '{$etime}'
                group by account_id,game_type,room_index
        ";
        Yii::$app->game_db->createCommand($sql)->query();

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

    /**
     *   分页获取数据
     */
    public function page($pageNo, $pageSize, $where)
    {
        $rs = Tool::page($pageNo,$pageSize);
        $limit  = $rs['limit'];
        $offset = $rs['offset'];
        $sql = "
                select rid.account_id,sum(rid.num) as num,rid.create_time, pinfo.nick_name as nickName, account.seoid as seoid
                from backend_record_item_day as rid
                left join fivepk_player_info as pinfo          on rid.account_id = pinfo.account_id
                left join fivepk_account as account            on rid.account_id = account.account_id
                where $where
                group by rid.create_time,rid.account_id
                order by rid.create_time desc
                limit {$limit}
                offset {$offset}
        ";
        return Yii::$app->game_db->createCommand($sql)->queryAll();
    }

    public function pageRoom($pageNo, $pageSize, $where)
    {
        $rs = Tool::page($pageNo,$pageSize);
        $limit  = $rs['limit'];
        $offset = $rs['offset'];
        $sql = "
                select 
                    rid.game_type,
                    rid.room_index,
                    sum(rid.num) as num,
                    rid.create_time
                from backend_record_item_day as rid
                where 
                $where
                group by rid.game_type,rid.room_index,rid.create_time
                order by rid.create_time desc,rid.game_type asc,rid.room_index asc
                limit {$limit}
                offset {$offset}
        ";
        return Yii::$app->game_db->createCommand($sql)->queryAll();
    }


    /**
     *   获取有奖券这个奖的所有的房间
     */
    public function getAllRoom()
    {
        //获取所有开启的游戏
        $DataGameListInfoObj = new DataGameListInfo();
        $games = $DataGameListInfoObj->getOpenGame();
        //获取所有的房间
        $DataRoomInfoListObj  = new DataRoomInfoList();
        $DataRoomInfoListObjs = $DataRoomInfoListObj->find()->asArray()->all();
        //获取所有有奖券的游戏
        $FivepkPrizeTypeObj = new FivepkPrizeType();
        $FivepkPrizeTypeObjs = $FivepkPrizeTypeObj->find()->where('prize_name = "奖券"')->asArray()->all();
        $rs = array();
        foreach ($games as $game){
            foreach ($FivepkPrizeTypeObjs as $FivepkPrizeTypeObj){
                if($game['game_number'] == $FivepkPrizeTypeObj['game_type']){
                    //表是这个游戏有这个奖，就可以初始化这个游戏 的第一条数据
                    foreach ($DataRoomInfoListObjs as $roomObj){
                        if( $roomObj['game'] == $game['game_number']){
                            $rs[$game['game_number']]['gameName'] = $game['game_name'];
                            $rs[$game['game_number']]['roomLevel'][$roomObj['room_index']] = $roomObj['name'];
                        }
                    }
                }
            }
        }
        return $rs;
    }
}
