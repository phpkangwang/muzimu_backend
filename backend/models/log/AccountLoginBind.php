<?php
namespace backend\models\log;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\Tool;
use Yii;
use yii\db\ActiveRecord;
use backend\models\MyException;


class AccountLoginBind extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_account_login_bind';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['code', 'menu_name', 'module_id', 'entry_url', 'action', 'controller'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
//            'id' => '主键',
        ];
    }

    /**
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function add($data)
    {
        try{
            foreach ( $data as $key => $val )
            {
                $this->$key = $val;
            }
            if( $this->save() )
            {
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * @param $accountId
     * @return int
     */
    public function del($accountId)
    {
        return self::deleteAll("account_id=:id",[':id'=>$accountId]);
    }

    /**
     * 检查是否在指定的机器和浏览器上登录
     * @param $accountId
     * @return bool
     * @throws \yii\db\Exception
     */
    public function check($accountId)
    {
        try{
            $cookieName = 'newbackend'.$accountId;
            $obj = self::find()->where("account_id=:id",[':id'=>$accountId])->asArray()->one();
            if( empty($obj) )
            {
                return true;
            }else{
                if( !isset($_COOKIE[$cookieName]) || $obj['cookie'] != $_COOKIE[$cookieName] )
                {
                    throw new MyException( ErrorCode::ERROR_ACCOUNT_LOGIN_BIND );
                }
                return true;
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 账号是否绑定机器
     * @param $accountId
     * @return bool
     */
    public function isBind($accountId)
    {
        $obj = self::find()->where("account_id=:id",[':id'=>$accountId])->asArray()->one();
        if( empty($obj) )
        {
            return false;
        }else{
            return true;
        }
    }

}
