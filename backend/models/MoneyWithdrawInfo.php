<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "money_withdraw_info".
 *
 * @property integer $id
 * @property string $username
 * @property string $nickname
 * @property string $seoid
 * @property string $pay_account
 * @property string $account_type
 * @property integer $diamond
 * @property double $money
 * @property string $created_at
 * @property string $updated_at
 * @property integer $status
 * @property string $comment
 * @property string $account_name
 * @property string $bank_type
 * @property string $bank_branch
 * @property string $operator
 */
class MoneyWithdrawInfo extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'money_withdraw_info';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['diamond', 'created_at', 'updated_at', 'status'], 'integer'],
            [['money'], 'number'],
            ['comment', 'string'],
            [['username', 'nickname', 'seoid', 'pay_account', 'account_type','account_name', 'bank_type', 'bank_branch','operator'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'username' => '游戏账号',
            'nickname' => '游戏昵称',
            'seoid' => '代理商',
            'pay_account' => '提现账号',
            'account_type' => '提现账号类型',
            'diamond' => '提现钻石',
            'money' => '实际提现金额',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
            'status' => '状态',
            'comment' => '备注',
            'account_name' => '收款人',
            'bank_type' => '开户银行',
            'bank_branch' => '开户支行',
            'operator' => '操作人',
        ];
    }

    public function getSetStatus()
    {
        $arr = Yii::$app->params['withdraw_order'];
        $str = null;
        switch ($this->status){
            case 10:$str = '<span class="btn btn-danger btn-xs">'.$arr[$this->status].'</span>';break;
            case 20:$str = '<span class="btn btn-success btn-xs">'.$arr[$this->status].'</span>';break;
            case 30:$str = '<span class="btn btn-primary btn-xs">'.$arr[$this->status].'</span>';break;
        }
        return $str;
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
//                         'comment' => 'id',
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
            'username' => array(
                'name' => 'username',
                'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '游戏账号',
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
                'label' => $this->getAttributeLabel('username'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'nickname' => array(
                'name' => 'nickname',
                'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '游戏昵称',
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
                'label' => $this->getAttributeLabel('nickname'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'seoid' => array(
                'name' => 'seoid',
                'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '代理商',
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
                'label' => $this->getAttributeLabel('seoid'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'pay_account' => array(
                'name' => 'pay_account',
                'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '提现账号',
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
                'label' => $this->getAttributeLabel('pay_account'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'account_type' => array(
                'name' => 'account_type',
                'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '提现账号类型',
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
                'label' => $this->getAttributeLabel('account_type'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'diamond' => array(
                'name' => 'diamond',
                'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '提现钻石',
//                         'dbType' => "int(255)",
                'defaultValue' => '',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'integer',
                'precision' => '255',
                'scale' => '',
                'size' => '255',
                'type' => 'integer',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('diamond'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'money' => array(
                'name' => 'money',
                'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '实际提现金额',
//                         'dbType' => "float(11,0)",
                'defaultValue' => '',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'double',
                'precision' => '11',
                'scale' => '0',
                'size' => '11',
                'type' => 'float',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('money'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'created_at' => array(
                'name' => 'created_at',
                'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '创建时间',
//                         'dbType' => "bigint(20)",
                'defaultValue' => '',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'string',
                'precision' => '20',
                'scale' => '',
                'size' => '20',
                'type' => 'bigint',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('created_at'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'updated_at' => array(
                'name' => 'updated_at',
                'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '更新时间',
//                         'dbType' => "bigint(20)",
                'defaultValue' => '',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'string',
                'precision' => '20',
                'scale' => '',
                'size' => '20',
                'type' => 'bigint',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('updated_at'),
                'inputType' => 'text',
                'isEdit' => true,
                'isSearch' => false,
                'isDisplay' => true,
                'isSort' => true,
//                         'udc'=>'',
            ),
            'status' => array(
                'name' => 'status',
                'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '状态',
//                         'dbType' => "int(11)",
                'defaultValue' => '10',
                'enumValues' => null,
                'isPrimaryKey' => false,
                'phpType' => 'integer',
                'precision' => '11',
                'scale' => '',
                'size' => '11',
                'type' => 'integer',
                'unsigned' => false,
                'label' => $this->getAttributeLabel('status'),
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
