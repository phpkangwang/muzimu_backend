<?php
namespace backend\models\forms\player;
use common\models\game\FivepkAccount;
use common\services\GroupService;
use common\services\ToolService;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/27
 * Time: 10:47
 */

class ChangePwdForm extends \yii\base\Model
{
    /**
     * @var 用户名
     */
    public $username;
    /**
     * @var 密码
     */
    public $password;
    /**
     * @var 重复密码
     */
    public $verify_password;

    /**
     * 规则
     * @return array
     */
    public function rules()
    {
        return [
            ['verify_password', "required",'message'=>"确认密码不能为空"],
            ["username", "required",'message'=>"用户名不能为空"],
            ['password', "required",'message'=>"密码不能为空"],
            ['password', 'match', 'pattern' => '/^[_0-9a-z]{6,12}$/i', 'message' => "密码位数必须在6-12位之间，且只能是数字，字母，下划线"],
            ['verify_password','verifyCurrentPwd'],
            ['username','FindPlayer'],
        ];
    }

    public function attributes()
    {
        return [
            'username' => '玩家账号',
            'password' => '密码',
            'verify_password' => '确认密码',
        ];
    }

    /**
     * 验证密码
     */
    public function verifyCurrentPwd()
    {
        if($this->password != $this->verify_password)
        {
            $this->addError("verify_password", "两次密码不一致");
        }
    }

    /**
     * 查询玩家是否存在
     */
    public function FindPlayer()
    {
        $user = \Yii::$app->user->identity;
        $group_codes = GroupService::getUserChildrenPromoCodes($user);
        $player = FivepkAccount::find()->filterWhere(['name'=>$this->username])->andFilterWhere(['in','seoid',$group_codes])->one();
        if(empty($player)){
            $this->addError('username','该玩家不存在');
        }
    }
}