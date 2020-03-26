<?php


namespace backend\controllers\fivepk;

use backend\models\Factory;
use backend\models\redis\MyRedis;
use backend\models\Tool;
use common\services\ToolService;
use Yii;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;

/**
 * 商品配置
 * Class Goods
 * @package backend\controllers
 */
class GoodsController extends MyController
{

    /**
     *  商品配置
     */
    public function actionGetPay()
    {
        try {
            $class = new \common\models\pay\CpayMenu();

            $potion = [
                'order' => 'status asc,sort asc'
            ];

            //这段是PDO where
            {
                $pdo   = [];
                $where = " type in(1,2) ";

                $field = 'status';
                if (!Tool::isIssetEmpty($this->get[$field])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where          .= "$field =:$field";
                    $pdo[":$field"] = ($this->get[$field]);
                }

                $field = 'type';
                if (!Tool::isIssetEmpty($this->get[$field])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where          .= "$field =:$field";
                    $pdo[":$field"] = ($this->get[$field]);
                }

                if (!empty($where)) {
                    $potion['where'] = $where;
                    $potion['pdo']   = $pdo;
                }
            }

            $data = $class->pageList(
                Tool::examineEmpty($this->get['pageNo'], 1)
                , Tool::examineEmpty($this->get['pageSize'], 999)
                , $potion
            );

            $data = $this->Tool->clearFloatZero($data);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  商品配置修改
     */
    public function actionSetPay()
    {
        try {
            if (
                !isset($this->post['id'])//
                || !isset($this->post['type'])//1-微信2-支付宝
                || !isset($this->post['coin'])//钻石数
                || !isset($this->post['cost'])//消耗
//                || !isset($this->post['sort'])//排序
//                || !isset($this->post['status'])//状态 1 可用 2不可用
                || !is_numeric($this->post['cost'])
            ) {
                $this->setMessage(ErrorCode::ERROR_PARAM);
                $this->sendJson();
            }
            $CpayMenu = new \common\models\pay\CpayMenu();
            $data     = array(
                'id'   => intval($this->post['id']),
                'type' => intval($this->post['type']),
                'coin' => intval($this->post['coin']),
                'cost' => ($this->post['cost']),
            );
            if (isset($this->post['sort'])) {
                $data['sort'] = intval($this->post['sort']);
            }
            if (isset($this->post['status'])) {
                $data['status'] = intval($this->post['status']);
            }
            if (empty($data['id'])) {
                unset($data['id']);
            } else {
                $CpayMenu = $CpayMenu->findBase($data['id'], true);
            }

            $return = $CpayMenu->add($data);

            if (empty($return)) {
                $this->setMessage(ErrorCode::ERROR_SYSTEM);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  商品全部状态设置
     */
    public function actionSetAllStatus()
    {
        try {
            $data['status'] = intval(Tool::examineEmpty($this->post['status'], 0));
            $type           = intval(Tool::examineEmpty($this->post['type'], 0));
            if (!in_array($data['status'], [1, 2]) || !in_array($type, [0, 1, 2])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $CpayMenu = new \common\models\pay\CpayMenu();
            $where    = '';
            if (!empty($type)) {
                $where = ['type' => $type];
            }
            $return = $CpayMenu::updateAll($data, $where);
            if (empty($return)) {
                throw new MyException(ErrorCode::ERROR_DATA_NOT_UP);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  获取转出列表
     * @return mixed
     */
    public function actionGetPayRecordList()
    {

        try {
            $class = new \common\models\pay\CpayRecord();

            $potion = [
                'order' => 'status asc,id desc'
//                'select'=>"update_time,create_time,account_id,nick_name,operator_id,bank_number,bank_type,name,seoid"
            ];

            //这段是PDO where
            {
                $pdo = [];

                $popCodeArr   = $this->Account->findAllSonPopCode($this->loginId);
                $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";
                $where        = " status in(1,3,4) and seoid in($popCodeStrIn) ";


                $field = 'nick_name';
                if (!Tool::isIssetEmpty($this->get[$field])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where          .= "$field =:$field";
                    $pdo[":$field"] = ($this->get[$field]);
                }

                $field = 'account_id';
                if (!Tool::isIssetEmpty($this->get["$field"])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where          .= "$field like :$field";
                    $pdo[":$field"] = "%{$this->get["$field"]}%";
                }

                $stime = strtotime(Tool::examineEmpty($this->get['stime'], 0));
                $etime = strtotime(Tool::examineEmpty($this->get['etime'], 0));

                $potion['obj'] = function (&$obj) use ($stime, $etime) {
                    if ($etime > 0 && $stime > 0 && $stime <= $etime) {
                        $obj->andFilterWhere(['between', 'create_time', $stime * 1000, ($etime + 86399) * 1000]);
                    }
                };

                $field = 'name';
                if (!Tool::isIssetEmpty($this->get["$field"])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where          .= "$field like :$field";
                    $pdo[":$field"] = "%{$this->get["$field"]}%";
                }

                $field = 'seoid';
                if (!Tool::isIssetEmpty($this->get[$field])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where          .= "$field =:$field";
                    $pdo[":$field"] = ($this->get[$field]);
                }

                $field = 'status';
                if (!Tool::isIssetEmpty($this->get[$field])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where          .= "$field =:$field";
                    $pdo[":$field"] = intval($this->get[$field]);
                }


                if (!empty($where)) {
                    $potion['where'] = $where;
                    $potion['pdo']   = $pdo;
                }
            }

            $data           = $class->pageList(
                Tool::examineEmpty($this->get['pageNo'], 1)
                , Tool::examineEmpty($this->get['pageSize'], 8)
                , $potion
            );
            $accountIdArr   = array_column($data, 'operator_id');
            $accountObjs    = $this->Account->finds($accountIdArr);
            $newAccountObjs = array();
            foreach ($accountObjs as $val) {
                $newAccountObjs[$val['id']] = $val;
            }
//            $money_pre=0;
//            $AccountClass =new \backend\models\Account();
//            $accountList=$AccountClass::find()->where("pop_code in($popCodeStrIn)")->select('money_pre,pop_code')->indexBy('pop_code')->asArray()->all();
//            $accountList['XO']['money_pre']=100;
//            varDump($accountList);

            $GlobalConfig = new \common\models\GlobalConfig();
            $value        = $GlobalConfig->getDataInType($GlobalConfig::PART_TYPE, 'value');
            $money_pre    = Tool::examineEmpty($value['value'], 0);

            $accountIdArr = array_unique(array_column($data, 'account_id'));
            $bankList     = $class::find()->select('account_id,nick_name as bank_name,bank_number,bank_type')->indexBy('account_id')->where(array('in', 'account_id', $accountIdArr))->andWhere(['=', 'status', '100'])->asArray()->all();

            foreach ($data as $key => &$val) {
                $val['update_time'] = date("Y-m-d H:i:s", $val['update_time'] / 1000);
                $val['create_time'] = date("Y-m-d H:i:s", $val['create_time'] / 1000);
//                $money_pre=98;
                $val['withdraw_before_money'] = ($val['withdraw'] / 100) * $money_pre;
                $val['operator_id']           = isset($newAccountObjs[$val['operator_id']]) ? $newAccountObjs[$val['operator_id']]['name'] : "";
                $val['name']                  = Factory::Tool()->hideName($val['name']);
                if (isset($bankList[$val['account_id']])) {
                    $val['bank_name']   = $bankList[$val['account_id']]['bank_name'];
                    $val['bank_number'] = $bankList[$val['account_id']]['bank_number'];
                    $val['bank_type']   = $bankList[$val['account_id']]['bank_type'];
                } else {
                    $val['bank_name']   = '';
                    $val['bank_number'] = '';
                    $val['bank_type']   = '';
                }

            }

            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  转出列表修改
     */
    public function actionSetRecord()
    {
        try {
            if (
            !isset($this->post['id'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $CpayRecord = new \common\models\pay\CpayRecord();
            $return     = $CpayRecord->updateStatus(intval($this->post['id']), $this->loginInfo);
            if (empty($return)) {
                throw new MyException(ErrorCode::ERROR_SYSTEM);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 转出列表返还
     */
    public function actionRecordReturn()
    {
        try {
            if (
            !isset($this->post['id'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $CpayRecord = new \common\models\pay\CpayRecord();

            $obj = $CpayRecord->findOneByField('id', intval($this->post['id']), true);

            $return = $obj->returnUser($this->loginInfo['name'], $this->loginId);

            if (!$return) {
                throw new MyException(ErrorCode::ERROR_SYSTEM);
            }

            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  订单列表
     */
    public function actionGetOrderList()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize']) || !isset($this->get['stime']) || !isset($this->get['etime'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $pageNo    = $this->get['pageNo'];
            $pageSize  = $this->get['pageSize'];
            $stime     = $this->get['stime'];
            $etime     = $this->get['etime'] . " 23:59:59";
            $orderNo   = isset($this->get['orderNo']) ? $this->get['orderNo'] : "";
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $name      = isset($this->get['name']) ? $this->get['name'] : "";
            $popCode   = isset($this->get['popCode']) ? $this->get['popCode'] : "";
            $status    = isset($this->get['status']) ? $this->get['status'] : "";
            $type      = isset($this->get['type']) ? $this->get['type'] : "";

            $popCodeArr   = $this->Account->findAllSonPopCode($this->loginId);
            $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";

            $where = " seoid in ({$popCodeStrIn})";
            if (!empty($orderNo)) {
                $where .= " and order_no = '{$orderNo}'";
            }
            if (!empty($accountId)) {
                $where .= " and account_id = '{$accountId}'";
            }
            if (!empty($name)) {
                $where .= " and name like '%{$name}%'";
            }
            if (!empty($popCode)) {
                $where .= " and seoid = '{$popCode}'";
            }
            if (!empty($status)) {
                $where .= " and status = '{$status}'";
            }
            if (!empty($type)) {
                $where .= " and type = '{$type}'";
            }
            if (!empty($stime) && !empty($etime)) {
                $stime = strtotime($stime);
                $etime = strtotime($etime);
                $where .= " and create_time between '{$stime}' and '{$etime}'";
            }

            $data = $this->CpayOrder->page($pageNo, $pageSize, $where);

            foreach ($data as $key => $val) {
                $data[$key]['name']        = Factory::Tool()->hideName($val['name']);
                $data[$key]['create_time'] = date("Y-m-d H:i:s", $val['create_time']);
                $data[$key]['update_time'] = date("Y-m-d H:i:s", $val['update_time']);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   订单统计
     */
    public function actionGetOrderCount()
    {
        try {
            if (!isset($this->get['stime']) || !isset($this->get['etime'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $stime     = $this->get['stime'];
            $etime     = $this->get['etime'] . " 23:59:59";
            $orderNo   = isset($this->get['orderNo']) ? $this->get['orderNo'] : "";
            $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $name      = isset($this->get['name']) ? $this->get['name'] : "";
            $popCode   = isset($this->get['popCode']) ? $this->get['popCode'] : "";
            $status    = isset($this->get['status']) ? $this->get['status'] : "";
            $type      = isset($this->get['type']) ? $this->get['type'] : "";

            $popCodeArr   = $this->Account->findAllSonPopCode($this->loginId);
            $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";

            //只查已付款的
            $where = " seoid in ({$popCodeStrIn}) and status = 2";
            if (!empty($orderNo)) {
                $where .= " and order_no = '{$orderNo}'";
            }
            if (!empty($accountId)) {
                $where .= " and account_id = '{$accountId}'";
            }
            if (!empty($name)) {
                $where .= " and name like '%{$name}%'";
            }
            if (!empty($popCode)) {
                $where .= " and seoid = '{$popCode}'";
            }
            if (!empty($status)) {
                $where .= " and status = '{$status}'";
            }
            if (!empty($type)) {
                $where .= " and type = '{$type}'";
            }
            if (!empty($stime) && !empty($etime)) {
                $stime = strtotime($stime);
                $etime = strtotime($etime);
                $where .= " and create_time between '{$stime}' and '{$etime}'";
            }
            $data = $this->CpayOrder->countNum($where);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  获取绑定银行卡的列表
     * @return mixed
     */
        public function actionGetBankList()
    {

        try {
            $class = new \common\models\pay\CpayRecord();


            if (isset($this->get['bankName'])) {
                $this->get['nick_name'] = $this->get['bankName'];
            }

            $potion = [
                'order'    => 'id desc',
                'select'   => "cpay_record.name,cpay_record.id,cpay_record.update_time,cpay_record.create_time,cpay_record.account_id,cpay_record.nick_name as bank_name,cpay_record.operator_id,cpay_record.bank_number,cpay_record.bank_type,cpay_record.seoid,fivepk_player_info.nick_name as nick_name",
                'leftJoin' => ['table' => 'fivepk_player_info', 'on' => 'cpay_record.account_id=fivepk_player_info.account_id']
            ];

            if (isset($this->get['phoneNumber'])) {
                $potion['select'] .= ',cpay_record.phone_number';
            }

            //这段是PDO where
            {
                $pdo = [];

                $popCodeArr   = $this->Account->findAllSonPopCode($this->loginId);
                $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";

                $where = " cpay_record.status=100 and cpay_record.seoid in($popCodeStrIn) ";

                if (!Tool::isIssetEmpty($this->get['nick_name'])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where             .= "cpay_record.nick_name like :nick_name";
                    $pdo[':nick_name'] = "%{$this->get['nick_name']}%";
                }

                $field = 'seoid';
                if (!Tool::isIssetEmpty($this->get[$field])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where          .= "cpay_record.$field =:$field";
                    $pdo[":$field"] = ($this->get[$field]);
                }

                $field = 'name';
                if (!Tool::isIssetEmpty($this->get["$field"])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where          .= "cpay_record.$field like :$field";
                    $pdo[":$field"] = "%{$this->get["$field"]}%";
                }


                if (!Tool::isIssetEmpty($this->get['account_id'])) {
                    if (!empty($where)) {
                        $where .= ' and ';
                    }
                    $where              .= "cpay_record.account_id =:account_id";
                    $pdo[':account_id'] = intval($this->get['account_id']);
                }

                if (!empty($where)) {
                    $potion['where'] = $where;
                    $potion['pdo']   = $pdo;
                }
            }


            $data = $class->pageList(
                Tool::examineEmpty($this->get['pageNo'], 1)
                , Tool::examineEmpty($this->get['pageSize'], 8)
                , $potion
            );

            $accountIdArr   = array_column($data, 'operator_id');
            $accountObjs    = $this->Account->finds($accountIdArr);
            $newAccountObjs = array();
            foreach ($accountObjs as $val) {
                $newAccountObjs[$val['id']] = $val;
            }
            foreach ($data as $key => &$val) {
                $val['update_time'] = empty($val['update_time']) ? '' : date("Y-m-d H:i:s", $val['update_time'] / 1000);
                $val['create_time'] = date("Y-m-d H:i:s", $val['create_time'] / 1000);
                $val['operator_id'] = isset($newAccountObjs[$val['operator_id']]) ? $newAccountObjs[$val['operator_id']]['name'] : "";
                $val['name']        = Factory::Tool()->hideName($val['name']);
            }

            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  获取绑定银行卡类型
     * @return mixed
     */
    public function actionGetBankTypeList()
    {
        $data = array(
            '中国工商银行',
            '中国建设银行',
            '中国农业银行',
            '中国银行',
            '交通银行',
            '邮储银行',
            '兴业银行',
            '招商银行',
            '中信银行',
            '民生银行',
            '浦发银行',
            '光大银行',
            '平安银行',
            '华夏银行',
            '北京银行',
            '广发银行',
            '上海银行',
            '江苏银行',
            '浙商银行',
            '南京银行',
            '其他银行',

        );
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *  绑定银行卡修改
     */
    public function actionSetBank()
    {
        try {
            if (
            !isset($this->post['accountId'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $data = array(
//                'update_time' => time() * 1000,
//                'operator_id' => $this->loginId,
                'accountId'  => intval($this->post['accountId']),
                'bankNumber' => Tool::examineEmpty($this->post['bankNumber']),
                'bankType'   => Tool::examineEmpty($this->post['bankType']),
                'nickName'   => Tool::examineEmpty($this->post['nickName']),
            );
//            $bankList = $this->getBankList();

            if (empty($data['bankNumber'])
                || empty($data['bankType'])
                || empty($data['nickName'])
//                || in_array($data['bank_type'], $bankList)
            ) {

                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            if (Tool::examineEmpty($this->post['phoneNumber'])) {
                $data['phoneNumber'] = $this->post['phoneNumber'];
            }


            $intfaceClass = new \backend\models\remoteInterface\remoteInterface();
            $retrun       = $intfaceClass->cpayChangeBank($data);

            $class  = new \common\models\pay\CpayRecord();
            $obj    = $class::find()->where("account_id={$data['accountId']} and  status=100")->one();
            $return = $obj->add(['operator_id' => $this->loginId]);
//            if (empty($return)) {
//                $this->setMessage(ErrorCode::ERROR_SYSTEM);
//            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 查看分成
     */
    public function actionGetPart()
    {
        try {
            $GlobalConfig = new \common\models\GlobalConfig();
            $value        = $GlobalConfig->getDataInType($GlobalConfig::PART_TYPE, 'value');
            $this->setData(Tool::examineEmpty($value['value'], 0));
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改分成
     */
    public function actionUpdatePart()
    {
        try {

            if (Tool::isIssetEmpty($this->post['value']) || !is_numeric($this->post['value'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $value = intval($this->post['value']);
            if ($value < 0 || $value > 100) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $GlobalConfig = new \common\models\GlobalConfig();
            $data         = $GlobalConfig->findOneByField('type', $GlobalConfig::PART_TYPE, true)->add(
                array(
                    'admin_id'   => $this->loginId,
                    'updated_at' => time(),
                    'value'      => $value
                )
            );
            $this->setData($value);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

}