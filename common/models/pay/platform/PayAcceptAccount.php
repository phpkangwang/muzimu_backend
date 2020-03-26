<?php
namespace common\models\pay\platform;

use backend\models\BaseModel;
use backend\models\Tool;
use Yii;

class PayAcceptAccount extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_accept_account';
    }

    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'=>'ID',
            'admin_account_id' => '后台用户id',
            'type' => '账户类型1银行账户 2第三方账户',
            'channel_id' => '支付渠道id',
            'account_name' => '账户名称',
            'accept_account' => '收款账户',
            'accept_name' => '收款人姓名',
            'bank_id' => '银行类型id',
            'accept_bank_address' => '收款银行地址',
            'accept_image' => '收款人二维码图片',
            'account_name_define' => '自定义账户名称',
            'accept_account_define' => '自定义收款账户',
            'accept_name_define' => '自定义收款人姓名',
            'accept_money_times' => '存款次数',
            'accept_money_sum' => '存款总金额(分)',
            'status' => '1启用 2停用',
            'create_time' => '创建时间',
        ];
    }


    public function setOrderBy(&$orderBy,&$get,$fields)
    {
        foreach ($fields as $fieldFromGet=>$fieldString){
            if (!Tool::isIssetEmpty($get[$fieldFromGet]) && in_array($get[$fieldFromGet], ['desc', 'asc'])) {
                $orderBy = ",$fieldString " . $get[$fieldFromGet];
            }
        }
    }

 
}
