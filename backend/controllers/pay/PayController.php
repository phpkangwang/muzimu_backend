<?php

namespace backend\controllers\pay;

use backend\models\Account;
use backend\models\Factory;
use backend\models\rbac\AccountRelation;
use backend\models\Tool;
use common\models\game\FivepkAccount;
use common\models\pay\platform\GTPay;
use common\models\pay\platform\PayChannel;
use common\models\pay\platform\PayBank;
use common\models\pay\platform\PayLayerAccount;
use common\models\pay\platform\PayLayerAccountUnion;
use common\models\pay\platform\PayManagement;
use common\models\pay\platform\PayMenu;
use common\models\pay\platform\PayThirdPlatform;
use common\models\pay\platform\PayThirdPlatformAccount;
use \common\models\pay\platform\PayAcceptAccount;
use common\models\TailkingAddress;
use Yii;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;

use common\models\pay\platform\PayOrder;


class PayController extends MyController
{
    /**
     *   获取所有用户层级
     */
    public function actionLayerAccountList()
    {
        $accountId = isset($this->get['accountId']) ? $this->get['accountId'] : "";

        $PayLayerAccountModel = new PayLayerAccount();
        if (empty($accountId)) {
            $list = $PayLayerAccountModel->tableList();
        } else {
            $list = $PayLayerAccountModel->findByAccount($accountId);
        }

        $adminAccountIds = array_column($list, 'admin_account_id', 'admin_account_id');
        $Account         = new Account;
        $AccountData     = $Account->finds($adminAccountIds, 'id');
        foreach ($list as &$value) {
            $value['nickName'] = Tool::examineEmpty($AccountData[$value['admin_account_id']]['name']);
        }
        $this->setData($list);
        $this->sendJson();
    }

