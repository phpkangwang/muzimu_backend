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

class ZYPay extends PayAbstract
{

    /**
     * 异步返回结果
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function callBackUrl($data)
    {

        Tool::myLog("callBackUrl" . json_encode(['data' => $data], JSON_UNESCAPED_UNICODE), 'ZYPay');

        //$rsjson = '{"order_no":"5211559111924853","trade_no":"20190529200040011100740069209949","time":"20190529143919","sign":"cdc04fee512a56d5b9e67553e22abb92","fee":"0.01"}';

        Tool::checkParam(['callbacks', 'appid', 'pay_type', 'success_url', 'error_url', 'out_trade_no', 'amount', 'sign', 'amount_true', 'out_uid'], $data);

        try {

            $this->orderNo = $data['out_trade_no'];
            $this->tradeNo = $data['out_trade_no'];

            if ($data['callbacks'] != 'CODE_SUCCESS') {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $PayOrderObj =& $this->getPayOrderObj($data);
            $this->getPayAcceptAccountObj($PayOrderObj->pay_accept_account_id);
            //验证
            $sign = self::getSignOfCallBackUrl($data);

            if ($sign != $data['sign']) {
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
        unset($data['sign']);
//        $sign = md5("app_id={$this->payAcceptAccountObj->shop_id}&pay_key={$this->payAcceptAccountObj->validate_key}&trade_no={$data['trade_no']}&time={$data['time']}");
        $sign = $this->makeSign($data);
        return $sign;//32位小写MD5签名值;
    }

    /**
     * @param array $data 参与签名的数据
     * @return string
     * @author LvGang
     * @Note 生成签名
     */
    public function makeSign($data)
    {
        // 去空
        $data = array_filter($data);
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string_a = http_build_query($data);
        $string_a = urldecode($string_a);
        //签名步骤二：在string后加入KEY
        $string_sign_temp = $string_a . "&key=" . $this->payAcceptAccountObj->shop_key;
        //签名步骤三：MD5加密
        $sign = md5($string_sign_temp);
        // 签名步骤四：所有字符转为大写
        $result = strtoupper($sign);
        return $result;
    }

}