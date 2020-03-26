<?php
namespace common\models\game;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "fivepk_order".
 *
 * @property string $id
 * @property integer $pay_type
 * @property string $account_id
 * @property string $inner_order_id
 * @property string $platform_order_id
 * @property string $create_time
 * @property string $pay_time
 * @property string $goods_name
 * @property double $amount
 * @property double $recharge_money
 * @property integer $status
 * @property integer $score
 * @property integer $gift_score
 * @property string $comment
 * @property string $recharge_type
 */
class FivepkOrder extends \backend\models\BaseModel
{
    /**
     * 月卡配置
     */
    const MONTH_CARD_RECHARGE_ACTIVITY='MONTH_CARD_RECHARGE_ACTIVITY';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_order';
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
            [['pay_type', 'account_id', 'is_succ', 'score', 'gift_score'], 'integer'],
            [['account_id', 'inner_order_id', 'platform_order_id', 'create_time', 'pay_time', 'goods_name', 'amount', 'recharge_money', 'status', 'score', 'gift_score'], 'required'],
            [['create_time', 'pay_time'], 'safe'],
            [['amount', 'recharge_money'], 'number'],
            [['inner_order_id', 'platform_order_id'], 'string', 'max' => 100],
            [['goods_name', 'comment','recharge_type'], 'string', 'max' => 25],
            [['inner_order_id'], 'unique']
        ];
    }

    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(),['account_id'=>'account_id']);
    }

    public function getAccount()
    {
        return $this->hasOne(FivepkAccount::className(),['account_id'=>'account_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pay_type' => '1-苹果2-谷歌',
            'account_id' => '用户id',
            'inner_order_id' => '内部订单号',
            'platform_order_id' => '平台订单号',
            'create_time' => '创建时间',
            'pay_time' => '支付时间',
            'goods_name' => '商品名',
            'amount' => '商品数量',
            'recharge_money' => '充值金额',
            'status' => '是否成功0-失败1-成功',
            'score' => '充值分数',
            'gift_score' => '赠送分数',
            'comment' => '备注',
            'recharge_type' => '充值类型'
        ];
    }

    /**
     * 分页
     * @return array
     */
    public function page($pageNo, $pageSize, $where)
    {
        $tableName = self::tableName();
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        return self::find()->select("{$tableName}.*,fivepk_account.seoid as popCode")->joinWith('account')->joinWith('playerInfo')->where($where)->offset($offset)->orderBy('id desc')->limit($limit)->asArray()->all();
    }

    /**
     * 分页数量
     * @return array
     */
    public function pageCount( $where )
    {
        return self::find()->joinWith('account')->joinWith('playerInfo')->where($where)->count();
    }

    /**
     *  订单充值统计
     * @param $where
     * @param $whereSon
     * @param $pageNo
     * @param $pageSize
     * @return array
     * @throws \yii\db\Exception
     */
    public function statistic( $where, $whereSon ,$orderBy,$pageNo, $pageSize)
    {
        $tableName = self::tableName();
        $db = self::getDb();

        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;

        $sql = "select * from (select {$tableName}.*,fivepk_account.address,fivepk_account.seoid as popCode,sum(recharge_money) as reMonSum,sum(score) as scoreSum,sum(gift_score) as gScoreSum 
                 from {$tableName},fivepk_account
                 where  {$where}
                 group by {$tableName}.account_id
                 {$orderBy}
                 limit {$limit}
                 OFFSET {$offset}
                 )t
                 where  {$whereSon}";
        return $db->createCommand($sql)->queryAll();
    }


    /**
     * 订单充值统计
     * @param $where
     * @return array
     * @throws \yii\db\Exception
     */
    public function statisticSumPayType( $where )
    {
        $tableName = self::tableName();
        $db = self::getDb();

        $sql = "select {$tableName}.pay_type,sum(recharge_money) as reMonSum,sum(score) as scoreSum,sum(gift_score) as gScoreSum 
                 from {$tableName},fivepk_account
                 where  {$where}
                 group by {$tableName}.pay_type
                 ";
        return $db->createCommand($sql)->queryAll();
    }

    /**
     *  订单充值统计
     * @param $where
     * @param $whereSon
     * @return array
     */
    public function statisticCount( $where, $whereSon)
    {
        $tableName = self::tableName();
        $db = self::getDb();

        $sql = "select * from (select {$tableName}.*,sum(recharge_money) as reMonSum,sum(score) as scoreSum,sum(gift_score) as gScoreSum 
                 from {$tableName},fivepk_account
                 where  {$where}
                 group by {$tableName}.account_id
                 )t
                 where  {$whereSon}";
        $data = $db->createCommand($sql)->queryAll();
        return count($data);
    }

    public function headerDataCounts($where)
    {
        $tableName = self::tableName();
        $counts = self::findBySql("
            select 
            count({$tableName}.recharge_money) as count,
            recharge_money,
            fivepk_player_info.is_online
            from 
            {$tableName}
            left join fivepk_account on {$tableName}.account_id = fivepk_account.account_id
            left join fivepk_player_info on {$tableName}.account_id = fivepk_player_info.account_id
            where {$where}
            group by recharge_money
        ")->asArray()->all();

        $sums = self::findBySql("
            select 
            sum({$tableName}.recharge_money) as rechargeMoneySum,
            sum({$tableName}.score) as scoreSum,
            sum({$tableName}.gift_score) as giftScoreSum,
            fivepk_order.pay_type
            from 
            {$tableName}
            left join fivepk_account on {$tableName}.account_id = fivepk_account.account_id
            left join fivepk_player_info on {$tableName}.account_id = fivepk_player_info.account_id
            where {$where}
            GROUP BY fivepk_order.pay_type
        ")->asArray()->all();


        return ['counts'=>$counts,'sums'=>$sums];
    }

}
