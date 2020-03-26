<?php
namespace backend\models\rbac;

use backend\models\BaseModel;
use Yii;
use yii\db\ActiveRecord;

class RbacMenuOne extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_rbac_menu_one';
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
//            [['module_id', 'display_order'], 'integer'],
//            [['create_date', 'update_date'], 'safe'],
//            [['code', 'entry_right_name', 'action', 'create_user', 'update_user'], 'string', 'max' => 50],
//            [['menu_name', 'display_label', 'entry_url'], 'string', 'max' => 200],
//            [['des'], 'string', 'max' => 400],
//            [['controller'], 'string', 'max' => 100],
//            [['has_lef'], 'string', 'max' => 1]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
        ];
    }

    /**
     * 添加一级菜单
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
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }

    }

    /**
     * 获取一级菜单列表 按level排序
     * @return array
     */
    public function tableList()
    {
        return self::find()->orderBy('level asc')->asArray()->all();
    }

    /**
     * 删除一级菜单 所有的一级菜单下的二级菜单都被删除
     * @param $id
     * @throws \yii\db\Exception
     */
    public function del($id)
    {
        $tr = Yii::$app->db->beginTransaction();
        //删除一级菜单
        self::deleteAll("id=:id",[':id'=>$id]);
        //删除一级菜单下面的所有二级菜单
        $rbacMenuTwoObj = new RbacMenuTwo();
        $rbacMenuTwoObj->delByMenuOne($id);
        $tr->commit();
        return;
    }

    /**
     * 根据id查询多条数据
     * @param $ids array
     * @return array
     */
    public function finds($ids)
    {
        return self::find()->where(['in','id',$ids])->orderBy('level asc')->asArray()->all();
    }

}
