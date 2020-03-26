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

class DiorPay extends PayAbstract
{

    /**
     * 异步返回结果
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function callBackUrl($data)
    {

        Tool::myLog("callBackUrl" . json_encode(['data' => $data], JSON_UNESCAPED_UNICODE), 'DiorPay');

        Tool::checkParam(['MerchantCode', 'OrderId', 'OrderDate', 'Amount', 'OutTradeNo', 'BankCode', 'Time', 'Remark', 'Status', 'Sign'], $data);

        try {

            $this->orderNo = $data['OrderId'];
            $this->tradeNo = $data['OutTradeNo'];

            if ($data['Status'] != '1') {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $PayOrderObj =& $this->getPayOrderObj($data);
            $this->getPayAcceptAccountObj($PayOrderObj->pay_accept_account_id);
            //验证
            $sign = self::getSignOfCallBackUrl($data);

            if ($sign != $data['Sign']) {
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
        $sign="Amount={$data['Amount']}&BankCode={$data['BankCode']}&MerchantCode={$data['MerchantCode']}&OrderDate={$data['OrderDate']}&OrderId={$data['OrderId']}&OutTradeNo={$data['OutTradeNo']}&Status={$data['Status']}&Time={$data['Time']}&Key={$this->payAcceptAccountObj->shop_key}";
        return md5($sign);//32位小写MD5签名值;
    }


}