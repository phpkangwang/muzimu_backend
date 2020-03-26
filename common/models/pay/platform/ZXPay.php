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

class ZXPay extends PayAbstract
{

    /**
     * 异步返回结果
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function callBackUrl($data)
    {

        Tool::myLog("ZXCallBackUrl" . json_encode(['data' => $data], JSON_UNESCAPED_UNICODE), 'ZXPay');

        Tool::checkParam(['userid', 'innerorderid', 'money', 'status', 'outorderid', 'sign'], $data);

        try {

            $this->orderNo = $data['innerorderid'];
            $this->tradeNo = $data['outorderid'];

            $PayOrderObj =& $this->getPayOrderObj($data);
            $this->getPayAcceptAccountObj($PayOrderObj->pay_accept_account_id);
            //验证
            $sign = self::getSignOfCallBackUrl($data);

            if ($sign == $data['sign'] && $data['status'] == '2') {
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

        $key = $this->payAcceptAccountObj->shop_key;


        $arr = $data;
        $arr = array_filter($arr);
        ksort($arr);
        $signStr = '';
        foreach ($arr as $k => $v) {
            if ($v) {
                $signStr .= $k . '=' . $v . '&';
            }

        }
        $orderSign = strtoupper(md5($signStr . "key=" . $key));


        return $orderSign;//32位小写MD5签名值;
    }

}