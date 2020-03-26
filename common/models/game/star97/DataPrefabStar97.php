<?php
namespace common\models\game\star97;

use Yii;

/**
 * This is the model class for table "data_prefab_star97".
 *
 * @property integer $id
 * @property integer $prefab_id
 * @property double $five_seven
 * @property double $six_seven
 * @property double $seven_seven
 * @property double $eight_seven
 * @property double $all_cherry
 * @property double $all_orange
 * @property double $all_mango
 * @property double $all_xigua
 * @property double $all_bell
 * @property double $all_red
 * @property double $all_yellow
 * @property double $all_blue
 * @property double $all_seven
 */
class DataPrefabStar97 extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_prefab_star97';
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
            [['prefab_id'], 'integer'],
            [['five_seven', 'six_seven', 'seven_seven', 'eight_seven', 'all_cherry', 'all_orange', 'all_mango', 'all_xigua', 'all_bell', 'all_red', 'all_yellow', 'all_blue', 'all_seven'], 'number'],
            [['prefab_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'prefab_id' => '档位',
            'five_seven' => 'Five Seven',
            'six_seven' => 'Six Seven',
            'seven_seven' => 'Seven Seven',
            'eight_seven' => 'Eight Seven',
            'all_cherry' => 'All Cherry',
            'all_orange' => 'All Orange',
            'all_mango' => 'All Mango',
            'all_xigua' => 'All Xigua',
            'all_bell' => 'All Bell',
            'all_red' => 'All Red',
            'all_yellow' => 'All Yellow',
            'all_blue' => 'All Blue',
            'all_seven' => 'All Seven',
        ];
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
    public function getTableColumnInfo(){
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
                        'label'=>$this->getAttributeLabel('id'),
                        'inputType' => 'hidden',
                        'isEdit' => true,
                        'isSearch' => true,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'prefab_id' => array(
                        'name' => 'prefab_id',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '档位',
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
                        'label'=>$this->getAttributeLabel('prefab_id'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'five_seven' => array(
                        'name' => 'five_seven',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('five_seven'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'six_seven' => array(
                        'name' => 'six_seven',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('six_seven'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'seven_seven' => array(
                        'name' => 'seven_seven',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('seven_seven'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'eight_seven' => array(
                        'name' => 'eight_seven',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('eight_seven'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_cherry' => array(
                        'name' => 'all_cherry',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_cherry'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_orange' => array(
                        'name' => 'all_orange',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_orange'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_mango' => array(
                        'name' => 'all_mango',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_mango'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_xigua' => array(
                        'name' => 'all_xigua',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_xigua'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_bell' => array(
                        'name' => 'all_bell',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_bell'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_red' => array(
                        'name' => 'all_red',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_red'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_yellow' => array(
                        'name' => 'all_yellow',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_yellow'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_blue' => array(
                        'name' => 'all_blue',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_blue'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_seven' => array(
                        'name' => 'all_seven',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_seven'),
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
