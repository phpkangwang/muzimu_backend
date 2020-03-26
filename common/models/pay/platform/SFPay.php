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

class SFPay extends PayAbstract
{

    /**
     * 异步返回结果
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function callBackUrl($data)
    {

        Tool::myLog("SFCallBackUrl" . json_encode(['data' => $data], JSON_UNESCAPED_UNICODE), 'SFPay');

        Tool::checkParam(['RtnCode', 'MerTradeID', 'MerUserID', 'Amount', 'Validate'], $data);

        try {

            $this->tradeNo = $data['MerTradeID'];
            $this->orderNo = $data['MerTradeID'];

            $PayOrderObj =& $this->getPayOrderObj($data);
            $this->getPayAcceptAccountObj($PayOrderObj->pay_accept_account_id);
            //验证
            $sign = self::getSignOfCallBackUrl([
                'RtnCode' => $data['RtnCode'],
                'TradeID' => $data['MerTradeID'],
                'UserID'  => $data['MerUserID'],
                'Money'   => $data['Amount'],
            ]);
            if ($data['Validate'] != $sign || $data['RtnCode'] != 1) {
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

        $str = md5("ValidateKey={$this->payAcceptAccountObj->validate_key}&HashKey={$this->payAcceptAccountObj->shop_key}&RtnCode={$data['RtnCode']}&TradeID={$data['TradeID']}&UserID={$data['UserID']}&Money={$data['Money']}");
        return $str;//32位小写MD5签名值;
    }


    /**
     * 错误返回结果
     * @param $data
     * @return bool
     */
    public function errorBackUrl($data)
    {

        Tool::checkParam(['RtnCode', 'MerTradeID', 'MerUserID', 'Amount', 'Validate'], $data);

        try {

            $PayOrderObj =& $this->getPayOrderObj($data);

            $this->getPayAcceptAccountObj($PayOrderObj->pay_accept_account_id);

            $sign = self::getSignOfErrorBackUrl([
                'RtnCode' => $data['RtnCode'],
                'TradeID' => $data['MerTradeID'],
                'UserID'  => $data['MerUserID'],
                'Money'   => $data['Amount'],
            ]);

            Tool::myLog("GTHrefBackUrl" . json_encode(['data' => $data], JSON_UNESCAPED_UNICODE), 'GTPay');

            if ($data['Validate'] != $sign || $data['RtnCode'] != 1) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            return true;
//            return $this->upPayStatus($data);
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }


    /**
     * 错误返回验参
     * @param $data
     * @return string
     */
    public function getSignOfErrorBackUrl($data)
    {
        $str = md5("ValidateKey={$this->payAcceptAccountObj->validate_key}&HashKey={$this->payAcceptAccountObj->shop_key}&RtnCode={$data['RtnCode']}&TradeID={$data['TradeID']}&UserID={$data['UserID']}&Money={$data['Money']}");
        return ($str);//32位小写MD5签名值;
    }


}