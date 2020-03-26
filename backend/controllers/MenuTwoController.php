<?php
namespace backend\controllers;

use backend\models\Account;
use backend\models\rbac\RbacMenuTwo;
use Yii;
use yii\web\Controller;
use backend\models\ErrorCode;
use backend\models\MyException;


class MenuTwoController extends MyController
{

    /**
     *  添加一个二级菜单
     */
    public function actionAdd()
    {
        try{
            if( !isset($this->get['menu_one_id']) || !isset($this->get['name']) || !isset($this->get['url']) || !isset($this->get['level']) || !isset($this->get['status']) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $menu_one_id = $this->get['menu_one_id'];
            $name     = $this->get['name'];
            $url      = $this->get['url'];
            $level    = $this->get['level'];
            $status   = $this->get['status'];
            $admin_id = $this->loginId;
            $time = time();

            if( isset($this->get['id']) )
            {
                $obj = RbacMenuTwo::findOne( $this->get['id'] );
            }else{
                $obj = new RbacMenuTwo();
            }
            $data = array(
                'menu_one_id'=> $menu_one_id,
                'name'      => $name,
                'url'       => $url,
                'level'     => $level,
                'status'    => $status,
                'admin_id'  => $admin_id,
                'updated_at'=> $time,
                'created_at'=> $time,
            );
            //开启事务
            $tr = Yii::$app->db->beginTransaction();
            $obj = $obj->add($data);
            //给超级管理员和当前账户添加菜单权限
            $nameArr = ['超级管理员','管理员'];
            $superAdminRoleObjs = $this->Role->findRoleByName($nameArr);
            $superAdminRoleIds = array_column($superAdminRoleObjs,'id');
            $superAdminObjs = $this->Account->findByRoleIds($superAdminRoleIds);
            $superAdminIds  = array_column($superAdminObjs,'id');
            array_push($superAdminIds, $this->loginId);
            foreach ($superAdminIds as $superAdminId)
            {
                $data = array(
                    'account_id'  => $superAdminId,
                    'menu_two_id' => $obj['id'],
                    'admin_id'    => $this->loginId,
                    'created_at'  => $this->time
                );
                $this->MenuAccount->add( $data );
            }
            $tr->commit();
            $this->setData($obj);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }

    }

    /**
     *  获取二级菜单列表
     */
    public function actionList()
    {
        $data = $this->RbacMenuTwo->tableList();
        foreach ( $data as $key=>$val )
        {
            foreach ( $val['menuTwo'] as $k=>$v)
            {
                $data[$key]['menuTwo'][$k]['adminInfo']    = $this->Account->findBase($val['admin_id']);
                $data[$key]['menuTwo'][$k]['updated_at']   = date("Y-m-d H:i:s", $data[$key]['updated_at']);
                $data[$key]['menuTwo'][$k]['created_at']   = date("Y-m-d H:i:s", $data[$key]['created_at']);
            }
        }
        $this->setData($data);
        $this->sendJson();
    }

    /**
     *  删除一个二级菜单
     */
    public function actionDel()
    {
        try{
            if( !isset($this->get['id']) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $id = $this->get['id'];
            $this->RbacMenuTwo->del($id);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


}

?>