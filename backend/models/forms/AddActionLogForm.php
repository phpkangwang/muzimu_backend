<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/25
 * Time: 17:23
 */

namespace backend\models\forms;


use backend\models\AdminUser;
use backend\models\BaseModel;

class AddActionLogForm extends BaseModel
{
    public $token;
    public $url;
    public $controller_id;
    public $action_id;
    public $module_name;
    public $func_name;
    public $right_name;
    public $create_user;
    public $client_ip;
    public $address;

    public function rules()
    {
        $result = [
            [['create_user','url','controller_id','action_id','module_name','func_name','right_name','token'],'required'],
            [['token'],'validateToken'],
            [['client_ip','address'],'safe']
        ];
        return $result;
    }

    public function validateToken($attribute){
        $user = AdminUser::find()->where(['uname'=>$this->create_user])->one();
        if(!empty($user)){
            if($user->access_token != $this->token){
                $this->addError($attribute, '验签错误');
            }
        }else{
            $this->addError($attribute, '验签错误');
        }

    }
}