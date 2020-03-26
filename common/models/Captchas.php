<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "captchas".
 *
 * @property integer $id
 * @property integer $id_user
 * @property string $target_type
 * @property string $code
 * @property integer $status
 * @property string $phone
 * @property string $vendor
 * @property string $result
 * @property string $rs_msg
 * @property string $trade_id
 * @property integer $created_at
 * @property integer $expire_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 */
class Captchas extends \yii\db\ActiveRecord
{
    const timeout = 60;//验证码限制再次获取间隔
    const timeoff = 3000;//验证码过期时间
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'captchas';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_user', 'status', 'created_at', 'expire_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'expire_at', 'updated_at', 'created_by', 'updated_by'], 'required'],
            [['target_type'], 'string', 'max' => 64],
            [['code'], 'string', 'max' => 10],
            [['phone', 'vendor', 'result'], 'string', 'max' => 32],
            [['rs_msg', 'trade_id'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增id',
            'id_user' => '用户id',
            'target_type' => '目标类型',
            'code' => '验证码',
            'status' => '状态',
            'phone' => '手机',
            'vendor' => '供应商',
            'result' => '回执码',
            'rs_msg' => '回执信息',
            'trade_id' => '短信订单号',
            'created_at' => '创建时间',
            'expire_at' => '过期时间',
            'updated_at' => '更新时间',
            'created_by' => '创建者id',
            'updated_by' => '更新者id',
        ];
    }

    /**
     * 生成短信验证码，单日过多或间隔时间限制
     * 默认返回消息'',
     */
    public function generateCode() {
        $message = '';
        //'created_at'=>date('Ymd',time())
        $query = $this->find()->andFilterWhere(['phone'=>$this->phone])
            ->andFilterWhere(['>','created_at',strtotime(date('Ymd',time()))])
            ->orderBy('id desc');
        $dataProvider = new ActiveDataProvider( [
            'query' => $query,
        ]);
        Yii::info('短消息当日记录 : ' . $dataProvider->count,'notes');
        if ($dataProvider->count>99){
            $message = '请求消息次数太多，请明天再试';
        }else{
            $time = time();
            if ($dataProvider->count>0 && ($time - $dataProvider->models[0]->created_at) <self::timeout){
                $message = '请求消息间隔时间过短，请稍后再试';
            }else{
                $this->code = (string)rand(100000, 999999);
                $this->created_by = '1';
                $this->updated_by = '1';
                $this->created_at = $time;
                $this->expire_at = $time + self::timeoff;
                $message = 'ok';
            }
        }
        return $message;
    }

    /**
     * 初始化通知用短消息记录
     * @param string $str_notice 限制长度小于10
     * @param integer $id_user
     */
    public function generateNotice($str_notice,$id_user) {

        $time = time();
        $this->code = mb_substr($str_notice,0,4,'utf-8');
        $this->created_by = $id_user;
        $this->updated_by = $id_user;
        $this->created_at = $time;
        $this->expire_at = $time + self::timeoff;
        $message = 'ok';

        return $message;
    }
}
