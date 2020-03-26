<?php
namespace backend\models\rbac;

use backend\models\BaseModel;
use Yii;
use yii\db\ActiveRecord;

class FunAccount extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_rbac_function_account';
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
     * 添加一个后台账号的功能权限
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try{
            $obj = new self;
            foreach ( $data as $key => $val )
            {
                $obj->$key = $val;
            }
            if( $obj->save() )
            {
                return $obj->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 检查账号是否拥有这个权限
     * @param $accountId
     * @param $funId
     * @return bool
     */
    public function check($accountId, $funId)
    {
        $obj = self::find()->where('account_id = :account_id and function_id = :function_id',[':account_id'=>$accountId, ':function_id'=>$funId])->one();
        if( empty($obj) )
        {
            return false;
        }else{
            return true;
        }
    }

    /**
     * 根据账号和菜单查找是否有这个权限
     * @param $accountId
     * @param $funId
     * @return bool
     */
    public function findByAcMe($accountId, $funId)
    {
        return self::find()->where('account_id = :account_id and function_id = :function_id',[':account_id'=>$accountId, ':function_id'=>$funId])->one();
    }

    /**
     * 删除一个账号的所有功能权限
     * @param $accountId 账号id
     * @return int 删除的个数
     */
    public function del($accountId)
    {
        return self::deleteAll("account_id=:id",[':id'=>$accountId]);
    }

    /**
     * 获取一个账号的所有功能权限
     * @param $accountId 账号id
     * @return array
     */
    public function findbyAccount($accountId)
    {
        $data = self::find()->where("account_id=:id",[':id'=>$accountId])->asArray()->all();
        $funids = array();
        foreach ($data as $key => $val)
        {
            array_push($funids, $val['function_id']);
        }
        $funListObj = new FunList();
        $funListObj = $funListObj->finds($funids);
        foreach ( $data as $key => $val )
        {
            $data[$key]['funInfo'] = array();
            foreach ( $funListObj as $fun)
            {
                if( $val['function_id'] == $fun['id'] )
                {
                    $data[$key]['funInfo'] = $fun;
                }
            }
        }
        return $data;
    }


}
