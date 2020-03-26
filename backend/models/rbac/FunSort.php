<?php
namespace backend\models\rbac;

use backend\models\BaseModel;
use Yii;
use yii\db\ActiveRecord;

class FunSort extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_rbac_function_sort';
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
     * 添加一个后台功能权限
     * @param $data
     * @return bool
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
                throw new MyException(ErrorCode::ERROR_SYSTEM);
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }


    /**
     * 获取功能权限列表 按level排序
     * @return array
     */
    public function tableList()
    {
        return self::find()->orderBy('level desc')->asArray()->all();
    }


    /**
     * 删除
     * @param $id
     * @return int 删除的个数
     */
    public function del($id)
    {
        return self::deleteAll("id=:id",[':id'=>$id]);
    }


}
