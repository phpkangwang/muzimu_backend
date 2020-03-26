<?php

namespace common\models\game;

use backend\models\OldPlayerSwitch;
use Yii;

/**
 * This is the model class for table "fivepk_day_contribution".
 *
 * @property integer $id
 * @property integer $account_id
 * @property string $record_time
 * @property integer $d1
 * @property integer $d2
 * @property integer $d3
 * @property integer $d4
 * @property integer $d5
 * @property integer $d6
 * @property integer $d7
 * @property integer $d8
 * @property integer $d9
 * @property integer $d10
 * @property integer $d11
 * @property integer $d12
 * @property integer $d13
 * @property integer $d14
 * @property integer $d15
 * @property integer $d16
 * @property integer $d17
 * @property integer $d18
 * @property integer $d19
 * @property integer $d20
 * @property integer $d21
 * @property integer $d22
 * @property integer $d23
 * @property integer $d24
 * @property integer $d25
 * @property integer $d26
 * @property integer $d27
 * @property integer $d28
 * @property integer $d29
 * @property integer $d30
 * @property integer $d31
 */
class FivepkDayContribution extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_day_contribution';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
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
            [['account_id', 'd1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd7', 'd8', 'd9', 'd10', 'd11', 'd12', 'd13', 'd14', 'd15', 'd16', 'd17', 'd18', 'd19', 'd20', 'd21', 'd22', 'd23', 'd24', 'd25', 'd26', 'd27', 'd28', 'd29', 'd30', 'd31'], 'integer'],
            [['record_time'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => '自增ID',
            'account_id'  => '用户id',
            'record_time' => '月份',
            'd1'          => '1',
            'd2'          => '2',
            'd3'          => '3',
            'd4'          => '4',
            'd5'          => '5',
            'd6'          => '6',
            'd7'          => '7',
            'd8'          => '8',
            'd9'          => '9',
            'd10'         => '10',
            'd11'         => '11',
            'd12'         => '12',
            'd13'         => '13',
            'd14'         => '14',
            'd15'         => '15',
            'd16'         => '16',
            'd17'         => '17',
            'd18'         => '18',
            'd19'         => '19',
            'd20'         => '20',
            'd21'         => '21',
            'd22'         => '22',
            'd23'         => '23',
            'd24'         => '24',
            'd25'         => '25',
            'd26'         => '26',
            'd27'         => '27',
            'd28'         => '28',
            'd29'         => '29',
            'd30'         => '30',
            'd31'         => '31',
        ];
    }

    public function getFivepkAccount()
    {
        return $this->hasOne(FivepkAccount::className(), ['account_id' => 'account_id'])->select($this->FivepkAccount->BaseColumn);
    }

    public function getFivepkPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(), ['account_id' => 'account_id']);
    }

    /**
     * 获取老玩家开关时间
     * @param $account_id
     * @return bool|string
     */
    public function getSwitchTime($account_id)
    {

        $conditions = ['account_id' => $account_id, 'is_player_switch' => 1];
        $games      = $this->DataGameListInfo->getOpenGame();
        foreach ($games as $key => $val) {
            $conditions['game_type_id'][] = $val['game_number'];
        }
        $result = OldPlayerSwitch::resultOne($conditions);
        $data   = '';
        if (!empty($result)) {
            $data = date('m-d', $result->open_time);
        }
        return $data;
    }

    /**
     * 分页获取数据
     * @param $where
     * @param $pageNo
     * @param $pageSize
     * @return array
     */
    public function Page($where, $orderBy, $pageNo, $pageSize)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo - 1) * $pageSize;

        $data = self::find()
            ->joinWith('fivepkAccount')
            ->joinWith('fivepkPlayerInfo')
            ->where($where)
            ->orderBy("$orderBy")
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all();

        return $data;
    }

    /**
     * 分页获取数据  数据总条数
     * @param $where
     * @return int
     */
    public function Count($where)
    {
        $count = self::find()
            ->joinWith('fivepkAccount')
            ->joinWith('fivepkPlayerInfo')
            ->where($where)
            ->count();
        return $count;
    }

    //日贡献报表
    public static function dayContribution($date)
    {
        //插入fivepk_day_contribution存在fivepk_day_contribution不存在的account_id
        //修改fivepk_day_contribution当日存在的数据


        $time  = strtotime($date);
        $month = date('Y-m', $time);
        $day   = date('d', $time);
        $day2  = intval($day);

        $sql = "INSERT INTO fivepk_day_contribution ( account_id,record_time )
SELECT account_id,'{$month}' FROM(
SELECT bcd.account_id,fdc.d1 from backend_contribution_day bcd
LEFT JOIN fivepk_day_contribution fdc on fdc.account_id=bcd.account_id and fdc.record_time='{$month}'
WHERE
bcd.create_time BETWEEN '{$month}-01' AND '{$month}-31' AND bcd.num!=0
GROUP BY bcd.account_id
HAVING d1 is NULL
) t;

UPDATE 
fivepk_day_contribution fdc
INNER JOIN backend_contribution_day acd on  fdc.account_id=acd.account_id
and acd.create_time='{$month}-{$day}' AND acd.num!=0 AND fdc.record_time='{$month}'
SET fdc.d{$day2} =acd.num;";


        return self::getDb()->createCommand($sql)->execute();

    }


}
