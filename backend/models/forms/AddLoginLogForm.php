<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/25
 * Time: 16:28
 */

namespace backend\models\forms;


use backend\models\BaseModel;
use common\services\Rsa;

class AddLoginLogForm extends BaseModel
{
    public $sign;
    public $username;
    public $ip;
    public $device;
    public $browser;
    public $os;
    public $address;

    public function rules()
    {
        $result = [
            [['username','ip', 'sign'],'required'],
            [['sign'],'validateSign'],
            [['device','browser','os','address'],'safe']
        ];
        return $result;
    }
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

}