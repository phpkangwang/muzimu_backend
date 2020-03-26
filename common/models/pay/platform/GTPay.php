<?php


namespace common\models\pay\platform;


use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;

class GTPay extends PayAbstract
{


    /**
     * 异步返回结果
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function callBackUrl($data)
    {

        Tool::myLog("GTCallBackUrl" . json_encode(['data' => $data], JSON_UNESCAPED_UNICODE), 'GTPay');


        Tool::checkParam(['partner', 'ordernumber', 'orderstatus', 'paymoney'], $data);

        try {

            $this->tradeNo = $data['sysnumber'];
            $this->orderNo = $data['ordernumber'];

            $PayOrderObj =& $this->getPayOrderObj($data);
            $this->getPayAcceptAccountObj($PayOrderObj->pay_accept_account_id);

            $sign = self::getSignOfCallBackUrl($data);

            if ($data['sign'] != $sign || $data['orderstatus'] != 1) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            return $this->upPayStatus($data);
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 同步返回结果
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function hrefBackUrl($data)
    {
        Tool::myLog("GTHrefBackUrl" . json_encode(['data' => $data], JSON_UNESCAPED_UNICODE), 'GTPay');
        Tool::checkParam(['partner', 'ordernumber', 'orderstatus', 'paymoney'], $data);
        try {

            $this->tradeNo = $data['sysnumber'];
            $this->orderNo = $data['ordernumber'];

            $PayOrderObj =& $this->getPayOrderObj($data);
            $this->getPayAcceptAccountObj($PayOrderObj->pay_accept_account_id);

            $sign = self::getSignOfHrefBackUrl($data);
            if ($data['sign'] != $sign || $data['orderstatus'] != 1) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            return $this->upPayStatus($data);
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }


    /**
     * 同步验参
     * @param $data
     * @return string
     */
    public function getSignOfHrefBackUrl($data)
    {
        $str = "partner={$data['partner']}&ordernumber={$data['ordernumber']}&orderstatus={$data['orderstatus']}&paymoney={$data['paymoney']}" . $this->payAcceptAccountObj->shop_key;
        return md5($str);//32位小写MD5签名值;
    }

    /**
     * 异步验参
     * @param $data
     * @return string
     */
    public function getSignOfCallBackUrl($data)
    {
        $str = "partner={$data['partner']}&ordernumber={$data['ordernumber']}&orderstatus={$data['orderstatus']}&paymoney={$data['paymoney']}" . $this->payAcceptAccountObj->shop_key;
        return md5($str);//32位小写MD5签名值;
    }


}