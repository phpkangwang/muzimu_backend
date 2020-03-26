<?php
namespace backend\controllers;

use backend\models\Account;
use backend\models\rbac\RbacMenuOne;
use backend\models\ErrorCode;
use backend\models\MyException;

class MenuController extends MyController
{

    /**
     *  添加一个一级菜单
     */
    public function actionAdd()
    {
        try{
            if( !isset( $this->get['name'] ) || !isset( $this->get['level'] ) || !isset( $this->get['status'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $name     = $this->get['name'];
            $level    = $this->get['level'];
            $status   = $this->get['status'];
            $admin_id = $this->loginId;

            if( isset($this->get['id']) )
            {
                $obj = RbacMenuOne::findOne( $this->get['id'] );
            }else{
                $obj = new RbacMenuOne();
            }

            $data = array(
                'name'      => $name,
                'level'     => $level,
                'status'    => $status,
                'admin_id'  => $admin_id,
                'updated_at'=> $this->time,
                'created_at'=> $this->time,
            );
            $obj = $obj->add($data);
            $this->setData($obj);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  获取一级菜单列表
     */
    public function actionList()
    {
        $RbacMenuOneObj = new RbacMenuOne();
        $data = $RbacMenuOneObj->tableList();

        //$AccountObj = new Account();
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
     *  删除一个一级菜单
     */
    public function actionDel()
    {
        try{
            if( !isset( $this->get['id'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $id = $this->get['id'];
            //$RbacMenuOneObj = new RbacMenuOne();
            $this->RbacMenuOne->del($id);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }




}

?>