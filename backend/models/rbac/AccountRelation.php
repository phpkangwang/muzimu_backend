<?php
namespace backend\models\rbac;

use backend\controllers\MyException;
use backend\models\Account;
use backend\models\BaseModel;
use backend\models\redis\MyRedis;
use Yii;
use yii\db\ActiveRecord;

class AccountRelation extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_account_relation';
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
     * 添加一账户关系
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try{
            $obj = new self();
            foreach ( $data as $key => $val )
            {
                $obj->$key = $val;
            }
            if( $obj->save() )
            {
                $MyRedisObj = new MyRedis();
                $MyRedisObj->clear("account*");
                return $obj->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 下级账户获取总代理id
     * @param $accountId
     * @return string
     */
    public function sonGetZdlId($accountId)
    {
        $AccountModel = new Account();
        $idArr = $this->findAllParent($accountId);
        $zdlObj = $AccountModel->getZDLFromStrIds($idArr);
        if( isset( $zdlObj[0] ) ){
            return $zdlObj[0]['id'];
        }
        return "";
    }

    //获取某个账户的直系下级账户
    public function findDirectSon($parentAccountId)
    {
       $rs = self::find()->where('parent_account_id = :parent_account_id',array(':parent_account_id'=>$parentAccountId))->asArray()->all();
        return array_column($rs, 'son_account_id');
    }

    //获取某个账户的直系上级账户
    public function findDirectParent($sonAccountId)
    {
        return self::find()->where('son_account_id = :son_account_id',array(':son_account_id'=>$sonAccountId))->asArray()->one();
    }

    //获取某个账户的所有直系上级账户
    public function findDirectParentAll($sonAccountId)
    {
        return self::find()->where('son_account_id = :son_account_id',array(':son_account_id'=>$sonAccountId))->asArray()->all();
    }

    /**
     * 获取指定角色的指定下级
     * @param $roleArr ['总代理','代理商','推广员'];
     * @param $parentAccountId
     * @return array
     */
    public function findSonInRole($roleArr, $parentAccountId){
        $needRoleObjs = $this->Role->findRoleByName($roleArr);
        $needRoleIds = array_column($needRoleObjs,'id');
        //获取直系下级
        $SonAccountIds = $this->AccountRelation->findDirectSon($parentAccountId);
        $SonAccountObjs = $this->Account->finds($SonAccountIds);

        $needAccountObjs = array();
        foreach ( $SonAccountObjs as $val){
            if( in_array( $val['role'], $needRoleIds)){
                array_push($needAccountObjs, $val);
            }
        }
        return $needAccountObjs;
    }

    /**
     *  获取所有的关系
     */
    public function findAllRelation()
    {
        $redisKey="role:findAllAccountRelation";
        $data = $this->MyRedis->get($redisKey);
        $AccountObj = new Account();
        if( empty($data) )
        {
            $obj = self::find()->asArray()->all();
            foreach ($obj as $key=>$val)
            {
                $obj[$key]['son_account_id'] = $AccountObj->findBase($val['son_account_id']);
                $obj[$key]['parent_account_id'] = $AccountObj->findBase($val['parent_account_id']);
            }
            $this->MyRedis->set($redisKey, json_encode($obj));
            return $obj;
        }else{
            return json_decode($data, true);
        }
    }


    /**
     * 删除一个账户关系
     * @param $id
     * @return int 删除的个数
     */
    public function del($roleId)
    {
        $MyRedisObj = new MyRedis();
        $MyRedisObj->clear("account*");
        return self::deleteAll("son_account_id=:id or parent_account_id =:id",[':id'=>$roleId]);
    }

    /**
     * 是否是我的下级，包括下级的下级
     * @param $parent
     * @param $son
     * @return bool
     */
    public function isSon($parent, $son)
    {
        $key = "account:isSon:".$parent.":".$son;
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
     * 获取这个账户的所有上级账户（包括上级的上级）
     * @param $parent
     * @param bool $hasSelf 是否包含自己
     * @return array
     */
    public function findAllParent($son, $hasSelf = true){
        $key = $hasSelf ? "account:findAllParent:".$son.":1" : "account:findAllParent:".$son.":0";
        $data = $this->MyRedis->get($key);
        if( empty($data) )
        {
            $parentArrIds = array();
            $this->recursionParent($parentArrIds, $son);
            $hasSelf ? array_push($parentArrIds, $son) : "";
            $this->MyRedis->set($key, json_encode($parentArrIds));
            return $parentArrIds;
        }else{
            return json_decode($data, true);
        }
    }

    //递归下级角色
    public function recursionParent(&$parentArrIds, $son)
    {
        $obj = $this->findParent($son,false);
        if( !empty($obj) ){
            foreach ( $obj as $v)
            {
                if( !in_array($v,$parentArrIds))
                {
                    array_push( $parentArrIds, $v);
                    $this->recursionParent($parentArrIds, $v);
                }
            }
        }else{
            return $parentArrIds;
        }

    }

    /**
     * 获取这个账户的所有下级账户（包括下级的下级）
     * @param $parent
     * @param bool $hasSelf 是否包含自己
     * @return array
     */
    public function findAllSon($parent, $hasSelf = true)
    {
        $key = $hasSelf ? "account:findAllSon:".$parent.":1" : "account:findAllSon:".$parent.":0";
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
                if( !in_array($v,$sonArrIds))
                {
                    array_push( $sonArrIds, $v);
                    $this->recursionSon($sonArrIds, $v);
                }

            }
        }else{
            return $sonArrIds;
        }

    }

    /**
     * @param $parent
     * @param bool $hasSelf 是否包含自己
     * @return array
     */
    public function findSon($parent, $hasSelf = true)
    {
        $parentObjs = self::find()->where('parent_account_id = :parent ',array(':parent'=>$parent))->asArray()->all();
        $parentIds = array_column($parentObjs, 'son_account_id');
        $hasSelf ? array_push($parentIds, $parent) : "";
        return $parentIds;
    }

    /**
     * @param $son
     * @param bool $hasSelf 是否包含自己
     * @return array
     */
    public function findParent($son, $hasSelf = true)
    {
        $parentObjs = self::find()->where('son_account_id = :son ',array(':son'=>$son))->asArray()->all();
        $parentIds = array_column($parentObjs, 'parent_account_id');
        $hasSelf ? array_push($parentIds, $son) : "";
        return $parentIds;
    }

    /**
     * 我是否有直系下级
     * @param $parent
     * @return array
     */
    public function hasSon($parent)
    {
        $obj = self::find()->where('parent_account_id = :parent ',array(':parent'=>$parent))->asArray()->all();
        return empty($obj) ? false : true;
    }

}
