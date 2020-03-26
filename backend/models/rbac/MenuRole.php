<?php
namespace backend\models\rbac;

use backend\models\BaseModel;
use Yii;
use yii\db\ActiveRecord;

class MenuRole extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_rbac_menu_role';
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
     * 给角色添加菜单权限
     * @param $data
     * @return array
     */
    public function add( $data )
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
        return "";
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

    /**
     * 获取一个角色的所有菜单权限
     * @param $roleId 角色id
     * @return array
     */
    public function findbyRole($roleId)
    {
        return self::find()->where("role_id=:id",[':id'=>$roleId])->asArray()->all();
    }

}
