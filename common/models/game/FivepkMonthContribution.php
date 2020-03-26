<?php

namespace common\models\game;

use Yii;

/**
 * This is the model class for table "fivepk_month_contribution".
 *
 * @property integer $id
 * @property integer $account_id
 * @property string $record_time
 * @property integer $m1
 * @property integer $m2
 * @property integer $m3
 * @property integer $m4
 * @property integer $m5
 * @property integer $m6
 * @property integer $m7
 * @property integer $m8
 * @property integer $m9
 * @property integer $m10
 * @property integer $m11
 * @property integer $m12
 */
class FivepkMonthContribution extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_month_contribution';
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
            [['account_id', 'm1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7', 'm8', 'm9', 'm10', 'm11', 'm12'], 'integer'],
            [['record_time'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => '自增id',
            'account_id'  => '玩家id',
            'record_time' => '时间',
            'm1'          => '一月',
            'm2'          => '二月',
            'm3'          => '三月',
            'm4'          => '四月',
            'm5'          => '五月',
            'm6'          => '六月',
            'm7'          => '七月',
            'm8'          => '八月',
            'm9'          => '九月',
            'm10'         => '十月',
            'm11'         => '十一月',
            'm12'         => '十二月',
        ];
    }

    public function getFivepkAccount()
    {
        return $this->hasOne(FivepkAccount::className(), ['account_id' => 'account_id'])->select($this->FivepkAccount->BaseColumn);;
    }

    public function getFivepkPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(), ['account_id' => 'account_id'])->select($this->FivepkPlayerInfo->BaseColumn);;
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

    //月贡献报表
    public static function monthContribution($date)
    {
        $time = strtotime($date);
        $year = date('Y', $time);

        $sql = "INSERT INTO fivepk_month_contribution ( account_id,record_time )
	SELECT account_id,'{$year}' FROM(
	SELECT fdc.account_id,fmc.m1 from fivepk_day_contribution fdc
	LEFT JOIN fivepk_month_contribution fmc on fmc.account_id=fdc.account_id
	WHERE
	fdc.record_time ='{$date}'
	HAVING m1 is NULL
) t;

UPDATE 
fivepk_month_contribution fmc
INNER JOIN fivepk_day_contribution fdc on  fmc.account_id=fdc.account_id
and fdc.record_time = '{$date}' AND fmc.record_time='{$year}'
SET fmc.m10=
(
fdc.d1 + fdc.d2 + fdc.d3 + fdc.d4 + fdc.d5 + fdc.d6 + fdc.d7 + fdc.d8 + fdc.d9 + fdc.d10 + fdc.d11 + fdc.d12 + fdc.d13 + fdc.d14 + fdc.d15 + fdc.d16 + fdc.d17 + fdc.d18 + fdc.d19 + 
fdc.d20 + fdc.d21 + fdc.d22 + fdc.d23 + fdc.d24 + fdc.d25 + fdc.d26 + fdc.d27 + fdc.d28 + fdc.d29 + fdc.d30 + fdc.d31
)
;";


//        varDump($sql);
        return self::getDb()->createCommand($sql)->execute();

    }

}
