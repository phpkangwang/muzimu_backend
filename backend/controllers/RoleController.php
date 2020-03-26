<?php
namespace backend\controllers;

use Yii;
use backend\models\rbac\Role;
use backend\models\ErrorCode;
use backend\models\MyException;

class RoleController extends MyController
{

    /**
     *  添加一个角色
     */
    public function actionAdd()
    {
        try{
            if( !isset( $this->get['name'] ) || !isset( $this->get['hasPopCode'] ) || !isset( $this->get['accountIsMyCode'] ) || !isset( $this->get['useParentDiamond'] ) || !isset( $this->get['lookParent'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $name     = $this->get['name'];
            $hasPopCode = $this->get['hasPopCode'];
            $accountIsMyCode = $this->get['accountIsMyCode'];
            $useParentDiamond = $this->get['useParentDiamond'];
            $lookParent = $this->get['lookParent'];
            $admin_id = $this->loginId;
            $time     = time();
            if( isset($this->get['id']) )
            {
                $obj = Role::findOne( $this->get['id'] );
            }else{
                $obj  = new Role();
            }
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            //添加角色
            $data = array(
                'name'      => $name,
                'has_pop_code' => $hasPopCode,
                'account_is_my_code' => $accountIsMyCode,
                'use_parent_diamond' => $useParentDiamond,
                'look_parent' => $lookParent,
                'admin_id'  => $admin_id,
                'created_at'=> $time,
            );
            $obj = $obj->add($data);
            $tr->commit();
            $this->setData($obj);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  初始化角色权限
     */
    public function actionInitRole()
    {
        if( !isset( $this->get['id'] ) )
        {
            throw new MyException( ErrorCode::ERROR_PARAM );
        }
        //查找这个角色下所有的账户
        $id = $this->get['id'];
        $RoleIds = [$id];
        $accountObjs = $this->Account->findByRoleIds($RoleIds);
        //开启事务
        $tr = Yii::$app->db->beginTransaction();
        foreach ($accountObjs as $accountObj)
        {
            $id = $accountObj['id'];
            $role = $accountObj['role'];
            $adminId = $this->loginId;
            //修改账户角色菜单权限
            $this->Account->addRoleMenu($id, $role, $adminId);
            //修改账户角色功能权限
            $this->Account->addFunMenu($id, $role, $adminId);
        }
        $tr->commit();
        $this->sendJson();
    }


    /**
     *  获取角色列表
     */
    public function actionList()
    {
        $redisKey="role:List";
        $redisData = $this->MyRedis->get($redisKey);
        if( empty($redisData) )
        {
            $data = $this->Role->tableList();
            $findAllRelations = $this->RoleRelation->findAllRelation();
            foreach ($data as $key=>$val)
            {
                $data[$key]['parentRoleInfo'] = array();
                foreach ( $findAllRelations as $findAllRelation)
                {
                    if( $findAllRelation['son_role_id'] == $val['id']){
                        $data[$key]['parentRoleInfo'] = $findAllRelation['parent_role_info'];
                    }
                }
                $data[$key]['hasSon']      = $this->RoleRelation->hasSon($val['id']);
                $data[$key]['adminInfo']    = $this->Account->findBase($val['admin_id']);
                $data[$key]['created_at']   = date("Y-m-d H:i:s", $data[$key]['created_at']);
            }
            $this->MyRedis->set($redisKey, json_encode($data) );
        }else{
            $data = json_decode($redisData, true);
        }
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *  获取下级角色列表
     */
    public function actionGetSonList()
    {
        try{
            if( !isset( $this->get['parentRoleId'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $parentRoleId = $this->get['parentRoleId'];
            $sonRoleIds = $this->RoleRelation->findSon($parentRoleId, false);
            $data = $this->Role->finds($sonRoleIds);
            $findAllRelations = $this->RoleRelation->findAllRelation();
            foreach ($data as $key=>$val)
            {
                $data[$key]['parentRoleInfo'] = array();
                foreach ( $findAllRelations as $findAllRelation)
                {
                    if( $findAllRelation['son_role_id'] == $val['id']){
                        $data[$key]['parentRoleInfo'] = $findAllRelation['parent_role_info'];
                    }
                }
                $data[$key]['hasSon']       = $this->RoleRelation->hasSon($val['id']);
                $data[$key]['adminInfo']    = $this->Account->findBase($val['admin_id']);
                $data[$key]['created_at']   = date("Y-m-d H:i:s", $data[$key]['created_at']);
            }
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  获取我的所有下级角色
     */
    public function actionMySonList()
    {
        try{
            $parentRoleId = $this->loginInfo['role'];
            $sonRoleIds = $this->RoleRelation->findAllSon($parentRoleId, false);
            $data = $this->Role->finds($sonRoleIds);
            foreach ($data as $key=>$val)
            {
                $data[$key]['hasSon']       = $this->RoleRelation->hasSon($val['id']);
                $data[$key]['adminInfo']    = $this->Account->findBase($val['admin_id']);
                $data[$key]['created_at']   = date("Y-m-d H:i:s", $data[$key]['created_at']);
            }
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  删除一个角色
     */
    public function actionDel()
    {
        try{
            if( !isset( $this->get['id'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $id = $this->get['id'];
            //$RoleObj = new Role();
            $this->Role->del($id);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  给角色添加菜单权限
     *  每次修改的时候先删除这个角色的所有菜单权限，然后再添加所有拥有的权限
     *  roleId 角色id
     *  ids 菜单ids 字符串，隔开
     */
    public function actionAddRoleMenu()
    {
        try{
            if( !isset( $this->get['roleId'] ) ||  !isset( $this->get['ids'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $roleId = $this->get['roleId'];
            $ids    = $this->get['ids'];
            $time   = time();
            $ids    = explode(",", $ids );
            //获取已存在且要给角色的权限列表
            $RbacMenuTwoObjs = $this->RbacMenuTwo->finds( $ids );
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            //删除这个角色下的所有权限
            $this->MenuRole->del($roleId);
            foreach ($RbacMenuTwoObjs as $val)
            {
                $data = array(
                    'role_id' => $roleId,
                    'menu_two_id' => $val['id'],
                    'admin_id' => $this->loginId,
                    'created_at' => $time
                );
                $this->MenuRole->add( $data );
            }
            $tr->commit();
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   获取某个角色下面的所有的菜单
     */
    public function actionMenuList()
    {
        try{
            if( !isset( $this->get['roleId'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $roleId = $this->get['roleId'];
            //$MenuRoleObj = new MenuRole();
            $data = $this->MenuRole->findbyRole($roleId);
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     *  给角色添加功能权限
     *  每次修改的时候先删除这个角色的所有功能权限，然后再添加所有拥有的权限
     *  roleId 角色id
     *  ids 菜单ids 字符串，隔开
     */
    public function actionAddRoleFun()
    {
        try{
            if( !isset( $this->get['roleId'] ) ||  !isset( $this->get['ids'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $roleId = $this->get['roleId'];
            $ids    = $this->get['ids'];
            $time   = time();
            $ids    = explode(",", $ids );
            //$FunListObj = new FunList();
            //$FunRoleObj = new FunRole();
            //获取已存在且要给角色的权限列表
            $FunListObjs = $this->FunList->finds( $ids );
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            //删除这个角色下的所有权限
            $this->FunRole->del($roleId);
            foreach ($FunListObjs as $val)
            {
                $data = array(
                    'role_id' => $roleId,
                    'function_id' => $val['id'],
                    'admin_id' => $this->loginId,
                    'created_at' => $time
                );
                $this->FunRole->add( $data );
            }
            $tr->commit();
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   获取某个角色下面的所有的功能
     */
    public function actionFunList()
    {
        try{
            if( !isset( $this->get['roleId'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $roleId = $this->get['roleId'];
            //$FunRoleObj = new FunRole();
            $data = $this->FunRole->findbyRole($roleId);
            $this->setData($data);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

}

?>