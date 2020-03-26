<?php
namespace backend\models;

use backend\models\rbac\AccountRelation;
use backend\models\rbac\RoleRelation;
use backend\models\redis\MyRedis;
use common\models\game\FivepkAccount;
use Yii;
use yii\db\ActiveRecord;
use backend\models\rbac\MenuAccount;
use backend\models\rbac\FunAccount;
use backend\models\rbac\FunRole;
use backend\models\rbac\MenuRole;


class Account extends BaseModel
{
    public $baseColumns = 'id,account,role,name,token,status,pop_code,remark,login_bind,admin_id,updated_at,created_at';

    public function __construct($config = [])
    {
        Factory::AccountController()->baseColumns($this->baseColumns);
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_account';
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
            ['account', 'required'],
            ['account', 'string', 'min' => 4],
            ['account', 'filter', 'filter' => 'trim'],
            [['account'], 'match', 'pattern' => '/^[A-Za-z0-9_]+$/u', 'message' => "只能是数字、字母、下划线"],
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
     *  关联Role对象
     * @return array
     */
    public function getRole()
    {
        return $this->hasOne(Role::className(), ['id' => 'role']);
    }

    /**
     *  关联RoleRelation对象
     * @return array
     */
    public function getRoleRelation()
    {
        return $this->hasOne(RoleRelation::className(), ['id' => 'odds_type_id']);
    }

    /**
     * 添加一个后台账户
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try{
            //开启事务
            $tr = Yii::$app->db->beginTransaction();

            foreach ( $data as $key => $val )
            {
                $this->$key = $val;
            }

            if( $this->save() )
            {
                $MyRedisObj = new MyRedis();
                $MyRedisObj->clear("account*");
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }

            //修改账户角色菜单权限
            $this->addRoleMenu($this->id, $this->role, $data['admin_id']);
            //修改账户角色功能权限
            $this->addFunMenu($this->id, $this->role, $data['admin_id']);
            $tr->commit();
            return $this->id;
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }



    /**
     * 分页获取所有的角色
     * @return array
     */
    public function page($pageNo, $pageSize, $where)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        return self::find()->select($this->baseColumns)->where($where)->offset($offset)->limit($limit)->asArray()->all();
    }

    /**
     * 通过账户查找这个账户
     * @param $account
     * @return mixed
     */
    public function findByAccount($account)
    {
        return self::find()->where("account=:id",[':id'=>$account])->one();
    }

    /**
     * 通过推广码查找这个账户
     * @param $popCode
     * @return mixed
     */
    public function findByPopCode($popCode)
    {
        return self::find()->select($this->baseColumns)->where("pop_code=:pop_code",[':pop_code'=>$popCode])->one();
    }

    /**
     * 通过name找这个账户
     * @param $name
     * @return mixed
     */
    public function findByName($name)
    {
        return self::find()->select($this->baseColumns)->where("name=:name",[':name'=>$name])->one();
    }

    /**
     * 通过角色查找这个账户
     * @param $RoleIds 角色ids
     * @return array
     */
    public function findByRoleIds($RoleIds)
    {
        return self::find()->select($this->baseColumns)->where(['in','role',$RoleIds])->asArray()->all();
    }

    public function likeName($name)
    {
        return self::find()->select('id,name')->where(['like','name',$name])->asArray()->all();
    }

