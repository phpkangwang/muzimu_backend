<?php


namespace common\models\pay\platform;


use backend\models\ErrorCode;
use backend\models\Factory;
use backend\models\MyException;
use backend\models\redis\MyRedis;
use backend\models\Tool;
use Yii;
use common\models\pay\platform\PayAcceptAccount;

/*总后台只参与回调和修改钻石*/

abstract class PayAbstract
{

    public $time;
    public $PayOrderObj;//订单数据
    public $payAcceptAccountObj;//配置账户信息
    public $tradeNo = '';//第三方交易码
    public $orderNo = '';//自己生成的交易号
    const UP_PAY_STATUS_REDIS = 'upPayStatus';//回调状态队列


    public function __construct()
    {
        $this->time = Tool::getCurrentTimeInMilliseconds();
    }

    /**
     * 生成订单
     * @param $PayOrderData
     * @return bool
     */
    public function addOrder($PayOrderData)
    {
        $PayOrder               = new PayOrder();
        $PayOrderData['status'] = 1;
        $PayOrderData           = $PayOrder->add($PayOrderData);
        if (empty($PayOrderData)) {
            return false;
        }
        return $PayOrderData;
    }


    /**
     * 修改订单状态 并给用户加钻石
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function upPayStatus($data)
    {
//        varDump($data);
        try {
            $PayOrderObj = &$this->getPayOrderObj($data);
            if (empty($this->payAcceptAccountObj)) {
                $this->getPayAcceptAccountObj($PayOrderObj->pay_accept_account_id);
            }

            if (empty($PayOrderObj)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            }
            $tradeNo = $this->tradeNo;

            //获取手动操作的人
            $operateName = Tool::examineEmpty($data['operateName'], 'system');
            if (isset($PayOrderObj->status) && $PayOrderObj->status == 2) {
                return true;
            }

            if (isset($PayOrderObj->status) && ($PayOrderObj->status == 1 || $PayOrderObj->status == 4)) {

                //当线上支付才会走队列 如果是队列任务则直接不走redis
                if (!(isset($this->isQueue) && $this->isQueue == 1) && ($PayOrderObj->pay_menu_id != 1 && $PayOrderObj->status == 1)) {
                    $redis = new MyRedis();
                    $redis->writeCacheHash(self::UP_PAY_STATUS_REDIS, $tradeNo, ['order_no' => $this->orderNo, 'trade_no' => $this->tradeNo]);
                    return true;
                }

                //首先状态改成支付成功，但是钻石未到账
                $PayOrderObj->payError($tradeNo);

                //给用户加钱
                $postData = array(
                    'sendPopCode'  => $PayOrderObj->seoid,
                    'acceptUserId' => $PayOrderObj->account_id,
                    'num'          => $PayOrderObj->fee,//这里的比例可能会改变
                    'operateName'  => $operateName,
                    'operatorType' => 2
                );

                Factory::RecordController()->UserDiamondUpdate($postData);

                $tr    = PayAcceptAccount::getDb()->beginTransaction();
                $money = $PayOrderObj->fee;
                $PayOrderObj->isPay($tradeNo);
                $sql = "update pay_accept_account SET accept_money_times=accept_money_times+1,accept_money_sum=accept_money_sum+{$money} WHERE id='{$this->payAcceptAccountObj->id}';";
                Yii::$app->game_db->createCommand($sql)->execute();

                $tr->commit();
            }

            return true;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    //获取订单信息
    public function &getPayOrderObj($data)
    {
        if (!$this->PayOrderObj) {
            $PayOrder = new PayOrder();

            if (empty($this->orderNo) && isset($data['ordernumber'])) {
                //兼容GT
                $this->orderNo = $data['ordernumber'];
            }

            $this->PayOrderObj = $PayOrder->findOneByField('order_no', $this->orderNo);
        }
        return $this->PayOrderObj;
    }

    //获取账户信息
    public function &getPayAcceptAccountObj($id)
    {
        if (!$this->payAcceptAccountObj) {
            $this->payAcceptAccountObj = PayAcceptAccount::findOneByField('id', $id);
        }
        return $this->payAcceptAccountObj;
    }


    /**
     * 异步返回结果
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public abstract function callBackUrl($data);

    /**
     * 异步回调验参
     * @param $data
     * @return string
     */
    public abstract function getSignOfCallBackUrl($data);


}