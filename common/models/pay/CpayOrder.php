<?php
namespace common\models\pay;

use backend\models\BaseModel;
use backend\models\Tool;
use Yii;

class CpayOrder extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cpay_order';
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
            'order_no'   => '订单编号',
            'account_id' => '用户ID',
            'name'       => '账号',
            'nick_name'  => '昵称',
            'pop_code'   => '推广码',
            'type'       => '1微信 2支付宝',
            'fee'        => '充值金额',
            'status'     => '1未付款 2-已付款 3-退款',
            'update_time'=> '创建时间',
            'create_time'=> '修改时间',
        ];
    }

    public function findByOrderNo($orderNo)
    {
        return self::find()->where('order_no = :order_no',array(':order_no'=>$orderNo))->asArray()->one();
    }

    /**
     * 分页
     * @return array
     */
    public function page($pageNo, $pageSize, $where)
    {
        $rs = Tool::page($pageNo,$pageSize);
        $limit  = $rs['limit'];
        $offset = $rs['offset'];
        return  self::find()->where($where)->orderBy('id desc')->offset($offset)->limit($limit)->asArray()->all();
    }

    /**
     *   订单充值数量统计
     */
    public function countNum($where)
    {
        $objs = self::find()->where($where)->asArray()->all();
        $data = array();
        $sumMoney = 0;//总充值金额
        $map=[1=>'微信',2=>'支付宝'];
        foreach ($objs as $val){
            $sumMoney += $val['fee'];
            $key=$map[$val['type']].$val['fee'];
            $data[$key] = isset( $data[$key] ) ? $data[$key] : 0;
            $data[$key] += 1;
        }
        //排个序
        ksort($data);
        $data['总金额'] = $sumMoney;
        return $data;
    }

    /**
     * 订单改为已支付状态
     * @$tradeNo 第三方交易码
     * @return bool
     */
    public function isPay($tradeNo)
    {
        try{
            $this->status = 2;
            $this->trade_no = $tradeNo;
            $this->update_time = time();
            if( $this->validate() && $this->save() )
            {
                return true;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 订单改为已支付失败状态
     * @$tradeNo 第三方交易码
     * @return bool
     */
    public function PayError($tradeNo)
    {
        try{
            $this->status = 4;
            $this->trade_no = $tradeNo;
            $this->update_time = time();
            if( $this->validate() && $this->save() )
            {
                return true;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }
 
}
