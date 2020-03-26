<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/22
 * Time: 14:34
 */

namespace backend\models\forms;

use backend\models\AdminUser;
use common\services\Rsa;

class AgentAddUserForm extends UserForm
{
    public $token;
    public $id;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }

    public function rules()
    {
        $result = [
            ['id','safe'],
            [['token'],'required'],
            [['token'],'validateToken']
        ];

        $result = array_merge(parent::rules(),$result);

        return $result;
    }

    /**
     * 验证公钥
     */
    public function validateSign($attribute){

        $rsa = new Rsa();
        $result = $rsa->decryptByPrivateKey($this->sign);

        if(!empty($result)){
            $arr_result = json_decode($result);
            foreach ($arr_result as $key=>$value){
                if($value != $this->$key){
                    $this->addError($attribute, '验签错误');
                }
            }
        }else{
            $this->addError($attribute, '解析验签错误');
        }
    }

    public function validateToken($attribute)
    {
        $user = AdminUser::find()->where(['uname'=>$this->created_by])->one();
        if(!empty($user)){
            if($user->access_token != $this->token){
                $this->addError($attribute, '验签错误');
            }
        }else{
            $this->addError($attribute, '验签错误');
        }
    }
}