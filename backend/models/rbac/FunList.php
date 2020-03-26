<?php
namespace backend\models\rbac;

use backend\models\BaseModel;
use Yii;
use yii\db\ActiveRecord;

class FunList extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_rbac_function_list';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function getFunSort()
    {
        return $this->hasOne(FunSort::className(),['id'=>'classify']);
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
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 分页获取所有的后台功能权限
     * @return array
     */
    public function page($pageNo, $pageSize)
    {
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        return self::find()->where('status = 1')->orderBy('id desc')->offset($offset)->limit($limit)->asArray()->all();
    }

    /**
     * 获取功能权限列表 按level排序
     * @return array
     */
    public function tableList()
    {
        return self::find()->joinWith('funSort as funSort')->where('status = 1')->orderBy('funSort.level desc,admin_rbac_function_list.level desc')->asArray()->all();
//        foreach ($data as $key => $val)
//        {
//            $data[$key]['classify'] = $val['funSort']['name'];
//        }
//        return $data;
    }

    /**
     *   获取所有的分类
     */
    public function getAllClassify()
    {
        return self::find()->select('classify')->joinWith('funSort as funSort')->orderBy('funSort.level desc,admin_rbac_function_list.level desc')->groupBy('classify')->asArray()->all();
    }

    /**
     *  获取最大条数
     */
    public function accountNum()
    {
        return self::find()->where('status = 1')->count();
    }

    /**
     * 删除一个后台功能权限
     * @param $id
     * @return int 删除的个数
     */
    public function del($id)
    {
        return self::deleteAll("id=:id",[':id'=>$id]);
    }


    /**
     * 给账号添加角色后台功能权限
     * @param $accountId
     * @param $roleId
     * @return bool
     */
    public function addRoleMenu($accountId, $roleId)
    {
        //删除这个账号的所有权限
        $time = time();
        $MenuAccountObj = new MenuAccount();
        MenuAccount::deleteAll("account_id=:id",[':id'=>$accountId]);
        //获取这个角色的所有权限
        $MenuRoleObj = new MenuRole();
        $MenuRoleObj = $MenuRoleObj->findbyRole($roleId);
        //添加这个账号所有权限
        foreach ($MenuRoleObj as $val)
        {
            $data = array(
                'account_id' => $accountId,
                'menu_two_id'=> $val['menu_two_id'],
                'admin_id'   => $this->loginId,
                'created_at' => $time,
            );
            $MenuAccountObj->add($data);
        }
        return true;
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
     * 根据Url查询一条记录
     * @param $url string 访问地址
     * @param $gameName string 游戏名称
     * @return array
     */
    public function findByUrl($url, $gameName)
    {
        return self::find()->where("url = :url and game = :game",array(":url"=>$url,":game"=>$gameName))->asArray()->one();
        //return self::find()->where("url = :url",array(":url"=>$url))->asArray()->one();
    }

    /**
     * 根据Url和游戏名称
     * @param $url string 访问地址
     * @param $gameName string 游戏名称
     * @return array
     */
    public function findByUrlGameName($url, $gameName){
        return self::find()->where("url = :url and game = :game",array(":url"=>$url,":game"=>$gameName))->asArray()->one();
    }

    /**
     *  查找基本信息
     */
    public function findBase($id)
    {
        return self::find()->select('id,,name,classify,url,type,status')->where("id=:id",[':id'=>$id])->asArray()->one();
    }

}
