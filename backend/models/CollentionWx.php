<?php
namespace backend\models;

use Yii;

/**
 * This is the model class for table "collention_wx".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $role_name
 * @property string $wx_account
 * @property string $wx_name
 * @property integer $status
 * @property integer $default_show
 * @property string $promo_code
 * @property integer $create_time
 * @property integer $update_time
 */
class CollentionWx extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'collention_wx';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'default_show', 'create_time', 'update_time'], 'integer'],
            [['wx_account', ], 'required','message'=>'微信账号不能为空'],
            [['role_name', 'wx_account', 'wx_name', 'promo_code'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'role_name' => 'Role Name',
            'wx_account' => 'Wx Account',
            'wx_name' => 'Wx Name',
            'status' => 'Status',
            'default_show' => 'Default Show',
            'promo_code' => 'Promo Code',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    public static function getAccountList($user_id = 0){
        $model = CollentionWx::find()->where(['status'=>10])->andFilterWhere(['user_id'=>$user_id])->orderBy('default_show desc')->all();
        $data =[];
        foreach($model as $key => $val) {
            $data[$val->id] = $val->wx_account;
        }
        return $data;
    }

    /**
     * 返回数据库字段信息，仅在生成CRUD时使用，如不需要生成CRUD，请注释或删除该getTableColumnInfo()代码
     * COLUMN_COMMENT可用key如下:
     * label - 显示的label
     * inputType 控件类型, 暂时只支持text,hidden  // select,checkbox,radio,file,password,
     * isEdit   是否允许编辑，如果允许编辑将在添加和修改时输入
     * isSearch 是否允许搜索
     * isDisplay 是否在列表中显示
     * isOrder 是否排序
     * udc - udc code，inputtype为select,checkbox,radio三个值时用到。
     * 特别字段：
     * id：主键。必须含有主键，统一都是id
     * create_date: 创建时间。生成的代码自动赋值
     * update_date: 修改时间。生成的代码自动赋值
     */
    public function getTableColumnInfo()
    {
        return array(
            'id' => array(
                'name' => 'id',
                'allowNull' => false,
//                         'autoIncrement' => true,
//                         'comment' => '',
//                         'dbType' => "int(11)",
                'defaultValue' => '',
                'enumValues' => null,
                'isPrimaryKey' => true,
                'phpType' => 'integer',
                'precision' => '11',
                'scale' => '',
                'size' => '11',
                'type' => 'integer',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('id'),
                'inputType' => 'hidden',
                'isEdit' => true,
                'isSearch' => true,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'user_id' => array(
                'name' => 'user_id',
                'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '用户id',
//                         'dbType' => "int(11)",
                'defaultValue' => '0',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'integer',
                'precision' => '11',
                'scale' => '',
                'size' => '11',
                'type' => 'integer',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('user_id'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'role_name' => array(
                'name' => 'role_name',
                'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => ' 角色名称',
//                         'dbType' => "varchar(255)",
                'defaultValue' => '',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'string',
                'precision' => '255',
                'scale' => '',
                'size' => '255',
                'type' => 'string',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('role_name'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'wx_account' => array(
                'name' => 'wx_account',
                'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '微信账号',
//                         'dbType' => "varchar(255)",
                'defaultValue' => '',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'string',
                'precision' => '255',
                'scale' => '',
                'size' => '255',
                'type' => 'string',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('wx_account'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'wx_name' => array(
                'name' => 'wx_name',
                'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '微信昵称',
//                         'dbType' => "varchar(255)",
                'defaultValue' => '',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'string',
                'precision' => '255',
                'scale' => '',
                'size' => '255',
                'type' => 'string',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('wx_name'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'status' => array(
                'name' => 'status',
                'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '状态',
//                         'dbType' => "tinyint(3)",
                'defaultValue' => '10',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'integer',
                'precision' => '3',
                'scale' => '',
                'size' => '3',
                'type' => 'smallint',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('status'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'default_show' => array(
                'name' => 'default_show',
                'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '是否默认前台显示  0=否',
//                         'dbType' => "tinyint(2)",
                'defaultValue' => '0',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'integer',
                'precision' => '2',
                'scale' => '',
                'size' => '2',
                'type' => 'smallint',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('default_show'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'promo_code' => array(
                'name' => 'promo_code',
                'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '推广号',
//                         'dbType' => "varchar(255)",
                'defaultValue' => '',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'string',
                'precision' => '255',
                'scale' => '',
                'size' => '255',
                'type' => 'string',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('promo_code'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'create_time' => array(
                'name' => 'create_time',
                'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '创建时间',
//                         'dbType' => "int(11)",
                'defaultValue' => '0',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'integer',
                'precision' => '11',
                'scale' => '',
                'size' => '11',
                'type' => 'integer',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('create_time'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'update_time' => array(
                'name' => 'update_time',
                'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '修改时间',
//                         'dbType' => "int(11)",
                'defaultValue' => '0',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'integer',
                'precision' => '11',
                'scale' => '',
                'size' => '11',
                'type' => 'integer',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('update_time'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
        );

    }

}
