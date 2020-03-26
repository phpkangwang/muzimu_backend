<?php
namespace backend\controllers;

use backend\models\Account;
use backend\models\rbac\FunAccount;
use backend\models\rbac\FunRole;
use backend\models\rbac\FunSort;
use Yii;
use backend\models\Role;
use backend\models\rbac\FunList;
use backend\models\ErrorCode;
use backend\models\MyException;

class FunController extends MyController
{

    /**
     *  添加一个后台功能权限
     */
    public function actionAdd()
    {
        try{
            if( !isset( $this->get['name'] ) || !isset( $this->get['url'] ) || !isset( $this->get['type'] )|| !isset( $this->get['level'] ) || !isset( $this->get['status'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $name     = $this->get['name'];
            $classify = $this->get['classify'];
            $url      = $this->get['url'];
            $type     = $this->get['type'];
            $level    = $this->get['level'];
            $roleIds  = isset( $this->get['roleIds'] ) ? $this->get['roleIds'] : "";
            $status   = $this->get['status'];
            $admin_id = $this->loginId;
            $time     = time();
            if( isset($this->get['id']) )
            {
                $FunListObj = FunList::findOne( $this->get['id'] );
            }else{
                $FunListObj = new FunList();
            }

            $data = array(
                'name'      => $name,
                'classify'  => $classify,
                'url'       => $url,
                'type'      => $type,
                'level'     => $level,
                'status'    => $status,
                'admin_id'  => $admin_id,
                'updated_at'=> $time,
                'created_at'=> $time,
            );
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            $FunListObj = $FunListObj->add($data);
            //假如选择了角色
            if( $roleIds != "")
            {
                $FunRoleObj = new FunRole();
                $AccountObj = new Account();
                $FunAccountObj = new FunAccount();
                $roleIds = explode(",", $roleIds);
                //所有的权限都必须给管理员添加
                $adminId = $this->Role->getAdminId();
                in_array($adminId,$roleIds) ? "" : array_push($roleIds, $adminId);
                //给角色添加这个权限
                foreach ($roleIds as $roleId)
                {
                    $roleFundata = array(
                        'role_id'     => $roleId,
                        'function_id' => $FunListObj['id'],
                        'admin_id'    => $this->loginId,
                        'created_at'  => $time
                    );
                    $FunRoleObj->add( $roleFundata );
                }
                //查找这个角色下的所有账户添加权限
                $accounts = $AccountObj->findByRoleIds($roleIds);
                foreach ($accounts as $acoount)
                {
                    $data = array(
                        'account_id'  => $acoount['id'],
                        'function_id' => $FunListObj['id'],
                        'admin_id'    => $this->loginId,
                        'created_at'  => $this->time
                    );
                    $FunAccountObj->add( $data );
                }
            }
            $tr->commit();
            $this->setData($FunListObj);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  分页获取后台功能权限
     *  pageNo  第几页
     * pageSize 一页多少条数据
     */
    public function actionPage()
    {
        try{
            if( !isset( $this->get['pageNo'] ) || !isset( $this->get['pageSize'] )  )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $pageNo   = $this->get['pageNo'];
            $pageSize = $this->get['pageSize'];
            //$obj  = new FunList();
            $data = $this->FunList->page($pageNo, $pageSize);
            $account = $this->FunList->accountNum();
            $page = array(
                'account' => $account,
                'maxPage' => ceil($account/$pageSize),
                'nowPage' => $pageNo
            );

            //$AccountObj = new Account();
            foreach ($data as $key=>$val)
            {
                $data[$key]['adminInfo']    = $this->Account->findBase($val['admin_id']);
                $data[$key]['updated_at']   = date("Y-m-d H:i:s", $data[$key]['updated_at']);
            }
            $this->setData($data);
            $this->setPage($page);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  获取功能列表
     *  type 三种状态 all(查询所有) my(查询登录用户) find(查询指定用户)
     */
    public function actionList()
    {
        try{
            if( !isset( $this->get['type'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }else{
                $type = $this->get['type'];
            }
            if( !isset( $this->get['accountId'] ) )
            {
                $accountId = $this->loginId;
            }else{
                $accountId = $this->get['accountId'];
            }
            $FunAccountData = array();
            if( $type != "all" ) {
                $FunAccountData = $this->FunAccount->findbyAccount($accountId);
            }
            $classifyArr = $this->FunSort->tableList();

            $data = $this->FunList->tableList();
            $rs = array();
            foreach ($classifyArr as $key => $calssify)
            {
                $rs[$calssify['name']] = array();
                foreach ($data as $val)
                {
                    if( $type != "all" ){
                        foreach ( $FunAccountData as $FunAccount){
                            if( $val['classify'] == $calssify['id'] && $val['id'] == $FunAccount['function_id'])
                            {
                                array_push($rs[$calssify['name']], $val);
                            }
                        }
                    }else{
                        if( $val['classify'] == $calssify['id'] )
                        {
                            array_push($rs[$calssify['name']], $val);
                        }
                    }
                }
            }

            $this->setData($rs);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  删除一个后台功能权限
     */
    public function actionDel()
    {
        try{
            if( !isset( $this->get['id'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $id = $this->get['id'];
            //$obj  = new FunList();
            $this->FunList->del($id);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  获取功能权限大分类排序
     */
    public function actionFunSortList()
    {
        $data = $this->FunSort->tableList();
        foreach ($data as $key=>$val)
        {
            $data[$key]['adminInfo']    = $this->Account->findBase($val['admin_id']);
            $data[$key]['updated_at']   = date("Y-m-d H:i:s", $data[$key]['updated_at']);
            $data[$key]['created_at']   = date("Y-m-d H:i:s", $data[$key]['created_at']);
        }
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *  获取功能权限大分类-修改
     */
    public function actionFunSortAdd()
    {
        try {
            if ( !isset($this->post['name']) || !isset($this->post['level']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }

            $id    = isset( $this->post['id'] ) ? $this->post['id'] : "";
            $name  = $this->post['name'];
            $level = $this->post['level'];
            $postData = array(
                'name' => $name,
                'level' => $level,
                'admin_id' => $this->loginId,
                'updated_at' => $this->time,
            );
            if( !empty($id) )
            {
                //修改
                $obj = FunSort::findOne($id);
                $obj->add($postData);
            }else{
                //新增
                $postData['created_at'] = $this->time;
                $this->FunSort->add($postData);
            }
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    //获取功能权限大分类-删除
    public function actionFunSortDelete()
    {
        try {
            if ( !isset($this->post['ids']) ) {
                throw new MyException(ErrorCode::ERROR_PARAM );
            }
            $ids     = $this->post['ids'];
            $ids     = explode(',', $ids);
            FunSort::DeleteAll(['id'=>$ids]);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

}

?>