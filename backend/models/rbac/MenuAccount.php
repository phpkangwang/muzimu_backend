<?php
namespace backend\models\rbac;

use backend\models\BaseModel;
use Yii;
use yii\db\ActiveRecord;

class MenuAccount extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_rbac_menu_account';
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
     * 添加账号菜单权限
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
     * 检查账号是否拥有这个权限
     * @param $accountId
     * @param $menuTwoId
     * @return bool
     */
    public function check($accountId, $menuTwoId)
    {
        $obj = self::find()->where('account_id = :account_id and menu_two_id = :menu_two_id',[':account_id'=>$accountId, ':menu_two_id'=>$menuTwoId])->one();
        if( empty($obj) )
        {
            return false;
        }else{
            return true;
        }
    }

    /**
     * 根据账号和菜单查找是否有这个权限
     * @param $accountId
     * @param $menuTwoId
     * @return bool
     */
    public function findByAcMe($accountId, $menuTwoId)
    {
        return self::find()->where('account_id = :account_id and menu_two_id = :menu_two_id',[':account_id'=>$accountId, ':menu_two_id'=>$menuTwoId])->one();
    }

    /**
     * 查找这个号拥有的所有的菜单权限
     * @param $accountId
     * @return array
     */
    public function findByAccount($accountId)
    {
        return self::find()->where('account_id = :account_id ',[':account_id'=>$accountId])->asArray()->all();
    }

    /**
     * 删除一个号的所有权限
     * @param $accountId 角色id
     * @return int 删除的个数
     */
    public function del($accountId)
    {
        return self::deleteAll("account_id=:id",[':id'=>$accountId]);
    }

}
