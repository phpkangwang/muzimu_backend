<?php

namespace common\models\pay\platform;

use backend\models\BaseModel;
use Yii;

class PayChannel extends BaseModel
{

    /*渠道*/
    const PAY_CHANNEL_XX_BANK = 1;//线下网银存款
    const PAY_CHANNEL_XX_WX = 2;//线下微信支付
    const PAY_CHANNEL_XX_ALI = 3;//线下支付宝支付

    const PAY_CHANNEL_XS_GT_BANK = 4;//高通银联快捷
    const PAY_CHANNEL_XS_GT_WX_WAP = 5;//高通微信支付wap
    const PAY_CHANNEL_XS_GT_ALI_WAP = 6;//高通支付宝支付wap
    const PAY_CHANNEL_XS_GT_QR = 7;//高通银联扫码
    const PAY_CHANNEL_XS_GT_BANK_NULL = 8;//高通无卡快捷
    const PAY_CHANNEL_XS_GT_WX = 9;//高通微信支付
    const PAY_CHANNEL_XS_GT_ALI = 10;//高通支付宝支付

    const PAY_CHANNEL_XS_SF_ALI = 11;//首富支付宝支付
    const PAY_CHANNEL_XS_SF_WX = 12;//首富微信支付
    const PAY_CHANNEL_XS_SF_YUN = 13;//首富云闪付
    const PAY_CHANNEL_XS_SF_BANK_NT = 14;//首富网银支付

    const PAY_CHANNEL_XS_ZX_WX_QR = 15;//兆鑫微信扫码
    const PAY_CHANNEL_XS_ZX_WX_H5 = 16;//兆鑫微信H5
    const PAY_CHANNEL_XS_ZX_ALI_QR = 17;//兆鑫支付宝扫码
    const PAY_CHANNEL_XS_ZX_ALI_H5 = 18;//兆鑫支付宝H5
    const PAY_CHANNEL_XS_ZX_BANK_UNITE = 19;//兆鑫银联扫码
    const PAY_CHANNEL_XS_ZX_BANK_UNITE_H5 = 20;//兆鑫银联H5

    const PAY_CHANNEL_XS_CP_ALI = 21;//CPAY支付宝
    const PAY_CHANNEL_XS_CP_WX = 22;//CPAY微信
    const PAY_CHANNEL_XS_SKY_ALI = 23;//SKY支付宝
    const PAY_CHANNEL_XS_SKY_WX = 24;//SKY微信

    const PAY_CHANNEL_XS_ZY_ALI = 25;//掌易支付宝
    const PAY_CHANNEL_XS_ZY_WX = 26;//掌易微信

    const PAY_CHANNEL_XS_SKY_ALI2 = 27;//SKY支付宝2
    const PAY_CHANNEL_XS_SKY_ALI3 = 28;//SKY支付宝3

    const PAY_CHANNEL_XS_DIOR_ALI = 29;//DIOR支付宝
    const PAY_CHANNEL_XS_DIOR_WX = 30;//DIOR微信

    const PAY_CHANNEL_XS_OREO_ALI = 31;//Oreo支付宝支付
    const PAY_CHANNEL_XS_OREO_WX = 32;//Oreo微信支付
    const PAY_CHANNEL_XS_OREO_YUN = 33;//Oreo云闪付
    const PAY_CHANNEL_XS_OREO_BANK_NT = 34;//Oreo网银支付

    const PAY_CHANNEL_XS_BOLE_ALI = 35;//BOLE支付宝
    const PAY_CHANNEL_XS_BOLE_WX = 36;//BOLE微信


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_channel';
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
            'id'         => 'ID',
            'name'       => '第三方平台支付id',
            'icon'       => 'icon',
            'need_image' => '是否需要二维码',
            'min_money'  => '最小金额',
            'max_money'  => '最大金额',
            'status'     => '状态',
        ];
    }

    /**
     *  关联菜单列表
     * @return \yii\db\ActiveQuery
     */
    public function getPayMenu()
    {
        return $this->hasOne(PayMenu::className(), ['id' => 'pay_menu_id']);
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        return self::find()
            ->joinWith('payMenu')
            ->orderBy('id desc')->asArray()->all();
    }

    public function findBase($id)
    {
        return self::find()
            ->joinWith('payMenu')
            ->where('pay_channel.id=:id', array('id' => $id))
            ->asArray()
            ->one();
    }

    /**
     *  根据menuid查找所有信息
     * @param $menuId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findByMenuId($menuId)
    {
        return self::find()
            ->joinWith('payMenu')
            ->where('pay_menu_id = :pay_menu_id and pay_channel.status=1', array(":pay_menu_id" => $menuId))
            ->orderBy('id desc')
            ->asArray()
            ->all();
    }
}
