<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/22
 * Time: 14:25
 */

namespace backend\models\forms;


use backend\models\BaseModel;

class AccountUpForm extends BaseModel
{
    public $user_id;  //代理商用户名id
    public $account_id;  //玩家Id
    public $agent_seoid; //代理号
}