<?php

namespace backend\controllers;

use backend\models\Factory;
use backend\models\rbac\RbacMenuOne;
use backend\models\redis\MyRedis;
use backend\models\Tool;
use common\models\BackendVersion;
use common\models\game\FivepkAccount;
use common\models\game\FivepkReportHot;
use Yii;
use backend\models\Account;
use backend\models\rbac\FunAccount;
use backend\models\rbac\FunList;
use backend\models\rbac\MenuAccount;
use backend\models\rbac\RbacMenuTwo;
use backend\models\ErrorCode;
use backend\models\MyException;

class AccountController extends MyController
{

    public function actionTest()
    {
        die;
        echo "脚本开始时间:" . time() . "</br>";
        //计算某一天的游戏轨迹玩家统计情况
        $stime       = date("2019-1-27", time());
        $etime       = date("2019-1-27 23:59:00", time());
        $create_time = $stime;
        $pageSize    = 10000;
        $stime       = strtotime($stime . '-1 minute') * 1000;
        $etime       = strtotime($etime) * 1000;
        $where       = " enter_time >= '{$stime}' and enter_time <'{$etime}'";

        $FivepkPathcount = $this->FivepkPath->count($where);
        $maxPag          = ceil($FivepkPathcount / $pageSize);

        //获取所有开启的游戏
        $OpenGame = $this->DataGameListInfo->getOpenGame();

        $result = array();
        for ($i = 1; $i <= $maxPag; $i++) {
            foreach ($OpenGame as $val) {
                $selfModelClassName = Yii::$app->params[$val['game_name']]['selfModel'];
                $selfModel          = new $selfModelClassName();
                $gameType           = $val['game_number'];
                if ($gameType == 1) {
                    //这里存放$result的是原始的数据
                    $selfModel->HotRecord($where, $i, $pageSize, $gameType, $result);

                }
            }
        }
        //存放计算前的数据
        $originData = $result;
        //求盈利和各种几率
        foreach ($OpenGame as $val) {
            $selfModelClassName = Yii::$app->params[$val['game_name']]['selfModel'];
            $selfModel          = new $selfModelClassName();
            $gameType           = $val['game_number'];
            if ($gameType == 1) {
                $selfModel->statistics($result);
            }
        }
        //插入数据库
        foreach ($result as $sqlAccountId => $val) {
            foreach ($val as $sqlGameType => $v) {
                if (isset($v['玩家盈利']) && isset($v['玩家游戏机率']) && isset($v['玩家中奖率']) && isset($v['玩家总玩局数'])) {
                    $origin             = json_encode($originData[$sqlAccountId][$sqlGameType]);
                    $postData           = array(
                        'account_id'  => $sqlAccountId,
                        'game_type'   => $sqlGameType,
                        'profit'      => $v['玩家盈利'],
                        'prize_odd'   => $v['玩家中奖率'],
                        'game_odd'    => $v['玩家游戏机率'],
                        'game_times'  => $v['玩家总玩局数'],
                        'origin'      => $origin,
                        'create_time' => $create_time
                    );
                    $FivepkReportHotObj = new FivepkReportHot();
                    $FivepkReportHotObj->add($postData);
                }
            }
        }
        echo "脚本结束时间:" . time();
    }

    /**
     * 后台主题
     * @return mixed
     */
    public function GetJsVersion()
    {
        $data = BackendVersion::find()->asArray()->one();
        return $data['version'];
    }