    /**
     *   新增
     */
    public function actionLayerAccountAdd()
    {
        try {
            if (!isset($this->post['accountId']) || !isset($this->post['name']) || !isset($this->post['sort'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $postData = array(
                'admin_account_id' => $this->post['accountId'],
                'name'             => $this->post['name'],
                'sort'             => $this->post['sort'],
            );
            $id       = isset($this->post['id']) ? $this->post['id'] : "";
            if (empty($id)) {
                $PayLayerAccountModel = new PayLayerAccount();
            } else {
                $PayLayerAccountModel = PayLayerAccount::findOne($id);
            }
            $data             = $PayLayerAccountModel->add($postData);
            $Account          = new Account;
            $AccountObj       = $Account->findBase($data['admin_account_id']);
            $data['nickName'] = Tool::examineEmpty($AccountObj['name']);
            $this->setData($data);
            $this->sendJson();
            return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   删除
     */
    public function actionLayerAccountDelete()
    {
        try {
            if (!isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id                   = $this->get['id'];
            $PayLayerAccountModel = new PayLayerAccount();
            $PayLayerAccountModel->del($id);
            //删除层级以后所有未这个层级的用户的层级都为默认值1
            FivepkAccount::initAccountPayLayer($id);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  支付菜单列表
     */
    public function actionPayMenuList()
    {
        //获取活动配置
        $PayMenuModel = new PayMenu();
        $list         = $PayMenuModel->tableList();
        $this->setData($list);
        $this->sendJson();
    }

    /**
     *  支付菜单列表
     */
    public function actionPayMenuListToSelect()
    {
        //获取活动配置
        $PayMenuModel = new PayMenu();
        $list         = $PayMenuModel->tableListSelect();
        $this->setData($list);
        $this->sendJson();
    }


    /**
     *   第三方存款渠道列表
     */
    public function actionPayChannelList()
    {
        //获取活动配置
        $PayChannelModel = new PayChannel();
        $list            = $PayChannelModel->tableList();
        $this->setData($list);
        $this->sendJson();
    }

    /**
     *    修改渠道金钱上下限制
     */
    public function actionUpdatePayChannel()
    {
        Tool::checkParam(['minMoney', 'maxMoney', 'id'], $this->post);
        $postData['min_money'] = $this->post['minMoney'];
        $postData['max_money'] = $this->post['maxMoney'];
        $id                    = $this->post['id'];
        $PayChannelObj         = PayChannel::findOne($id);
        $obj                   = $PayChannelObj->add($postData);
        $data                  = $PayChannelObj->findBase($obj['id']);
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *   第三方存款渠道列表
     */
    public function actionPayChannelByMenu()
    {
        Tool::checkParam(['menuId'], $this->get);
        $menuId = $this->get['menuId'];

        //获取活动配置
        $PayChannelModel = new PayChannel();
        $list            = $PayChannelModel->findByMenuId($menuId);
        $this->setData($list);
        $this->sendJson();
    }

    /**
     *   新增
     */
    public function actionThirdPlatformAccountAdd()
    {
        try {
            if (!isset($this->post['accountId']) || !isset($this->post['thirdPlatformId']) || !isset($this->post['status'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $postData                     = array(
                'admin_account_id'  => $this->post['accountId'],
                'third_platform_id' => $this->post['thirdPlatformId'],
                'status'            => $this->post['status'],
                'create_time'       => $this->time
            );
            $PayThirdPlatformAccountModel = new PayThirdPlatformAccount();
            $data                         = $PayThirdPlatformAccountModel->add($postData);
            $this->setData($data);
            $this->sendJson();
            return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   删除
     */
    public function actionThirdPlatformAccountDelete()
    {
        try {
            if (!isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id                           = $this->get['id'];
            $PayThirdPlatformAccountModel = new PayThirdPlatformAccount();
            $PayThirdPlatformAccountModel->del($id);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 添加/修改支付收款账户列表
     */
    public function actionPayAcceptAccountAdd()
    {
        $data = $this->platform(__FUNCTION__, [$this]);

        try {
            //验证
            Tool::checkParam([
//                'acceptAccount',
//                'acceptName',
                'adminAccountId',
                'channelId',
                'payMoneyList',
                'payMenuId',
            ], $this->post);

            $id = 0;
            if (!Tool::isIssetEmpty($this->post['id'])) {
                $id = intval($this->post['id']);
            }

            if (
                (!is_numeric($this->post['minMoney']) && $this->post['minMoney'] !== '')//如果不是空并且不是数字
                ||
                (!is_numeric($this->post['maxMoney']) && $this->post['maxMoney'] !== '')//如果不是空并且不是数字
                ||
                (is_numeric($this->post['minMoney']) && is_numeric($this->post['maxMoney']) && $this->post['maxMoney'] <= $this->post['minMoney'])
                //如果都是数字 并且最大值小于等于最小值
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            //取值
            $data = [
                'pay_menu_id'           => $this->post['payMenuId'],//菜单
                'pay_channel_id'        => intval($this->post['channelId']),//支付渠道id
                'admin_account_id'      => intval($this->post['adminAccountId']),//总后台是前端传 代理商是只能代理商创建
                'accept_image'          => Tool::examineEmpty($this->post['acceptImage']),//收款人二维码图片 第三方的时候才有值
                'account_name'          => Tool::examineEmpty($this->post['accountName']),//账户名称
                'accept_account'        => Tool::examineEmpty($this->post['acceptAccount']),//收款账户
                'accept_name'           => Tool::examineEmpty($this->post['acceptName']),//收款人姓名
                'account_name_define'   => Tool::examineEmpty($this->post['accountNameDefine']),//别名
                'accept_account_define' => Tool::examineEmpty($this->post['acceptAccountDefine']),//别名
                'accept_name_define'    => Tool::examineEmpty($this->post['acceptNameDefine']),//别名
                'pay_bank_id'           => intval(Tool::examineEmpty($this->post['bankId'])),//类型是银行的时候才有值
                'accept_bank_address'   => Tool::examineEmpty($this->post['acceptBankAddress']),//开户行 类型是银行的时候才有值
                'shop_id'               => Tool::examineEmpty($this->post['shopId']),//对接第三方商户号
                'shop_key'              => Tool::examineEmpty($this->post['shopKey']),//对接第三方商户秘钥
                'validate_key'          => Tool::examineEmpty($this->post['validateKey']),//对接第三方商户秘钥
                'priority_min_money'    => $this->post['minMoney'],//最小钱由前端填
                'priority_max_money'    => $this->post['maxMoney'],//最大钱由前端填
                'pay_money_list'        => $this->post['payMoneyList'],//快捷支付钱选择列表
            ];

            $layerJson        = explode(',', Tool::examineEmpty($this->post['layerOptionJson']));
            $PayAcceptAccount = new PayAcceptAccount();

            $tr = $PayAcceptAccount::getDb()->beginTransaction();

            if (empty($id)) {
                $data['create_time']        = $this->time;
                $data['status']             = 1;
                $data['accept_money_times'] = 0;
                $data['accept_money_sum']   = 0;
                //添加
                $obj = $PayAcceptAccount;
            } else {
                $obj = $PayAcceptAccount->findOneByField('id', $id);
            }
            $data = $obj->add($data);
            PayLayerAccountUnion::deleteAll(['pay_accept_account_id' => $data['id']]);
            if (is_array($layerJson) && !empty($layerJson)) {
                $pay_layer_account_union_arr = [];
                foreach ($layerJson as $val) {
                    if (empty($val)) {
                        continue;
                    }
                    $pay_layer_account_union_arr[] = [$data['id'], $val];
                }
                if (!empty($pay_layer_account_union_arr)) {
                    PayLayerAccountUnion::getDb()->createCommand()->batchInsert(
                        'pay_layer_account_union', ['pay_accept_account_id', 'pay_layer_account_id']
                        , $pay_layer_account_union_arr
                    )->execute();
                }
            }
            $tr->commit();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }

    /**
     * 修改支付收款账户状态
     */
    public function actionPayAcceptAccountStatus()
    {
        try {

            //验证
            if (
                Tool::isIssetEmpty($this->post['id'])
                || Tool::isIssetEmpty($this->post['status'])
                || !in_array($this->post['status'], [1, 2])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $id = intval($this->post['id']);
            //取值
            $data = [
                'status' => intval($this->post['status']),//1启用 2停用
            ];

            $PayAcceptAccount = new PayAcceptAccount();
            $obj              = $PayAcceptAccount->findOneByField('id', $id);
            if (empty($obj)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_NOT_EXIST);
            }
            $obj->add($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }


    /**
     * 删除支付收款账户
     */
    public function actionPayAcceptAccountDelete()
    {
        try {
            //验证
            if (
            Tool::isIssetEmpty($this->post['id'])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = intval($this->post['id']);
            PayAcceptAccount::DeleteAll(['id' => $id]);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 查看支付收款账户列表
     */
    public function actionPayAcceptAccountList()
    {

        $this->platform(__FUNCTION__, [$this]);

        $orderBy               = 'id desc';
        $PayAcceptAccountModel = new PayAcceptAccount();
        $PayAcceptAccountModel->setOrderBy($orderBy, $this->get, ['acceptMoneyTimes' => 'accept_money_times', 'acceptMoneySum' => 'accept_money_sum']);
        $list                          = $PayAcceptAccountModel::find()
            ->select('pay_accept_account.*,pay_menu.name as pay_menu_name,pay_channel.name as pay_channel_name,pay_bank.name as pay_bank_name')
            ->andFilterWhere(['=', 'pay_accept_account.admin_account_id', Tool::examineEmpty($this->get['adminAccountId'])])
            ->andFilterWhere(['=', 'pay_accept_account.status', Tool::examineEmpty($this->get['status'])])
            ->andFilterWhere(['=', 'pay_accept_account.id', Tool::examineEmpty($this->get['id'])])
            ->andFilterWhere(['=', 'pay_accept_account.pay_channel_id', Tool::examineEmpty($this->get['payChannelId'])])
            ->andFilterWhere(['=', 'pay_accept_account.pay_menu_id', Tool::examineEmpty($this->get['payMenuId'])])
            ->orderBy($orderBy)
//            ->leftJoin('newcq.admin_account as aa','aa.id=pay_accept_account.admin_account_id')
            ->leftJoin('pay_menu', 'pay_menu.id=pay_accept_account.pay_menu_id')
            ->leftJoin('pay_channel', 'pay_channel.id=pay_accept_account.pay_channel_id')
            ->leftJoin('pay_bank', 'pay_bank.id=pay_accept_account.pay_bank_id')
            ->asArray()
            ->all();
        $adminAccountIds               = array_column($list, 'admin_account_id', 'admin_account_id');
        $ids                           = array_column($list, 'id');
        $Account                       = new Account;
        $AccountData                   = $Account->finds($adminAccountIds, 'id');
        $counts                        = PayLayerAccount::getCountOfAccount($adminAccountIds);
        $PayLayerAccountUnionData      = PayLayerAccountUnion::find()
            ->select('pay_layer_account_union.*,pay_layer_account.name')
            ->leftJoin('pay_layer_account', "pay_layer_account.id = pay_layer_account_union.pay_layer_account_id")
            ->where(['in', 'pay_layer_account_union.pay_accept_account_id', $ids])
            ->asArray()
            ->all();
        $PayLayerAccountUnionDataIndex = [];
        foreach ($PayLayerAccountUnionData as $val) {
            $PayLayerAccountUnionDataIndex[$val['pay_accept_account_id']][$val['pay_layer_account_id']] = $val;
        }
        foreach ($list as &$value) {
            $value['layer_count']     = isset($counts[$value['admin_account_id']]) ? $counts[$value['admin_account_id']]['count'] : 0;
            $value['admin_nick_name'] = Tool::examineEmpty($AccountData[$value['admin_account_id']]['name']);
            $value['create_time']     = date(Tool::DATE_USUALLY_FORMAT, $value['create_time']);
            if (isset($PayLayerAccountUnionDataIndex[$value['id']])) {
                $value['layer_select'] = $PayLayerAccountUnionDataIndex[$value['id']];
            }
        }
        $this->setData($list);
        $this->sendJson();
    }

    /**
     * 上传二维码
     */
    public function actionQRCodeUploaded()
    {
        Tool::checkParam(['QRCode'], $this->post);
        $QRCode   = $this->post['QRCode'];
        $arr      = &Tool::checkImageBase64($QRCode, 1024, ['jpg', 'jpeg', 'png', 'gif']);
        $result   = $arr['result'];
        $imageStr = $arr['imageStr'];
        //用图片流来上传图片
        $fileDir  = Yii::getAlias('@imageBaseDir') . DIRECTORY_SEPARATOR . $this->loginId . DIRECTORY_SEPARATOR;
        $rand     = Tool::createFileName($this->time);
        $fileName = "$rand.{$result[2]}";
//        $filePath = $fileDir . $fileName;
        //上传文件
        $Tool = new Tool();
        $Tool->streamUploadFile($fileDir, $fileName, $imageStr);
        $this->setData($this->loginId . DIRECTORY_SEPARATOR . $fileName);
        $this->sendJson();
    }


    /**
     *   银行列表
     */
    public function actionBankList()
    {
        $PayBankModel = new PayBank();
        $list         = $PayBankModel->tableList();
        $this->setData($list);
        $this->sendJson();
    }

    /**
     *   获取全部总代理
     */
    public function actionAdminZdlList()
    {
        $Account    = new Account();
        $sql        = "SELECT admin_account.id,admin_account.name
                FROM admin_account
                INNER JOIN admin_rbac_role  on  admin_rbac_role.id= admin_account.role
                WHERE
                admin_rbac_role.`name` ='总代理';";
        $connection = $Account::getDb();
        $data       = $connection->createCommand($sql)->queryAll();
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * 修改银行状态
     */
    public function actionPayBankStatusUpdate()
    {
        try {
            Tool::checkParam(['id', 'status'], $this->post);
            //验证
            if (
            !in_array($this->post['status'], [1, 2])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = intval($this->post['id']);
            //取值
            $data  = [
                'status' => intval($this->post['status']),//1启用 2停用
            ];
            $model = new PayBank();
            $obj   = $model->findOneByField('id', $id);
            if (empty($obj)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_NOT_EXIST);
            }
            $obj->add($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改菜单状态
     */
    public function actionPayMenuStatusUpdate()
    {
        try {
            Tool::checkParam(['id', 'status'], $this->post);
            //验证
            if (
            !in_array($this->post['status'], [1, 2])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = intval($this->post['id']);
            //取值
            $data  = [
                'status' => intval($this->post['status']),//1启用 2停用
            ];
            $model = new PayMenu();
            $obj   = $model->findOneByField('id', $id);
            if (empty($obj)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_NOT_EXIST);
            }
            $obj->add($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改层次状态
     */
    public function actionPayLayerStatusUpdate()
    {
        try {
            Tool::checkParam(['id', 'status'], $this->post);
            //验证
            if (
            !in_array($this->post['status'], [1, 2])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = intval($this->post['id']);
            //取值
            $data  = [
                'status' => intval($this->post['status']),//1启用 2停用
            ];
            $model = new PayLayerAccount();
            $obj   = $model->findOneByField('id', $id);
            if (empty($obj)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_NOT_EXIST);
            }
            $obj->add($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改渠道状态
     */
    public function actionPayChannelStatusUpdate()
    {
        try {
            Tool::checkParam(['id', 'status'], $this->post);
            //验证
            if (
            !in_array($this->post['status'], [1, 2])
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = intval($this->post['id']);
            //取值
            $data  = [
                'status' => intval($this->post['status']),//1启用 2停用
            ];
            $model = new PayChannel();
            $obj   = $model->findOneByField('id', $id);
            if (empty($obj)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_NOT_EXIST);
            }
            $obj->add($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改渠道请求地址
     */
    public function actionPayChannelPostUrlUpdate()
    {
        try {
            Tool::checkParam(['id', 'url'], $this->post);
            //验证
            if (
            !filter_var($this->post['url'], FILTER_VALIDATE_URL)
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = intval($this->post['id']);
            //取值
            $data  = [
                'post_url' => $this->post['url'],
            ];
            $model = new PayChannel();
            $obj   = $model->findOneByField('id', $id);
            if (empty($obj)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_NOT_EXIST);
            }
            $obj->add($data);
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
            $pageNo       = $this->get['pageNo'];
            $pageSize     = $this->get['pageSize'];
            $stime        = $this->get['stime'];
            $etime        = $this->get['etime'] . " 23:59:59";
            $orderNo      = isset($this->get['orderNo']) ? $this->get['orderNo'] : "";
            $accountId    = isset($this->get['accountId']) ? $this->get['accountId'] : "";
            $name         = isset($this->get['name']) ? $this->get['name'] : "";
            $popCode      = isset($this->get['popCode']) ? $this->get['popCode'] : "";
            $status       = isset($this->get['status']) ? $this->get['status'] : "";
            $type         = isset($this->get['type']) ? $this->get['type'] : "";
            $payMenuId    = isset($this->get['payMenuId']) ? intval($this->get['payMenuId']) : "";
            $payChannelId = isset($this->get['payChannelId']) ? intval($this->get['payChannelId']) : "";

            $accountName = Tool::examineEmpty($this->get['accountName']);
            $channelName = Tool::examineEmpty($this->get['channelName']);

            $Account = new Account();

            $popCodeArr   = $Account->findAllSonPopCode($this->loginId);
            $popCodeStrIn = "'" . implode("','", $popCodeArr) . "'";

            $where = " pay_order.seoid in ({$popCodeStrIn})";
            if (!empty($orderNo)) {
                $where .= " and pay_order.order_no = '{$orderNo}'";
            }
            if (!empty($accountId)) {
                $where .= " and pay_order.account_id = '{$accountId}'";
            }
            if (!empty($name)) {
                $where .= " and pay_order.name like '%{$name}%'";
            }
            if (!empty($popCode)) {
                $where .= " and pay_order.seoid = '{$popCode}'";
            }
            if (!empty($status)) {
                $where .= " and pay_order.status = '{$status}'";
            }
            if (!empty($type)) {
                $where .= " and pay_order.type = '{$type}'";
            }
            if (!empty($payChannelId)) {
                $where .= " and pay_order.pay_channel_id = '{$payChannelId}'";
            }

            if (!empty($accountName)) {
                $where .= " and pay_accept_account.accept_account like '%{$accountName}%'";
            }

            if (!empty($channelName)) {
                $where .= " and pay_channel.name like '%{$channelName}%'";
            }

            if (!empty($payMenuId)) {
                //线下
                $where .= " and pay_order.pay_menu_id = 1";
            } else {
                //线上
                $where .= " and pay_order.pay_menu_id > 1";
            }

            if (!empty($stime) && !empty($etime)) {
                $stime = strtotime($stime) * 1000;
                $etime = strtotime($etime) * 1000;
                $where .= " and pay_order.create_time between '{$stime}' and '{$etime}'";
            }

            $PayOrder = new PayOrder();

            $rs     = Tool::page($pageNo, $pageSize);
            $limit  = $rs['limit'];
            $offset = $rs['offset'];
            $obj    = $PayOrder::find()->where($where)
                ->leftJoin('pay_accept_account', 'pay_order.pay_accept_account_id=pay_accept_account.id')
                ->leftJoin('pay_channel', 'pay_channel.id=pay_order.pay_channel_id')
                ->orderBy('id desc')->offset($offset)->limit($limit);
            $data   = $obj->asArray()->all();
//echo $obj->createCommand()->getRawSql().PHP_EOL;
//die;
            $PayLayerAccountData = PayAcceptAccount::find()->where(['in', 'id', array_column($data, 'pay_accept_account_id', 'pay_accept_account_id')])->select('id,account_name,accept_account,accept_name')->asArray()->indexBy('id')->all();
            $PayChannelData      = PayChannel::find()->where(['in', 'id', array_column($data, 'pay_channel_id', 'pay_channel_id')])->select('id,name')->asArray()->indexBy('id')->all();
            $PayMenuData         = PayMenu::find()->where(['in', 'id', array_column($data, 'pay_menu_id', 'pay_menu_id')])->select('id,name')->asArray()->indexBy('id')->all();
            foreach ($data as $key => $val) {
                $data[$key]['account_name']     = Tool::examineEmpty($PayLayerAccountData[$val['pay_accept_account_id']]['account_name']);
                $data[$key]['pay_menu_name']    = Tool::examineEmpty($PayMenuData[$val['pay_menu_id']]['name']);
                $data[$key]['pay_channel_name'] = Tool::examineEmpty($PayChannelData[$val['pay_channel_id']]['name']);
                $data[$key]['name']             = Factory::Tool()->hideName($val['name']);
                $data[$key]['create_time']      = date("Y-m-d H:i:s", $val['create_time'] / 1000);
                $data[$key]['update_time']      = empty($val['update_time']) ? '' : date("Y-m-d H:i:s", $val['update_time'] / 1000);
                $data[$key]['accept_account']   = Tool::examineEmpty($PayLayerAccountData[$val['pay_accept_account_id']]['accept_account']);
                $data[$key]['accept_name']      = Tool::examineEmpty($PayLayerAccountData[$val['pay_accept_account_id']]['accept_name']);
            }

            if (isset($this->get['total'])) {

                $obj = $PayOrder::find()->where($where)
                    ->leftJoin('pay_accept_account', 'pay_order.pay_accept_account_id=pay_accept_account.id')
                    ->leftJoin('pay_channel', 'pay_channel.id=pay_order.pay_channel_id');
                $sum = $obj->asArray()->sum('pay_order.fee');

                $data = [
                    'data'  => $data,
                    'total' => $sum
                ];
            }

            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 付款钻石未到账回调
     */
    public function actionUpPayStatus()
    {
        try {
            Tool::checkParam(['id'], $this->post);
            $PayOrder    = new PayOrder();
            $PayOrderObj = $PayOrder->findOneByField('id', $this->post['id'], false);

            if (!isset($PayOrderObj['status'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            /*status = 1的时候   只有 pay_menu_id  = 1 的时候  这个时候 线下人工支付  这个时候能够手动点击改变状态 其余的都不可以点击
status = 4的时候  都可以点击 其余的都不能点击*/
            if (!(
                ($PayOrderObj['pay_menu_id'] == 1 && $PayOrderObj['status'] == 1) || (
                    $PayOrderObj['status'] == 4
                ))
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }


            $PayManagement          = new PayManagement();
            $PayManagement->tradeNo = $PayOrderObj['trade_no'];
            $status                 = $PayManagement->upPayStatus([
                'operateName' => Tool::examineEmpty($this->loginInfo['name'], '未知账号'),
                'ordernumber' => $PayOrderObj['order_no'],
                'sysnumber'   => $PayOrderObj['trade_no'],
            ]);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 修改订单状态 -失败
     */
    public function actionUpPayFailure()
    {
        try {
            Tool::checkParam(['id'], $this->post);
            $PayOrder    = new PayOrder();
            $PayOrderObj = $PayOrder->findOneByField('id', $this->post['id'], true);

            if (isset($PayOrderObj->status) && $PayOrderObj->status == 1) {
                $PayOrderObj->payFailure($PayOrderObj->order_no);
            } else {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    //第三方客服 查看
    public function actionTailkingList()
    {
        $obj = TailkingAddress::find()->asArray()->all();
        $this->setData($obj);
        $this->sendJson();
    }

    //第三方客服 修改/添加
    public function actionTailkingUpdate()
    {
        Tool::checkParam(['name', 'address'], $this->post);

        $data = ['name' => $this->post['name'], 'address' => $this->post['address']];

        $TailkingAddress = new TailkingAddress();

        if (isset($this->post['id']) && !empty($this->post['id'])) {
            $obj = $TailkingAddress->findOneByField('id', $this->post['id'], true);
        } else {
            $AccountRelationModel = new AccountRelation();
            $zdlId                = $AccountRelationModel->sonGetZdlId($this->loginId);
            $AccountModel         = new Account();
            $AccountObj           = $AccountModel->findBase($zdlId);

            $obj           = $TailkingAddress;
            $data['seoid'] = $AccountObj['pop_code'];
        }

        $return = $obj->add($data);

        $this->setData($return);
        $this->sendJson();
    }

}

?>