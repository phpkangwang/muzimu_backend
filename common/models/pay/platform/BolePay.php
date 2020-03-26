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

class BolePay extends PayAbstract
{

    public $appSecret = '';
    private $appId = '';

    /**
     * 异步返回结果
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function callBackUrl($data)
    {

        Tool::myLog("BolePay" . json_encode(['data' => $data], JSON_UNESCAPED_UNICODE), 'BolePay');

        Tool::checkParam(['app_id', 'sign', 'sn', 'trade_no', 'money', 'pay_time'], $data);

        try {

            $this->tradeNo = $data['sn'];
            $this->orderNo = $data['trade_no'];

            $PayOrderObj =& $this->getPayOrderObj($data);
            $this->getPayAcceptAccountObj($PayOrderObj->pay_accept_account_id);

            $this->appId     = $this->payAcceptAccountObj->shop_id;
            $this->appSecret = $this->payAcceptAccountObj->shop_key;
            //验证
            $sign = self::getSignOfCallBackUrl($data);
            if (!$sign) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            return $this->upPayStatus($data);
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 校验回调请求签名
     */
    public function getSignOfCallBackUrl($data = null)
    {
        if (empty($data)) {
            $data = $_POST;
        }

        if (!isset($data['sign']) || strlen($data['sign']) != 32 ||
            !isset($data['app_id']) || $data['app_id'] != $this->appId) {
            return false;
        }

        $post_sign = $data['sign'];
        $safe_sign = $this->getSign($data);
        return $post_sign == $safe_sign;
    }

    /**
     * 生成签名
     */
    private function getSign($data)
    {
        ksort($data);
        reset($data);

        unset($data['sign']);

        $sign = '';
        foreach ($data as $key => $val) {
            $val = trim($val);
            if ($val === '') {
                continue;
            }
            $sign .= $key . '=' . $val . '&';
        }
        $sign .= 'app_secret=' . $this->appSecret;
        return md5($sign);
    }


}