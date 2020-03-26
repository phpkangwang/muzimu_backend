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

class OreoPay extends PayAbstract
{

    /**
     * 异步返回结果
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function callBackUrl($data)
    {

        Tool::myLog("OreoPayCallBackUrl" . json_encode(['data' => $data], JSON_UNESCAPED_UNICODE), 'OreoPay');

        Tool::checkParam(['RtnCode', 'RtnMessage', 'MerTradeID', 'MerProductID', 'MerUserID', 'Number', 'Amount', 'PaymentDate', 'Sign'], $data);

        try {

            $this->tradeNo = $data['MerTradeID'];
            $this->orderNo = $data['MerTradeID'];

            $PayOrderObj =& $this->getPayOrderObj($data);
            $this->getPayAcceptAccountObj($PayOrderObj->pay_accept_account_id);
            //验证
            $sign = self::getSignOfCallBackUrl($data);
            if ($data['Sign'] != $sign || $data['RtnCode'] != 1) {
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
        $str = MD5("Amount={$data['Amount']}&MerProductID={$data['MerProductID']}&MerTradeID={$data['MerTradeID']}&MerUserID={$data['MerUserID']}&Number={$this->payAcceptAccountObj->shop_id}&PaymentDate={$data['PaymentDate']}&RtnCode={$data['RtnCode']}&RtnMessage={$data['RtnMessage']}&Key={$this->payAcceptAccountObj->shop_key}");
        return $str;//32位小写MD5签名值;
    }


}