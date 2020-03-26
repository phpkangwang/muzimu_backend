<?php
namespace backend\controllers\platform\thwj\backend\controllers;

use backend\models\Tool;
use Yii;
use backend\models\Account;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\BaseModel;

class AccountController extends BaseModel
{
    /**
     * 引入特质类 主要用到__call
     */
    use \backend\controllers\platform\PlatformTrait;


    /**
     * @param $_this
     * @return bool|void
     * @throws \yii\db\Exception
     */
    public function add($_this)
    {
        try{
            if( !isset( $_this->get['account'] ) || !isset( $_this->get['password'] ) || !isset( $_this->get['name'] ) || !isset( $_this->get['role'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $account  = $_this->get['account'];
            //判断这个账户是否已经存在
            $existAccount = $_this->Account->findByAccount($account);
            if( !empty($existAccount) )
            {
                throw new MyException( ErrorCode::ERROR_ACCOUNT_EXIST );
            }

            //密码统一为hash加密
            $passwordHashKey = rand(10000, 99999);
            $password = hash( $_this->config['hashAlgo'], $_this->get['password'].$passwordHashKey );
            $name     = $_this->get['name'];
            $role     = $_this->get['role'];
            $popCode  = isset($_this->get['popCode']) ? strtoupper( $_this->get['popCode'] ) : "";
            $parentPopCode = isset($_this->get['parentPopCode']) ? strtoupper($_this->get['parentPopCode']) : "";
            $loginBind= isset($_this->get['loginBind']) ? $_this->get['loginBind'] : 1;
            $admin_id = $_this->loginId;
            $moneyPre=intval(Tool::examineEmpty($_this->get['moneyPre']));

            //只能添加自己下级的角色
            if( !$_this->RoleRelation->isSon($_this->loginInfo['role'], $role) )
            {
                throw new MyException( ErrorCode::ERROR_NOT_CREATE_ROLE_ACCOUNT );
            }

            //判断推广码是否重复
            if( !empty($popCode) ) {
                $popCodeAccountObj = $_this->Account->findByPopCode($popCode);
                if (!empty($popCodeAccountObj)) {
                    throw new MyException(ErrorCode::ERROR_POP_CODE_EXIST);
                }
            }

            //后台账户昵称不能重复
            $AccountNameObj = $_this->Account->findByName($name);
            if( !empty($AccountNameObj) )
            {
                throw new MyException( ErrorCode::ERROR_ACCOUNT_NAME_EXIST );
            }

            $data = array(
                'account'   => $account,
                'password'  => $password,
                'password_hash_key' => $passwordHashKey,
                'name'      => $name,
                'role'      => $role,
                'status'    => 1,
                'pop_code'  => $popCode,
                'login_bind'=> $loginBind,
                'admin_id'  => $admin_id,
                'updated_at'=> $_this->time,
                'created_at'=> $_this->time,
                'money_pre'=>$moneyPre
            );
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            $trGame = Yii::$app->game_db->beginTransaction();
            $objId = $_this->Account->add($data);
            if ( $popCode != "")
            {
                $SeoidDiamondData = array(
                    'seoid' => $popCode,
                    'diamond' => 0,
                    'status' => 0,
                );
                $_this->SeoidDiamond->add($SeoidDiamondData);
            }
            if( !empty($parentPopCode) )
            {
                $parentPopCodeAccountObj = $_this->Account->findByPopCode($parentPopCode);
                if( !empty($parentPopCodeAccountObj) ){
                    if ($moneyPre > $parentPopCodeAccountObj->money_pre) {
                        throw new MyException(ErrorCode::ERROR_DIAMOND_NOT_EXCEED);
                    }
                    //添加一个账户关系
                    $accountRelationData = array(
                        'son_account_id' => $objId,
                        'parent_account_id'=>$parentPopCodeAccountObj['id']
                    );
                    $_this->AccountRelation->add($accountRelationData);
                    $_this->Account->updateAccount(['parent_pop_code'=>$parentPopCodeAccountObj['pop_code']]);

                }else{
                    throw new MyException( ErrorCode::ERROR_DLS_POP_CODE_NOT_EXIST );
                }
            }else{
                //添加到超级管理员账户和管理员的下级
                $nameArr = ['超级管理员','管理员'];
                $superAdminRoleObjs = $_this->Role->findRoleByName($nameArr);
                $superAdminRoleIds = array_column($superAdminRoleObjs,'id');
                $superAdminObjs = $_this->Account->findByRoleIds($superAdminRoleIds);
                $superAdminIds  = array_column($superAdminObjs,'id');
                foreach ($superAdminIds as $superAdminId)
                {
                    if($objId != $superAdminId){
                        $accountRelationData = array(
                            'son_account_id' => $objId,
                            'parent_account_id'=>$superAdminId
                        );
                        $_this->AccountRelation->add($accountRelationData);
                    }
                }
                //给当前创建的角色是管理员和超级管理员就可以访问所有超级管理员的下级账户
                if( in_array($role, $superAdminRoleIds)){
                    $_this->opMySon($objId);
                }
            }
            $tr->commit();
            $trGame->commit();
            $_this->setData($objId);
            $_this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改账户
     * @param $get
     * @return bool
     */
    public function UpdateAccount($get)
    {
        try{
            if( !isset( $get['accountId'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $accountId  = $get['accountId'];
            $password   = isset($get['password']) ? $get['password'] : "";
            $admin_id   = $get['admin_id'];
            $updated_at = $get['updated_at'];
            $hashAlgo   = $get['hashAlgo'];
//            $moneyPre   = isset($get['moneyPre']) ? $get['moneyPre'] : 0;

            if( !empty($password) ){
                if( mb_strlen($password) < 6 || mb_strlen($password) > 16){
                    throw new MyException( ErrorCode::ERROR_USER_PWD_FORMAT );
                }
                $passwordHashKey = rand(10000, 99999);
                $postData['password'] = hash( $hashAlgo, $password.$passwordHashKey );
                $postData['password_hash_key'] = $passwordHashKey;
            }
//            $postData['money_pre']  = $moneyPre;
            $postData['admin_id']   = $admin_id;
            $postData['updated_at'] = $updated_at;

            $AccountObj = Account::findOne($accountId);
            //判断用户存不存在
            if( empty($AccountObj) )
            {
                throw new MyException( ErrorCode::ERROR_ACCOUNT_NOT_EXIST );
            }
            //修改账户
            $AccountObj->updateAccount($postData);
            return true;
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }



    /**
     * @param $baseColumns
     */
    public function baseColumns(&$baseColumns)
    {
        $baseColumns .= ',money_pre';
    }


}

?>