    /**
     *  查找账户基本信息
     */
    public function findBase($id)
    {
        $redisKey="account:".$id;
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
     *  获取最大条数
     */
    public function accountNum($where)
    {
        return self::find()->where($where)->count();
    }

    /**
     * 删除一个账户
     * @param $id
     * @return int 删除的个数
     */
    public function del($id)
    {

        return self::deleteAll("id=:id",[':id'=>$id]);
    }

    /**
     * 修改账户
     * @param $data
     * @tr
     */
    public function updateAccount($data)
    {
        try{
            foreach ( $data as $key => $val )
            {
                $this->$key = $val;
            }
            $this->save();
            //删除账户redis缓存
            $redisKey="account:".$this->id;
            $this->MyRedis->clear($redisKey);
            return $this->id;
        }catch (MyException $e){
            $e->toJson($e->getMessage());
        }

    }

    /**
     * 给账号添加角色菜单权限
     * @param $accountId
     * @param $roleId
     * @return bool
     */
    public function addRoleMenu($accountId, $roleId, $adminId)
    {
        //删除这个账号的所有菜单权限
        MenuAccount::deleteAll("account_id=:id",[':id'=>$accountId]);
        $time = time();
        $MenuAccountObj = new MenuAccount();
        //获取这个角色的所有权限
        $MenuRoleObj = new MenuRole();
        $MenuRoleObj = $MenuRoleObj->findbyRole($roleId);
        //添加这个账号所有权限
        foreach ($MenuRoleObj as $val)
        {
            $data = array(
                'account_id' => $accountId,
                'menu_two_id'=> $val['menu_two_id'],
                'admin_id'   => $adminId,
                'created_at' => $time,
            );
            $MenuAccountObj->add($data);
        }
        return true;
    }

    /**
     * 给账号添加角色功能权限
     * @param $accountId
     * @param $roleId
     * @return bool
     */
    public function addFunMenu($accountId, $roleId, $adminId)
    {
        $time = time();
        //删除这个账号的所有菜单权限
        $FunAccountObj = new FunAccount();
        FunAccount::deleteAll("account_id=:id",[':id'=>$accountId]);
        //获取这个角色的所有权限
        $FunRoleObj = new FunRole();
        $FunRoleObj = $FunRoleObj->findbyRole($roleId);
        //添加这个账号所有权限
        foreach ($FunRoleObj as $val)
        {
            $data = array(
                'account_id' => $accountId,
                'function_id'=> $val['function_id'],
                'admin_id'   => $adminId,
                'created_at' => $time,
            );
            $FunAccountObj->add($data);
        }
        return true;
    }


    /**
     *  添加错误登陆次数
     */
    public function errorLoginTimes($type)
    {
        try{
            if($type == "add")
            {
                $this->error_login_times += 1;
            }else if($type == "init"){
                $this->error_login_times = 0;
            }
            $this->save();
            if($this->error_login_times >= 3)
            {
                $this->updateStatus($this->id,"forbid",$this->id);
                throw new MyException( ErrorCode::ERROR_ACCOUNT_LOGIN_LOCK );
            }
            return true;
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 修改账号状态
     * @param $type 对应表status
     * @return bool
     */
    public function updateStatus($id,$type,$admin_id)
    {
        $tableName = Account::tableName();
        if($type == "forbid"){

            $sql = "update {$tableName} set status = 2,admin_id={$admin_id} where id in ({$id}) ";
        }else if($type == "access"){
            $sql = "update {$tableName} set status = 1,error_login_times = 0,admin_id={$admin_id} where id = {$id} limit 1";
        }
        $this->db->createCommand($sql)->query();
        return true;
    }

    /**
     * 根据id查询多条数据
     * @param $ids
     * @param string $indexBy
     * @return array|ActiveRecord[]
     */
    public function finds($ids,$indexBy='')
    {
        $dbObj = self::find();
        if ($indexBy) {
            $dbObj->indexBy($indexBy);
        }
        return $dbObj->select($this->baseColumns)->where(['in', 'id', $ids])->asArray()->all();
    }

    /**
     * 获取我所有下级的推广码
     */
    public function findAllSonPopCode($id)
    {
        //查看这个用户的角色是否允许查看上级记录，如果允许则使用上级的推广号
        $id = $this->findCanUsePopCodeAccount($id);
        $ids = $this->AccountRelation->findAllSon($id, true);
        $accounts = $this->finds($ids);
        $popCodes = array();
        foreach ($accounts as $account){
            $pop_code = $account['pop_code'];
            if( !in_array($pop_code, $popCodes) && !empty($pop_code)){
                array_push($popCodes, $pop_code);
            }
        }
        return $popCodes;
    }

    /**
     * 对于有的账户可以查看上级推广号下的内容，这里返回最终的推广码
     *  @param $id 用户id
     * @return array
     */
    public function findCanUsePopCodeAccount($id)
    {
        $accountObj = self::findBase($id);
        //获取角色看是否可以使用
        $roleObj = $this->Role->findBase($accountObj['role']);
        if( $roleObj['look_parent'] == 2 ){//可以使用上级推广码
            //查找上级
            $AccountRelationObj = $this->AccountRelation->findDirectParent($accountObj['id']);
            $ParentObj = $this->Account->findBase($AccountRelationObj['parent_account_id']);
            return $this->findCanUsePopCodeAccount($ParentObj['id']);
        }else{
            return $accountObj['id'];
        }

    }

    /**
     *   获取所有的推广码
     */
    public function getAllPopCode()
    {
        $objs = self::find()->where('pop_code <> ""')->groupBy('pop_code')->asArray()->all();
        $popCodes = array_column($objs, 'pop_code');
        return $popCodes;
    }


    /**
     *   获取可以使用父级钻石的账户
     */
    public function getUseParentDiamond($id)
    {
        //获取用户信息
        $AccountObj = $this->Account->findBase($id);
        //获取role信息
        $RoleObj = $this->Role->findBase($AccountObj['role']);
        //可以使用
        if( $RoleObj['use_parent_diamond'] == 2){
            //获取父级账户
            $AccountRelationObj = $this->AccountRelation->findDirectParent($AccountObj['id']);
            $ParentObj = $this->Account->findBase($AccountRelationObj['parent_account_id']);
            if( empty($ParentObj) ){
                return $id;
            }
            return $this->Account->getUseParentDiamond($ParentObj['id']);
        }else{
            return $id;
        }
    }

    /**
     *  根据账户id获取角色名称
     * @param $id
     * @return mixed
     */
    public function getRoleName($id)
    {
        $AccountObj = self::findBase($id);
        $RoleObj = $this->Role->findBase($AccountObj['role']);
        return $RoleObj['name'];
    }

    /**
     *   获取某个id下的所有的推广员的推广码
     */
    public function getKFYPopCode($id)
    {
        $connection = Yii::$app->db;
        $sql = "select * from admin_account ac,admin_account_relation re,admin_rbac_role ro 
                where ac.role = ro.id and re.parent_account_id = {$id} and ac.id = re.son_account_id and ro.name='推广员'";
        $command = $connection->createCommand($sql);
        $data    = $command->queryAll();
        return array_column($data,'pop_code');
    }

    /**
     *   获取管理员id
     */
    public function getGLYId()
    {
        $connection = Yii::$app->db;
        $sql = "select ac.id from admin_account ac,admin_rbac_role ro where ro.name='超级管理员' limit 1";
        $command = $connection->createCommand($sql);
        $data    = $command->queryOne();
        return $data['id'];
    }

    //获取总代理
    public function getZDLFromStrIds($idArr)
    {
        $ids = implode($idArr,",");
        $sql = "SELECT admin_account.* 
                FROM admin_account
                INNER JOIN admin_rbac_role  on  admin_rbac_role.id= admin_account.role
                WHERE
                admin_account.id in({$ids})
                AND admin_rbac_role.`name` ='总代理';";
        $connection = self::getDb();
        $data = $connection->createCommand($sql)->queryAll();

        return $data;

    }


    /**
     *  根据用户id获取总代理id
     * @param $accountId
     * @return int
     */
    public function getZdlByAccountId($accountId)
    {
        //找到玩家用户信息
        $accountModel         = new Account();
        $accountRelationModel = new AccountRelation();
        $FivepkAccountModel   = new FivepkAccount();
        $FivepkAccountObj     = $FivepkAccountModel::findOne($accountId);
        if (!empty($FivepkAccountObj)) {
            //玩家推广号的后台用户id
            $popCode      = $FivepkAccountObj['seoid'];
            $AccountObj   = $accountModel->findByPopCode($popCode);
            $accountIdArr = $accountRelationModel->findAllParent($AccountObj['id']);
        } else {
            $accountIdArr = array();
        }

        $getId = 0;
        if (!empty($accountIdArr)) {
            $zdlObjs = $accountModel->getZDLFromStrIds($accountIdArr);
            if (isset($zdlObjs[0]['id'])) {
                $getId = $zdlObjs[0]['id'];
            }
        }
        return $getId;
    }


}
