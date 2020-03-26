<?php
namespace backend\models\rbac;

use backend\models\BaseModel;
use Yii;
use yii\db\ActiveRecord;

class FunRole extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_rbac_function_role';
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
     * 添加一个后台角色功能权限
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
     * 获取一个角色的所有功能权限
     * @param $roleId 角色id
     * @return array
     */
    public function findbyRole($roleId)
    {
        $data = self::find()->where("role_id=:id",[':id'=>$roleId])->asArray()->all();
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

    /**
     * 删除一个角色的所有权限
     * @param $roleId 角色id
     * @return int 删除的个数
     */
    public function del($roleId)
    {
        return self::deleteAll("role_id=:id",[':id'=>$roleId]);
    }

}
