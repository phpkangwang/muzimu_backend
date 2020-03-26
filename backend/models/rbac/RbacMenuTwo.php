<?php
namespace backend\models\rbac;

use backend\models\BaseModel;
use Yii;
use yii\db\ActiveRecord;

class RbacMenuTwo extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_rbac_menu_two';
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
//            'id' => '主键',
//            'code' => 'code',
//            'menu_name' => '名称',
//            'module_id' => '模块id',
//            'display_label' => '显示名',
//            'des' => '描述',
//            'display_order' => '显示顺序',
//            'entry_right_name' => '入口地址名称',
//            'entry_url' => '入口地址',
//            'action' => '操作ID',
//            'controller' => '控制器ID',
//            'has_lef' => '是否有子',
//            'create_user' => '创建人',
//            'create_date' => '创建时间',
//            'update_user' => '修改人',
//            'update_date' => '修改时间',
        ];
    }

    /**
     * 添加二级菜单
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
     * 获取二级菜单列表 按level排序
     * @return array
     */
    public function tableList()
    {
        //获取一级菜单列表
        $RbacMenuOneObj = new RbacMenuOne();
        $MenuOneList = $RbacMenuOneObj->tableList();
        $MenuTwoList = self::find()->orderBy('level asc')->asArray()->all();
        foreach ( $MenuOneList as $key => $oneList )
        {
            $MenuOneList[$key]['menuTwo'] = array();
            foreach( $MenuTwoList as $twoList )
            {
                if( $MenuOneList[$key]['id'] ==  $twoList['menu_one_id'] )
                {
                    array_push( $MenuOneList[$key]['menuTwo'], $twoList );
                }
            }
        }
        return $MenuOneList;
    }

    /**
     * 删除二级菜单
     * @param $id
     * @return int 删除的个数
     */
    public function del($id)
    {
        return self::deleteAll("id=:id",[':id'=>$id]);
    }

    /**
     * 删除某个一级菜单下所有的二级菜单
     * @param $menuOneId
     * @return int
     */
    public function delByMenuOne($menuOneId)
    {
        return self::deleteAll("menu_one_id=:id",[':id'=>$menuOneId]);
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
