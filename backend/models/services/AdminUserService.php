<?php
namespace backend\services;

use backend\models\AdminUser;
use backend\models\AdminUserRole;
use backend\models\FivepkSeoidDiamond;
use common\models\AdminUserInfo;
use common\services\GroupService;
use common\services\Messenger;
use yii\db\Exception;

class AdminUserService extends AdminUser
{
    public $model;

    public function init()
    {
        $this->model = new Messenger();
    }

    /**
     * 验证访问令牌
     * @return bool
     */
    public static function verifyAccessToken()
    {
        $user = \Yii::$app->user->identity;
        if(empty($_COOKIE['user']) || ($user->access_token != $_COOKIE['user'])) {
            return false;
        }else{
            return true;
        }
    }

    /**
     * 封禁代理商
     * @param $user_id
     * @return mixed
     */
    public function bannedAgent($user_id){
        try {
            $login_user = \Yii::$app->user->identity;
            //查询用户
            $admin_user = AdminUser::findOne($user_id);
            //代理号
            $promo_code = $admin_user->userInfo->promo_code;

            $level = [0,1];

            if(!in_array($login_user->role->level,$level)){
                throw new Exception('权限不足，无法封禁！');
            }
            //有代理商号 封禁游戏代理商
            if(!empty($promo_code)){

                $user_list = GroupService::getUsers($admin_user);   //用户id
                $this->updateUserStatus($user_list,-10);

                $seoid_list = GroupService::getUserChildrenPromoCodes($admin_user);  //代理商号
                $this->updateAgentStatus($seoid_list,1);
            }else{
                $user_list = [$user_id];
                $this->updateUserStatus($user_list,-10);
            }

        }catch(Exception $e){
            $this->model->status = 0;
            $this->model->message = $e->getMessage();
        }
        return $this->model;
    }

    /**
     * 用户或代理商解封
     * @param $user_id
     * @return mixed
     */
    public function unlockAgent( $user_id ){
        try {
            $login_user = \Yii::$app->user->identity;
            //查询用户
            $admin_user = AdminUser::findOne($user_id);
            //代理号
            $promo_code = $admin_user->userInfo->promo_code;
            //用户状态
            $admin_status = $admin_user->status;
            $level = [0,1]; //管理员或超级管理员

            if(!in_array($login_user->role->level,$level)){
                throw new Exception('权限不足，无法封禁！');
            }

            $parent = $admin_user->parent;
            //用户上级状态

            if(!empty($parent) && $parent->status == -10){
                $parent_role_name = $parent->role->name;
                throw new Exception('请先解封'.$parent_role_name.'用户：'.$parent->uname);
            }
            $user_list = [$user_id];   //用户id
            $this->updateUserStatus($user_list,10);

            if(!empty($promo_code)){
                $seoid_list = [$promo_code]; //代理商号
                $this->updateAgentStatus($seoid_list,0);
            }

        }catch(Exception $e){
            $this->model->status = 0;
            $this->model->message = $e->getMessage();
        }
        return $this->model;
    }

    /**
     * 修改后台用户 状态
     * @param array $userList
     * @param int $status
     * @throws Exception
     */
    protected function updateUserStatus($userList = [] , $status = 10){
        if(empty($userList)){
            throw new Exception('用户为空,无法封禁或解封!');
        }
        AdminUser::updateAll(['status'=>$status],['id'=>$userList]);
    }

    /**
     * 修改游戏表 代理商的状态
     * @param array $agentList
     * @param int $status
     * @throws Exception
     */
    protected function updateAgentStatus($agentList = [] , $status = 0){
        if(empty($agentList)){
            throw new Exception('代理商为空,无法封禁或解封!');
        }
        FivepkSeoidDiamond::updateAll(['status'=>$status],['seoid'=>$agentList]);
    }

    /**
     * 删除用户
     * @param array $ids
     * @return mixed
     * @throws Exception
     */
    public function deleteUserInfo($ids = array()){

        //开启事务
        $transaction =  \Yii::$app->db->beginTransaction();
        try{
            $user = \Yii::$app->user->identity;

            $user_id = array_search($user->id,$ids);
            if($user_id !== false  && !empty($ids[$user_id])){
                unset($ids[$user_id]);
            }

            if (count($ids) <= 0) {
                throw new Exception('不能删除该用户');
            }
            $adminUser = [];
            foreach($ids as $key => $val){
                $admin_user =  AdminUser::findOne($val);
                $childrens = GroupService::getChildrenUser($admin_user);
                $array_diff = array_diff($childrens,$ids);

                if(!empty($array_diff)){
                    $one_id = reset($array_diff);
                    $adminUser = AdminUser::findOne($one_id);
                    $array_intersect = array_intersect($ids,$childrens);
                    foreach($array_intersect as $index => $id){
                        unset($ids[$index]);
                    }
                }
            }

            if(empty($ids)){
                throw new Exception('无法删除，请先删除用户：'.$adminUser->uname);
            }
            $promo_codes = AdminUserInfo::getPromoCodes($ids);

            $admin_user = AdminUser::deleteAdminUser($ids);
            $admin_user_info = AdminUserInfo::deleteAdminUserInfo($ids);
            $admin_user_role = AdminUserRole::deleteAdminUserRole($ids);

            try{
                if(!empty($promo_codes)) {
                    $seoid = FivepkSeoidDiamond::deleteSeoId($promo_codes);
                }
            }catch(\Exception $eg){
                throw new Exception($eg->getMessage());
            }

            $transaction->commit();
        }catch(Exception $e){
            $this->model->status = 0;
            $this->model->message = $e->getMessage();
            $transaction->rollBack();
        }
        return $this->model;
    }


}
