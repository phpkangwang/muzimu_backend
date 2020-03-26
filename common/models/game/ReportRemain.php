<?php

namespace common\models\game;

use Yii;

/**
 * This is the model class for table "ReportRemain".
 *
 * @property string $id
 * @property integer $dru
 * @property integer $active
 * @property double $second_day
 * @property double $third_day
 * @property double $seventh_day
 * @property double $fourteen_day
 * @property double $thirtieth_day
 * @property string $stat_time
 * @property string $add_time
 */
class ReportRemain extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'report_remain';
    }


    /**
     * @return object|\yii\db\Connection|null
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
            [['dru', 'active'], 'integer'],
            [['second_day', 'third_day', 'seventh_day', 'fourteen_day', 'thirtieth_day'], 'number'],
            [['stat_time', 'add_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'dru'           => 'Dru',
            'active'        => 'Active',
            'second_day'    => 'Second Day',
            'third_day'     => 'Third Day',
            'seventh_day'   => 'Seventh Day',
            'fourteen_day'  => 'Fourteen Day',
            'thirtieth_day' => 'Thirtieth Day',
            'stat_time'     => 'Stat Time',
            'add_time'      => 'Add Time',
            'channel'       => '市场名称'
        ];
    }

    /**
     * 分页
     * @return array
     */
    public function page($pageNo, $pageSize, $where)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo - 1) * $pageSize;
        return self::find()->where($where)->offset($offset)->orderBy('id desc')->limit($limit)->asArray()->all();
    }

    /**
     * 分页数量
     * @return array
     */
    public function pageCount($where)
    {
        return self::find()->where($where)->count();
    }

    /**
     * 每天凌晨定时生成
     * @param $channel string 渠道
     * @return int
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function timing($channel)
    {
        $oneDaySecond = 86400;
        $time         = time();
//        $channel='OPPO';
        $today      = date('Y-m-d', $time);
        $nowTime    = date('Y-m-d H:i:s', $time);
        $yesterday  = date('Y-m-d', $time - $oneDaySecond);
        $days_ago_2 = date('Y-m-d', $time - $oneDaySecond * 2);
        $days_ago_3 = date('Y-m-d', $time - $oneDaySecond * 3);
//        $days_ago_4 = date('Y-m-d', $time - $oneDaySecond * 4);
        $days_ago_6  = date('Y-m-d', $time - $oneDaySecond * 6);
        $days_ago_7  = date('Y-m-d', $time - $oneDaySecond * 7);
        $days_ago_13 = date('Y-m-d', $time - $oneDaySecond * 13);
        $days_ago_14 = date('Y-m-d', $time - $oneDaySecond * 14);
        $days_ago_29 = date('Y-m-d', $time - $oneDaySecond * 29);
        $days_ago_30 = date('Y-m-d', $time - $oneDaySecond * 30);
        $arr         = [
            //两日
            'second_day'    => [
                'today' => $days_ago_2,
                'end'   => $yesterday
            ],
            //三日
            'third_day'     => [
                'today' => $days_ago_3,
                'end'   => $days_ago_2
            ],
            //七日
            'seventh_day'   => [
                'today' => $days_ago_7,
                'end'   => $days_ago_6
            ],
            //十四日
            'fourteen_day'  => [
                'today' => $days_ago_14,
                'end'   => $days_ago_13
            ],
            //三十日
            'thirtieth_day' => [
                'today' => $days_ago_30,
                'end'   => $days_ago_29
            ],
        ];

        $tableName    = self::tableName();
        $whereChannel = '';
        if ($channel !== 'ALL') {
            $whereChannel = "AND channel='$channel'";
        }

//dru 新增人数 active 活跃人数
        $sql = "INSERT INTO $tableName ( dru,active, stat_time, add_time,channel )
SELECT sum(dru) as dru,sum(active) as active,'$yesterday','$nowTime','$channel' FROM (
(
SELECT
count( DISTINCT account_id ) as dru,
 0 as active
FROM
	fivepk_account ll
WHERE
	ll.last_login_time > '2000-01-01'
	$whereChannel
	AND ll.`create_date` BETWEEN '$yesterday' AND '$today'
)
union all
(
 SELECT
0 as dru,
count( DISTINCT account_id ) as active
FROM
	fivepk_account ll
WHERE
	ll.last_login_time > '2000-01-01'
	$whereChannel
	AND ll.`last_login_time` BETWEEN '$yesterday' AND '$today'
)
) t;";

        //7日留存 就是七天前创建并且今天登陆过除以七天前创建人数
        foreach ($arr as $key => $value) {

            $sql .= "
UPDATE $tableName
SET {$key} = (
SELECT concat(SUM(login_num),'/',SUM(create_num)) FROM
(
SELECT 0 as create_num,count(  DISTINCT account_id ) as login_num FROM fivepk_account
WHERE (create_date BETWEEN '{$value['today']}' AND '{$value['end']}')
AND  (last_login_time BETWEEN '$yesterday' AND '$today')
$whereChannel
UNION ALL
SELECT count( DISTINCT account_id ) as create_num,0 as login_num FROM fivepk_account
WHERE create_date BETWEEN '{$value['today']}' AND '{$value['end']}'
$whereChannel
) t
)
WHERE
stat_time = '{$value['today']}' and channel='$channel' ;
";
        }
        return self::getDb()->createCommand($sql)->execute();
    }

    //根据时间获取 当日的数据 $time='2019-09-18 00:00:00'
    public static function getOneDataForTime($time, $channel)
    {
        $data = self::find()->where([
            'stat_time' => $time,
            'channel'   => $channel
        ])->asArray()->one();

        return $data;
    }

    //根据时间获取 当日的活跃人数
    public static function getOneActiveDruForTime($time, $channel = 'ALL')
    {
        $data = self::getOneDataForTime($time, $channel);

        $return = [
            'active' => 0,
            'dru'    => 0,
        ];

        if (isset($data['active'])) {
            $return['active'] = $data['active'];
            $return['dru']    = $data['dru'];
        }

        return $return;
    }


}
