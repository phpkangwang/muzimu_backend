<?php

namespace backend\controllers\dls;

use backend\controllers\SendByMyController;
use backend\models\Account;
use backend\models\Factory;
use backend\models\remoteInterface\remoteInterface;
use backend\models\Tool;
use common\models\game\FivepkAccount;
use common\models\game\FivepkPlayerInfo;
use common\models\pay\CpayMenu;
use common\models\pay\CpayOrder;
use common\models\pay\platform\BolePay;
use common\models\pay\platform\CPay;
use common\models\pay\platform\DiorPay;
use common\models\pay\platform\GTPay;
use common\models\pay\platform\OreoPay;
use common\models\pay\platform\SFPay;
use common\models\pay\platform\SKYPay;
use common\models\pay\platform\ZXPay;
use common\models\pay\platform\ZYPay;
use Yii;
use backend\models\ErrorCode;
use backend\models\MyException;
use yii\web\Controller;

class PayController extends SendByMyController
{

    /**
     *   cp添加订单
     */
    public function actionCpayAddOrder()
    {
        try {
            $post = Yii::$app->request->post();
            if (!isset($post['orderNo']) || !isset($post['accountId']) || !isset($post['type']) || !isset($post['fee'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId           = $post['accountId'];
            $FivepkAccountObj    = new FivepkAccount();
            $FivepkPlayerInfoObj = new FivepkPlayerInfo();
            $FivepkAccountObj    = $FivepkAccountObj->findOne($accountId);
            if (empty($FivepkAccountObj)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_EXIST);
            }

            $FivepkPlayerInfoObj = $FivepkPlayerInfoObj->findOne($accountId);
            if (empty($FivepkPlayerInfoObj)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_EXIST);
            }

            $postData     = array(
                'order_no'     => $post['orderNo'],
                'cpay_menu_id' => $post['cpayMenuId'],
                'account_id'   => $post['accountId'],
                'name'         => $FivepkAccountObj->name,
                'nick_name'    => $FivepkPlayerInfoObj->nick_name,
                'seoid'        => $FivepkAccountObj->seoid,
                'type'         => $post['type'],
                'fee'          => $post['fee'],
                'status'       => 1,
                'create_time'  => time(),
            );
            $CpayOrderObj = new CpayOrder();
            $CpayOrderObj->add($postData);
            $rsData = array(
                'code'    => 200,
                'message' => ""
            );
            header('Content-type:application/json; charset=utf-8');
            echo json_encode($rsData);
            return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   cp订单改为支付状态
     */
    public function actionCpayIsPay()
    {
        try {
            $post = Yii::$app->request->post();
//            if (!isset($post['orderNo']) || !isset($post['tradeNo'])) {
//                throw new MyException(ErrorCode::ERROR_PARAM);
//            }

            Tool::checkParam(['order_no', 'trade_no', 'time', 'sign', 'fee'], $post);

            $CPay = new CPay();

            $CPay->payAcceptAccountObj = (object)['shop_id' => '0000000063', 'validate_key' => 'O5lxhJ2NsU'];

            if ($CPay->getSignOfCallBackUrl($post) != $post['sign']) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }


            $tradeNo      = $post['tradeNo'];
            $CpayOrderObj = new CpayOrder();
            $obj          = $CpayOrderObj->find()->where('order_no = :order_no', array(":order_no" => $post['orderNo']))->one();
            if (empty($obj)) {
                throw new MyException(ErrorCode::ERROR_OBJ);
            }

            //因为回调有很多次，只要又一次改正过来状态后面的直接跳过
            if ($obj->status != 2) {
                //获取手动操作的人
                $operateName = isset($post['operateName']) ? $post['operateName'] : "system";

                //首先状态改成支付成功，但是钻石未到账
                $obj->PayError($tradeNo);
                //获取cpayMenu对象
                $CpayMenuObj = CpayMenu::findOne($obj['cpay_menu_id']);
                //给用户加钱
                $postData = array(
                    'sendPopCode'  => $obj['seoid'],
                    'acceptUserId' => $obj->account_id,
                    'num'          => $CpayMenuObj->coin,
                    'operateName'  => $operateName,
                    'operatorType' => 2
                );
                Factory::RecordController()->UserDiamondUpdate($postData);
                $obj->isPay($tradeNo);
            }

            $rsData = array(
                'code'    => 200,
                'message' => ""
            );
            header('Content-type:application/json; charset=utf-8');
            echo json_encode($rsData);
            return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  高通支付 添加订单
     */
    public function actionGPayAddOrder()
    {
        $GTPay = new GTPay();
        $data  = $GTPay->addOrder($this->post);
        $this->setData($data);
        $this->sendJson();
    }


    /**
     *  高通支付 回调修改订单状态
     */
    public function actionCallBackUrl()
    {
        try {
            $GTPay  = new GTPay();
            $status = $GTPay->callBackUrl($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  高通支付 回调修改订单状态
     */
    public function actionHrefBackUrl()
    {
        try {
            $GTPay  = new GTPay();
            $status = $GTPay->hrefBackUrl($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  sf支付 添加订单
     */
    public function actionSPayAddOrder()
    {
        $SFPay = new SFPay();
        $data  = $SFPay->addOrder($this->post);
        $this->setData($data);
        $this->sendJson();
    }


    /**
     * sf支付 回调修改订单状态
     * @throws \yii\db\Exception
     */
    public function actionSCallBackUrl()
    {
        try {
            $SFPay  = new SFPay();
            $status = $SFPay->callBackUrl($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  sf支付 回调修改订单状态
     */
    public function actionSErrorBackUrl()
    {
        try {
            $SFPay  = new SFPay();
            $status = $SFPay->errorBackUrl($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 兆鑫支付 回调修改订单状态
     * @throws \yii\db\Exception
     */
    public function actionZXCallBackUrl()
    {
        try {
            $class  = new ZXPay();
            $status = $class->callBackUrl($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * cp支付 回调修改订单状态
     * @throws \yii\db\Exception
     */
    public function actionCPayCallBackUrl()
    {
        try {
            $class  = new CPay();
            $status = $class->callBackUrl($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * SKY支付 回调修改订单状态
     * @throws \yii\db\Exception
     */
    public function actionSKYPayCallBackUrl()
    {
        try {
            $class  = new SKYPay();
            $status = $class->callBackUrl($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * ZY支付 回调修改订单状态
     * @throws \yii\db\Exception
     */
    public function actionZYPayCallBackUrl()
    {
        try {
            $class  = new ZYPay();
            $status = $class->callBackUrl($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * dior支付 回调修改订单状态
     * @throws \yii\db\Exception
     */
    public function actionDiorPayCallBackUrl()
    {
        try {
            $class  = new DiorPay();
            $status = $class->callBackUrl($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * OreoPay 回调修改订单状态
     * @throws \yii\db\Exception
     */
    public function actionOreoPayCallBackUrl()
    {
        try {
            $class  = new OreoPay();
            $status = $class->callBackUrl($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * OreoPay 回调修改订单状态
     * @throws \yii\db\Exception
     */
    public function actionBolePayCallBackUrl()
    {
        try {
            $class  = new BolePay();
            $status = $class->callBackUrl($this->post);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


}

?>