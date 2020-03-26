<?php
namespace backend\models\rbac;

use backend\controllers\MyException;
use backend\models\BaseModel;
use backend\models\redis\MyRedis;
use Yii;
use yii\db\ActiveRecord;

class Role extends BaseModel
{
    private $baseColumns = 'id,name,has_pop_code,account_is_my_code,use_parent_diamond,look_parent';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_rbac_role';
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
     * 添加一角色
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
                $this->MyRedis->clear("role*");
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 获取所有的角色
     * @return array
     */
    public function tableList()
    {
        return self::find()->asArray()->all();
    }

    /**
     * 根据id查询多条数据
     * @param $ids array
     * @return array
     */
    public function finds($ids)
    {
        return self::find()->where(['in','id',$ids])->asArray()->all();
    }

    /**
     * @param $id 角色id
     * @return bool
     * @throws \yii\db\Exception
     */
    public function del($id)
    {
        //开启事务
        $tr = Yii::$app->db->beginTransaction();
        //删除角色
        self::deleteAll("id=:id",[':id'=>$id]);
        //删除这个角色的所有关系
        $RoleRelationObj = new RoleRelation();
        $RoleRelationObj->delByRole($id);
        $this->MyRedis->clear("role*");
        $tr->commit();
        return true;
    }

    /**
     *  查找基本信息
     */
    public function findBase($id)
    {
        $redisKey="role:".$id;
        $redisData = $this->MyRedis->get($redisKey);
        if( empty($redisData) )
        {
            $data = self::find()->select($this->baseColumns)->where("id=:id",[':id'=>$id])->asArray()->one();
            $this->MyRedis->set($redisKey, json_encode($data) );
            return $data;
        }else{
            return json_decode($redisData, true);
        }
    }

    /**
     *   获取超级管理员id
     */
    public function getAdminId()
    {
        $obj = self::find()->select('id')->where("name=:name",[':name'=>"超级管理员"])->asArray()->one();
        return $obj['id'];
    }

    /**
     *   获取指定角色的名称
     */
    public function findRoleByName($nameArr)
    {
        return self::find()->where(['in','name',$nameArr])->asArray()->all();
    }

}