    /**
     * 后台主题
     * @return mixed
     */
    public function actionInitJsVersion()
    {
        try {
            $jsVersion = time();
            $redisKey  = "jsVersion";
            $this->MyRedis->set($redisKey, $jsVersion);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 后台主题
     * @return mixed
     */
    public function actionGetTheme()
    {
        $redisKey  = "theme:" . $this->loginId;
        $redisData = $this->MyRedis->get($redisKey);
        $this->setData($redisData);
        $this->sendJson();
    }

    /**
     * 后台主题
     * @return mixed
     */
    public function actionSetTheme()
    {
        try {
            if (!isset($this->get['theme'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $theme    = $this->get['theme'];
            $redisKey = "theme:" . $this->loginId;
            $this->MyRedis->set($redisKey, $theme);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 获取登录角色的基本信息
     * @return mixed
     */
    public function actionMyInfo()
    {
        $data             = $this->Account->findBase($this->loginId);
        $data['roleInfo'] = $this->Role->findBase($data['role']);
        $this->setData($data);
        $this->sendJson();
    }

    /**
     * 获取登录角色的基本信息
     * @return mixed
     */
    public function actionLikeName()
    {
        try {
            if (!isset($this->get['name'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $name = $this->get['name'];
            $data = $this->Account->likeName($name);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    public function actionClearRedis()
    {
        Yii::$app->redis->select(Yii::$app->params['redisCommonDatabase']);
        Yii::$app->redis->flushdb();
        $this->setData("redis clear success!!!");
        $this->sendJson();
    }

    /**
     *  添加一个后台账户
     */
    public function actionAdd()
    {
        Factory::AccountController()->add($this);

        try {
            if (!isset($this->get['account']) || !isset($this->get['password']) || !isset($this->get['name']) || !isset($this->get
                    ['role'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $account = $this->get['account'];
            //判断这个账户是否已经存在
            $existAccount = $this->Account->findByAccount($account);
            if (!empty($existAccount)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_EXIST);
            }

            //密码统一为hash加密
            $passwordHashKey = rand(10000, 99999);
            $password        = hash($this->config['hashAlgo'], $this->get['password'] . $passwordHashKey);
            $name            = $this->get['name'];
            $role            = $this->get['role'];
            $popCode         = isset($this->get['popCode']) ? strtoupper($this->get['popCode']) : "";
            $parentPopCode   = isset($this->get['parentPopCode']) ? strtoupper($this->get['parentPopCode']) : "";
            $loginBind       = isset($this->get['loginBind']) ? $this->get['loginBind'] : 1;
            $admin_id        = $this->loginId;

            //只能添加自己下级的角色
            if (!$this->RoleRelation->isSon($this->loginInfo['role'], $role)) {
                throw new MyException(ErrorCode::ERROR_NOT_CREATE_ROLE_ACCOUNT);
            }

            //判断推广码是否正确
            if (!empty($popCode)) {
                $popCodeAccountObj = $this->Account->findByPopCode($popCode);
                if (!empty($popCodeAccountObj)) {
                    throw new MyException(ErrorCode::ERROR_POP_CODE_EXIST);
                }
//                $popCodeAccountObj->money_pre
            }

            //后台账户昵称不能重复
            $AccountNameObj = $this->Account->findByName($name);
            if (!empty($AccountNameObj)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_NAME_EXIST);
            }

            $data = array(
                'account'           => $account,
                'password'          => $password,
                'password_hash_key' => $passwordHashKey,
                'name'              => $name,
                'role'              => $role,
                'status'            => 1,
                'pop_code'          => $popCode,
                'login_bind'        => $loginBind,
                'admin_id'          => $admin_id,
                'updated_at'        => $this->time,
                'created_at'        => $this->time,
            );
            //开启事务
            $tr     = Yii::$app->db->beginTransaction();
            $trGame = Yii::$app->game_db->beginTransaction();
            $objId  = $this->Account->add($data);
            if ($popCode != "") {
                $SeoidDiamondData = array(
                    'seoid'   => $popCode,
                    'diamond' => 0,
                    'status'  => 0,
                );
                $this->SeoidDiamond->add($SeoidDiamondData);
            }

            if (!empty($parentPopCode)) {
                $parentPopCodeAccountObj = $this->Account->findByPopCode($parentPopCode);
                if (!empty($parentPopCodeAccountObj)) {
                    //添加一个账户关系
                    $accountRelationData = array(
                        'son_account_id'    => $objId,
                        'parent_account_id' => $parentPopCodeAccountObj['id']
                    );
                    $this->AccountRelation->add($accountRelationData);
                } else {
                    throw new MyException(ErrorCode::ERROR_DLS_POP_CODE_NOT_EXIST);
                }
            } else {
                //添加到超级管理员账户和管理员的下级
                $nameArr            = ['超级管理员', '管理员'];
                $superAdminRoleObjs = $this->Role->findRoleByName($nameArr);
                $superAdminRoleIds  = array_column($superAdminRoleObjs, 'id');
                $superAdminObjs     = $this->Account->findByRoleIds($superAdminRoleIds);
                $superAdminIds      = array_column($superAdminObjs, 'id');
                foreach ($superAdminIds as $superAdminId) {
                    if ($objId != $superAdminId) {
                        $accountRelationData = array(
                            'son_account_id'    => $objId,
                            'parent_account_id' => $superAdminId
                        );
                        $this->AccountRelation->add($accountRelationData);
                    }
                }
                //给当前创建的角色是管理员和超级管理员就可以访问所有超级管理员的下级账户
                if (in_array($role, $superAdminRoleIds)) {
                    $this->opMySon($objId);
                }
            }
            $trGame->commit();
            $tr->commit();
            $this->setData($objId);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }


    /**
     *   修改账户分成
     */
    public function actionUpdateAccountMoneyPre()
    {
        try {
            if (!isset($this->post['accountId']) || !isset($this->post['money_pre']) || $this->post['money_pre'] < 0) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }

            $accountId = $this->post['accountId'];
            $postData  = array(
                'admin_id'   => $this->loginId,
                'updated_at' => $this->time,
                'money_pre'  => intval($this->post['money_pre']),
            );

            $AccountObj       = Account::findOne($accountId);
            $Account          = new Account();
            $AccountParentObj = $Account->findByPopCode($AccountObj->parent_pop_code);

            if ($postData['money_pre'] > $AccountParentObj->money_pre) {
                throw new MyException(ErrorCode::ERROR_DIAMOND_NOT_EXCEED);
            }

            $allLowUser = $Account::find()->select('id,account,pop_code,money_pre')->where("parent_pop_code='{$AccountObj->pop_code}'")->asArray()->all();

            //不能低于下级
            foreach ($allLowUser as $value) {
                if ($postData['money_pre'] < $value['money_pre']) {
                    throw new MyException(ErrorCode::ERROR_DIAMOND_NOT_LOW_EXCEED);
                }
            }

            //判断用户存不存在
            if (empty($AccountObj)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_NOT_EXIST);
            }

            //修改账户
            $AccountObj->updateAccount($postData);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   修改账户
     *   accountId 账户id
     *   roleId角色id
     */
    public function actionUpdateAccount()
    {
        $this->get['admin_id']   = $this->loginId;
        $this->get['hashAlgo']   = Yii::$app->params['hashAlgo'];
        $this->get['updated_at'] = $this->time;

        Factory::AccountController()->UpdateAccount($this->get);

        try {
            if (!isset($this->get['accountId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId  = $this->get['accountId'];
            $password   = isset($this->get['password']) ? $this->get['password'] : "";
            $admin_id   = $this->get['admin_id'];
            $updated_at = $this->get['updated_at'];
            $hashAlgo   = $this->get['hashAlgo'];

            if (!empty($password)) {
                if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
                    throw new MyException(ErrorCode::ERROR_USER_PWD_FORMAT);
                }
                $passwordHashKey               = rand(10000, 99999);
                $postData['password']          = hash($hashAlgo, $password . $passwordHashKey);
                $postData['password_hash_key'] = $passwordHashKey;
            }
            $postData['admin_id']   = $admin_id;
            $postData['updated_at'] = $updated_at;

            $AccountObj = Account::findOne($accountId);
            //判断用户存不存在
            if (empty($AccountObj)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_NOT_EXIST);
            }
            //修改账户
            $AccountObj->updateAccount($postData);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  删除一个账户
     */
    public function actionDel()
    {
        try {
            if (!isset($this->get['id'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $id = $this->get['id'];

            if (!$this->AccountRelation->isSon($this->loginId, $id)) {
                throw new MyException(ErrorCode::ERROR_NOT_SON);
            }
            $AccountObj = $this->Account->findBase($id);
            $roleObj    = $this->Role->findBase($AccountObj['role']);

            //账户如果有钻石不能删除
            $SeoidDiamondObj = $this->SeoidDiamond->findBySeoid($AccountObj['pop_code']);
            if (!empty($SeoidDiamondObj)) {
                if ($SeoidDiamondObj['diamond'] != 0) {
                    throw new MyException(ErrorCode::ERROR_ACCOUNT_HAS_DIAMOND);
                }
            }

            //如果有直系下级不能删除账户
            if ($roleObj['name'] != "管理员" && $this->AccountRelation->hasSon($id)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_HAS_SON);
            }
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            //删除账户
            $this->Account->del($id);
            //删除账户关系
            $this->AccountRelation->del($id);
            //删除账户权限
            $this->FunAccount->del($id);
            //删除账户菜单
            $this->MenuAccount->del($id);
            //删除账户分数
            $this->SeoidDiamond->delByPopCode($AccountObj['pop_code']);
            $tr->commit();
            $this->setData("success");
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  分页获取下级账户
     */
    public function actionSonPage()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId     = isset($this->get['accountId']) ? $this->get['accountId'] : $this->loginId;
            $pageNo        = $this->get['pageNo'];
            $pageSize      = $this->get['pageSize'];
            $where         = "  1";
            $searchAccount = isset($this->get['account']) ? $this->get['account'] : "";
            $roleId        = isset($this->get['roleId']) ? $this->get['roleId'] : "";

            //获取我所有的直系下级账户
            $sonRoleIds      = $this->AccountRelation->findSon($accountId, true);
            $sonAccountObjs  = $this->Account->finds($sonRoleIds);
            $sonAccountIds   = array_column($sonAccountObjs, 'id');
            $sonAccountIdstr = implode(",", $sonAccountIds);
            if (!empty($sonAccountIdstr)) {
                $where .= " and id in({$sonAccountIdstr})";
            }
            if (!empty($searchAccount)) {
                $where .= " and account = '{$searchAccount}'";
            }
            if (!empty($roleId)) {
                $where .= " and role = '{$roleId}'";
            }
            $data    = $this->Account->page($pageNo, $pageSize, $where);
            $account = $this->Account->accountNum($where);

            $page = array(
                'account' => $account,
                'maxPage' => ceil($account / $pageSize),
                'nowPage' => $pageNo
            );
            foreach ($data as $key => $val) {
                $data[$key]['roleInfo']   = $this->Role->findBase($val['role']);
                $data[$key]['hasSon']     = $this->AccountRelation->hasSon($val['id']);
                $data[$key]['adminInfo']  = $this->Account->findBase($val['admin_id']);
                $data[$key]['updated_at'] = date("Y-m-d H:i:s", $data[$key]['updated_at']);
                $data[$key]['isBind']     = $this->AccountLoginBind->isBind($val['id']);
            }
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  分页获取下级账户
     */
    public function actionAllSonPage()
    {
        try {
            if (!isset($this->get['pageNo']) || !isset($this->get['pageSize'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId     = $this->loginId;
            $pageNo        = $this->get['pageNo'];
            $pageSize      = $this->get['pageSize'];
            $where         = "  1";
            $searchAccount = isset($this->get['account']) ? $this->get['account'] : "";
            $roleId        = isset($this->get['roleId']) ? $this->get['roleId'] : "";

            //获取我所有的直系下级账户
            $sonRoleIds      = $this->AccountRelation->findAllSon($accountId, true);
            $sonAccountObjs  = $this->Account->finds($sonRoleIds);
            $sonAccountIds   = array_column($sonAccountObjs, 'id');
            $sonAccountIdstr = implode(",", $sonAccountIds);
            if (!empty($sonAccountIdstr)) {
                $where .= " and id in({$sonAccountIdstr})";
            }
            if (!empty($searchAccount)) {
                $where .= " and account = '{$searchAccount}'";
            }
            if (!empty($roleId)) {
                $where .= " and role = '{$roleId}'";
            }
            $data    = $this->Account->page($pageNo, $pageSize, $where);
            $account = $this->Account->accountNum($where);
            $page    = array(
                'account' => $account,
                'maxPage' => 1,
                'nowPage' => 1
            );
            foreach ($data as $key => $val) {
                $data[$key]['roleInfo']   = $this->Role->findBase($val['role']);
                $data[$key]['hasSon']     = $this->AccountRelation->hasSon($val['id']);
                $data[$key]['adminInfo']  = $this->Account->findBase($val['admin_id']);
                $data[$key]['updated_at'] = date("Y-m-d H:i:s", $data[$key]['updated_at']);
            }
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  允许某个账户操作我的下级账户
     */
    public function opMySon($accountId)
    {
        try {
            $accountSonIds = $this->AccountRelation->findDirectSon($this->loginId);
            foreach ($accountSonIds as $accountSonId) {
                if ($accountSonId != $accountId) {
                    //添加一个账户关系
                    $accountRelationData = array(
                        'son_account_id'    => $accountSonId,
                        'parent_account_id' => $accountId,
                        'outer'             => 1
                    );
                    $this->AccountRelation->add($accountRelationData);
                }
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
        return true;
    }

    /**
     *  后台账号登录
     *  account  账号
     *  password 密码
     *  code     验证码
     */
    public function actionLogin()
    {

        try {
            if (!isset($this->get['account']) || !isset($this->get['password']) || !isset($this->get['code']) || !isset($this->get['yzmId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $account  = $this->get['account'];
            $password = $this->get['password'];
            $code     = $this->get['code'];
            $yzmId    = $this->get['yzmId'];
            $redis    = new MyRedis();
            $yzmCode  = $redis->get('yzmCode' . $yzmId);

            //判断验证码
            if ($code !== $yzmCode) {
                throw new MyException(ErrorCode::ERROR_YZM);
            } else {
                //销毁session验证码
                //$session->remove('yzmCode'.$yzmId);
                //$session->destroy();
            }
            $accountObj = $this->Account->findByAccount($account);
            if (empty($accountObj)) {
                throw new MyException(ErrorCode::ERROR_PASSWORD);
            }

            //检查是否在指定的机器上登录
            $this->AccountLoginBind->check($accountObj->id);
            //判断账号是否被锁定
            if ($accountObj->status == 2) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_LOGIN_LOCK);
            }
            //判断密码是否正确
            if ($accountObj->password != hash($this->config['hashAlgo'], $password . $accountObj->password_hash_key)) {
                $error_login_times = $accountObj->error_login_times + 1;
                $accountObj->errorLoginTimes("add");
                if ($error_login_times < 3) {
                    throw new MyException("您的密码输错" . $error_login_times . "次,请重新输入");
                } else {
                    throw new MyException("您的密码输错" . $error_login_times . "次,请联系管理员解锁");
                }
            } else {
                $accountObj->errorLoginTimes("init");
            }
            //给用户添加一个
            $ba    = array(
                'id'   => $accountObj->id,
                'time' => $this->time
            );
            $token = base64_encode(json_encode($ba));
            $accountObj->updateAccount(array('token' => $token));

            //添加用户登录日志
            $AgentInfo = Tool::getAgentInfo();
            //查询Ip
            $IpArea  = new \common\services\IpArea();
            $address = $IpArea->getLoginIpAddress($AgentInfo['ip']);

            $logLoginData = array(
                'account_id' => $accountObj->id,
                'ip'         => $AgentInfo['ip'],
                'address'    => $address,
                'OS'         => $AgentInfo['os'],
                'device'     => $AgentInfo['device'],
                'browser'    => $AgentInfo['browser'],
                'created_at' => $this->time
            );
            $this->LogLogin->add($logLoginData);

            $isBind    = $this->AccountLoginBind->isBind($accountObj->id);
            $roleObj   = $this->Role->findBase($accountObj->role);
            $jsVersion = $this->GetJsVersion();
            $this->setData(array('name' => $accountObj->account, 'role' => $roleObj['name'], 'id' => $accountObj->id, 'token' => $token, 'isBind' => $isBind, 'jsVersion' => $jsVersion));
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  设置Token
     */
    private function setToken(&$db, &$logLoginData)
    {

        $arr = array(
            'id'        => $db->id,
            'time'      => $this->time,
            'ip'        => $logLoginData['ip'],
            'userAgent' => Yii::$app->request->headers['user-agent']
        );
        //token 算法
        $tokenArr     = array(
            'token' => md5(json_encode($arr) . Yii::$app->request->cookieValidationKey),
            'uid'   => $arr['id']
        );
        $token        = base64_encode(json_encode($tokenArr));
        $arr['token'] = $tokenArr['token'];
        $this->MyRedis->writeCacheHash($this->redisTokenName, $arr['id'], $arr);

        return $token;
    }


    /**
     *  修改账户状态
     */
    public function actionStatus()
    {
        try {
            if (!isset($this->get['accountId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId = $this->get['accountId'];
            $obj       = Account::findOne($accountId);
            if ($obj->status == 2) {
                $this->Account->updateStatus($obj->id, "access", $this->loginId);
            } else {
                $this->Account->updateStatus($obj->id, "forbid", $this->loginId);
            }
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  退出账户
     */
    public function actionLoginOut()
    {
        $accountObj = Account::findOne($this->loginId);
        $data       = array(
            'token' => ""
        );
        $accountObj->updateAccount($data);
        $this->sendJson();
    }

    /**
     *  添加账号绑定信息
     */
    public function actionAddLoginBind()
    {
        try {
            $accountId = $this->loginId;
            if ($this->AccountLoginBind->isBind($this->loginId)) {
                throw new MyException(ErrorCode::ERROR_ACCOUNT_IS_BIND);
            }
            $cookieName  = 'newbackend' . $accountId;
            $cookieValue = Tool::createRand();
            //设置cookie有效期为10年
            setcookie($cookieName, $cookieValue, time() + 10 * 365 * 24 * 60 * 60);
            $data = array(
                'account_id' => $accountId,
                'browser'    => "",
                'mac'        => "",
                'cookie'     => $cookieValue,
            );

            $this->AccountLoginBind->add($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  删除账号绑定信息
     */
    public function actionDelAccountBind()
    {
        try {
            if (!isset($this->get['accountId'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId = $this->get['accountId'];
            $this->AccountLoginBind->del($accountId);
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   修改账户菜单权限
     *   accountId 账户id
     *   ids 二级菜单ids
     */
    public function actionUpdateMenu()
    {
        try {
            if (!isset($this->get['accountId']) || !isset($this->get['ids'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId  = $this->get['accountId'];
            $accountObj = $this->Account->findBase($accountId);
            if (!$this->RoleRelation->isSon($this->loginInfo['role'], $accountObj['role'])) {
                throw new MyException(ErrorCode::ERROR_UPDATE_MENU_NOT_SON);
            }
            $ids            = $this->get['ids'];
            $ids            = explode(",", $ids);
            $RbacMenuTwoObj = new RbacMenuTwo();
            $MenuAccountObj = new MenuAccount();
            //获取已存在且要给角色的权限列表
            $RbacMenuTwoObjs = $RbacMenuTwoObj->finds($ids);
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            //删除这个账户下的所有权限
            $MenuAccountObj->del($accountId);
            foreach ($RbacMenuTwoObjs as $val) {
                $data = array(
                    'account_id'  => $accountId,
                    'menu_two_id' => $val['id'],
                    'admin_id'    => $this->loginId,
                    'created_at'  => $this->time
                );
                $MenuAccountObj->add($data);
            }
            $tr->commit();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *   修改账户功能权限
     *   accountId 账户的id
     *   ids 功能ids
     */
    public function actionUpdateFun()
    {
        try {
            if (!isset($this->get['accountId']) || !isset($this->get['ids'])) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $accountId  = $this->get['accountId'];
            $accountObj = $this->Account->findBase($accountId);
            if (!$this->RoleRelation->isSon($this->loginInfo['role'], $accountObj['role'])) {
                throw new MyException(ErrorCode::ERROR_UPDATE_FUN_NOT_SON);
            }
            $ids           = $this->get['ids'];
            $ids           = explode(",", $ids);
            $FunListObj    = new FunList();
            $FunAccountObj = new FunAccount();
            //获取已存在且要给角色的权限列表
            $FunListObjs = $FunListObj->finds($ids);
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            //删除这个角色下的所有权限
            $FunAccountObj->del($accountId);
            foreach ($FunListObjs as $val) {
                $data = array(
                    'account_id'  => $accountId,
                    'function_id' => $val['id'],
                    'admin_id'    => $this->loginId,
                    'created_at'  => $this->time
                );
                $FunAccountObj->add($data);
            }
            $tr->commit();
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   获取账户的菜单列表
     */
    public function actionMenuList()
    {
        try {
            if (!isset($this->get['accountId'])) {
                $accountId = $this->loginId;
            } else {
                $accountId = $this->get['accountId'];
            }
            if (empty($accountId)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $MenuAccountObj = $this->MenuAccount->findByAccount($accountId);
            $MenuTwoIds     = array_column($MenuAccountObj, 'menu_two_id');
            $MenuTwoObjs    = $this->RbacMenuTwo->finds($MenuTwoIds);
            $MenuOneIds     = array_unique(array_column($MenuTwoObjs, 'menu_one_id'));
            $MenuOneObjs    = $this->RbacMenuOne->finds($MenuOneIds);
            $k              = 0;
            $data           = array();
            foreach ($MenuOneObjs as $MenuOneObj) {
                $data[$k]            = $MenuOneObj;
                $data[$k]['menuTwo'] = array();
                foreach ($MenuTwoObjs as $MenuTwoObj) {
                    if ($MenuOneObj['id'] == $MenuTwoObj['menu_one_id']) {
                        array_push($data[$k]['menuTwo'], $MenuTwoObj);
                    }
                }
                $k++;
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   获取账户下面的所有的功能
     */
    public function actionFunList()
    {
        try {
            if (!isset($this->get['accountId'])) {
                $accountId = $this->loginId;
            } else {
                $accountId = $this->get['accountId'];
            }
            if (empty($accountId)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $data = $this->FunAccount->findbyAccount($accountId);
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   获取登录用户的菜单列表
     */
    public function actionLoginMenuList()
    {
        $MenuAccountObj = new MenuAccount();
        $RbacMenuOneObj = new RbacMenuOne();
        $RbacMenuTwoObj = new RbacMenuTwo();
        $MenuAccountObj = $MenuAccountObj->findByAccount($this->loginId);
        $MenuTwoIds     = array_column($MenuAccountObj, 'menu_two_id');
        $MenuTwoObjs    = $RbacMenuTwoObj->finds($MenuTwoIds);
        $MenuOneIds     = array_unique(array_column($MenuTwoObjs, 'menu_one_id'));
        $MenuOneObjs    = $RbacMenuOneObj->finds($MenuOneIds);
        $k              = 0;
        $data           = array();
        $GetJsVersion   = $this->GetJsVersion();
        foreach ($MenuOneObjs as $MenuOneObj) {
            if ($MenuOneObj['status'] == 1) {
                $data[$k]            = $MenuOneObj;
                $data[$k]['menuTwo'] = array();
                foreach ($MenuTwoObjs as $MenuTwoObj) {
                    if ($MenuOneObj['id'] == $MenuTwoObj['menu_one_id'] && $MenuTwoObj['status'] == 1) {
                        $MenuTwoObj['url'] = $this->Tool->htmlAddVersion($MenuTwoObj['url'], $GetJsVersion);
                        array_push($data[$k]['menuTwo'], $MenuTwoObj);
                    }
                }
                $k++;
            }

        }
        $this->setData($data);
        $this->sendJson();
        return;
    }

    /**
     * 修改用户层级
     */
    public function actionUpdatePayLayer()
    {
        Tool::checkParam(['accountId', 'payLayerId'], $this->post);
        FivepkAccount::updatePayLayer(intval($this->post['accountId']), intval($this->post['payLayerId']));
        $this->sendJson();
    }

    /**
     * 查看用户信息
     */
    public function actionGetPayLayerShow()
    {
        try {
            Tool::checkParam(['accountId'], $this->get);
            $data         = FivepkAccount::find()->select('account_id,phone_number,name,pay_layer')->where(array('account_id' => intval($this->get['accountId'])))->asArray()->one();
            $data['name'] = Factory::Tool()->hideName($data['name']);
            if (empty($data)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 查看用户层级或手机号信息
     */
    public function actionGetPLShow()
    {
        try {
            Tool::checkParam(['accountId'], $this->get);
            if (isset($this->get['phoneNumber'])) {
                $select = 'phone_number';
            } elseif (isset($this->get['payLayer'])) {
                $select = 'pay_layer';
            } else {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $data         = FivepkAccount::find()->select('account_id,name,' . $select)->where(array('account_id' => intval($this->get['accountId'])))->asArray()->one();
            $data['name'] = Factory::Tool()->hideName($data['name']);
            if (empty($data)) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $this->setData($data);
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


}

?>