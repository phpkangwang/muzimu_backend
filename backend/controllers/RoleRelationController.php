<?php
namespace backend\controllers;

use Yii;
use backend\models\ErrorCode;
use backend\models\MyException;

class RoleRelationController extends MyController
{
    /**
     *   添加一个角色关系
     *   parentRoleId  父角色id
     *   sonRoleId     子角色id
     */
    public function actionAdd()
    {
        try{
            if( !isset( $this->get['parentRoleId'] ) || !isset( $this->get['sonRoleId'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $parentRoleId = $this->get['parentRoleId'];
            $sonRoleId = $this->get['sonRoleId'];

            if( $sonRoleId == $parentRoleId)
            {
                throw new MyException( ErrorCode::ERROR_NOT_EACH_PARENT );
            }
            //不能互相是上下级
            if( $this->RoleRelation->isSon($sonRoleId, $parentRoleId) )
            {
                throw new MyException( ErrorCode::ERROR_NOT_EACH_PARENT );
            }
            //增加
            $RoleRelationData = array(
                'parent_role_id' => $parentRoleId,
                'son_role_id'    => $sonRoleId
            );
            $this->RoleRelation->add($RoleRelationData);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  删除一个角色关系
     */
    public function actionDel()
    {
        try{
            if( !isset( $this->get['parentRoleId'] ) || !isset( $this->get['sonRoleId'] ) )
            {
                throw new MyException( ErrorCode::ERROR_PARAM );
            }
            $parentRoleId = $this->get['parentRoleId'];
            $sonRoleId = $this->get['sonRoleId'];
            $this->RoleRelation->delByParentSon($parentRoleId, $sonRoleId);
            $this->sendJson();
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }



}

?>