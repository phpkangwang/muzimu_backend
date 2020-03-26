<?php


namespace common\models\pay\platform;


use backend\models\ErrorCode;
use backend\models\Factory;
use backend\models\MyException;
use backend\models\Tool;
use common\models\game\FivepkAccount;
use common\models\pay\platform\PayOrder;
use common\models\pay\platform\PayAcceptAccount;
use Yii;

class CPay extends PayAbstract
{

    /**
     * 异步返回结果
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function callBackUrl($data)
    {

        Tool::myLog("callBackUrl" . json_encode(['data' => $data], JSON_UNESCAPED_UNICODE), 'cPay');

        //$rsjson = '{"order_no":"5211559111924853","trade_no":"20190529200040011100740069209949","time":"20190529143919","sign":"cdc04fee512a56d5b9e67553e22abb92","fee":"0.01"}';

        Tool::checkParam(['order_no', 'trade_no', 'time', 'sign', 'fee'], $data);

        try {

            $this->orderNo = $data['order_no'];
            $this->tradeNo = $data['trade_no'];

            $PayOrderObj =& $this->getPayOrderObj($data);
            $this->getPayAcceptAccountObj($PayOrderObj->pay_accept_account_id);
            //验证
            $sign = self::getSignOfCallBackUrl($data);

            if ($sign == $data['sign']) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            return $this->upPayStatus($data);
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 异步验参
     * @param $data
     * @return string
     */
    public function getSignOfCallBackUrl($data)
    {
        $sign = md5("app_id={$this->payAcceptAccountObj->shop_id}&pay_key={$this->payAcceptAccountObj->validate_key}&trade_no={$data['trade_no']}&time={$data['time']}");
        return $sign;//32位小写MD5签名值;
    }

}