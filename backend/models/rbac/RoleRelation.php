<?php
namespace backend\models\rbac;

use backend\controllers\MyException;
use backend\models\BaseModel;
use backend\models\redis\MyRedis;
use Yii;
use yii\db\ActiveRecord;

class RoleRelation extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_rbac_role_relation';
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
            [['son_role_id', 'parent_role_id'], 'required'],
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
     * 添加一角色关系
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
     *  获取所有的关系
     */
    public function findAllRelation()
    {
        $redisKey="role:findAllRoleRelation";
        $data = $this->MyRedis->get($redisKey);
        if( empty($data) )
        {
            $RoleObj = new Role();
            $obj = self::find()->asArray()->all();
            foreach ($obj as $key=>$val)
            {
                $obj[$key]['son_role_info'] = $RoleObj->findBase($val['son_role_id']);
                $obj[$key]['parent_role_info'] = $RoleObj->findBase($val['parent_role_id']);
            }
            $this->MyRedis->set($redisKey, json_encode($obj));
            return $obj;
        }else{
            return json_decode($data, true);
        }
    }

    /**
     * 获取所有的角色关系
     * @return array
     */
    public function tableList()
    {
        return self::find()->asArray()->all();
    }

    /**
     *  删除某个角色下的所有角色关系
     * @param $roleId 角色id
     * @return int 删除的个数
     */
    public function delByRole($roleId)
    {
        $this->MyRedis->clear("role*");
        return self::deleteAll("son_role_id=:id or parent_role_id =:id",[':id'=>$roleId]);
    }

    /**
     * 删除某个角色下的所有角色关系
     * @param $parentRoleId   父角色id
     * @param $sonRoleId     子角色id
     * @return false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delByParentSon($parentRoleId, $sonRoleId)
    {
        $this->MyRedis->clear("role*");
        $obj = self::find()->where("parent_role_id =:parent_role_id and son_role_id=:son_role_id",[':parent_role_id'=>$parentRoleId,':son_role_id'=>$sonRoleId])->one();
        return $obj->delete();
    }

    /**
     * 根据id删除角色id
     * @param $id 主键
     * @return false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function del($id)
    {
        $this->MyRedis->clear("role*");
        return self::findOne($id)->delete();
    }

    /**
     * 是否是我的下级，包括下级的下级
     * @param $parent  角色id
     * @param $son     角色id
     * @return bool
     */
    public function isSon($parent, $son)
    {
        $key = "role:isSon:".$parent.":".$son;
        $data = $this->MyRedis->get($key);
        if( empty($data) ) {
            if ($parent == $son) {
                return false;
            }
            $ids = $this->findAllSon($parent, false);
            $rs = in_array($son, $ids) ? true : false;
            $this->MyRedis->set($key, json_encode($rs));
            return $rs;
        }else{
            return json_decode($data, true);
        }
    }

    /**
     * @param $parent
     * @param bool $hasSelf 是否包含自己
     * @return array
     */
    public function findSon($parent, $hasSelf = true)
    {
        $parentRoleObjs = self::find()->where('parent_role_id = :parent ',array(':parent'=>$parent))->asArray()->all();
        $parentRoleIds = array_column($parentRoleObjs, 'son_role_id');
        $hasSelf ? array_push($parentRoleIds, $parent) : "";
        return $parentRoleIds;
    }

    /**
     * 获取这个角色的所有下级角色（包括下级的下级）
     * @param $parent
     * @param bool $hasSelf 是否包含自己
     * @return array
     */
    public function findAllSon($parent, $hasSelf = true)
    {
        $key = $hasSelf ? "role:findAllSon:".$parent.":1" : "role:findAllSon:".$parent.":0";
        $data = $this->MyRedis->get($key);
        if( empty($data) )
        {
            $sonArrIds = array();
            $this->recursionSon($sonArrIds, $parent);
            $hasSelf ? array_push($sonArrIds, $parent) : "";
            $this->MyRedis->set($key, json_encode($sonArrIds));
            return $sonArrIds;
        }else{
            return json_decode($data, true);
        }

    }
    //递归下级角色
    public function recursionSon(&$sonArrIds, $parent)
    {
        $obj = $this->findSon($parent,false);
        if( !empty($obj) ){
            foreach ( $obj as $v)
            {
                array_push( $sonArrIds, $v);
                $this->recursionSon($sonArrIds, $v);
            }
        }else{
            return $sonArrIds;
        }

    }

    /**
     * 我是否有直系下级
     * @param $parent
     * @return array
     */
    public function hasSon($parent)
    {
        $obj = self::find()->where('parent_role_id = :parent ',array(':parent'=>$parent))->asArray()->all();
        return empty($obj) ? false : true;
    }
}
