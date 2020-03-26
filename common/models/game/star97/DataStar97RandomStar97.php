<?php
namespace common\models\game\star97;

use Yii;

/**
 * This is the model class for table "data_prefab_random_star97".
 *
 * @property integer $id
 * @property integer $prefab_id
 * @property double $all_orange
 * @property double $all_bell
 * @property double $all_mango
 * @property double $star_two
 * @property double $star_three
 * @property double $star_four
 * @property double $all_fruits
 * @property double $all_bars
 * @property double $two_seven
 * @property double $three_seven
 * @property double $four_seven
 */
class DataStar97RandomStar97 extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_prefab_random_star97';
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
            [['all_orange', 'all_bell', 'all_mango', 'star_two', 'star_three', 'star_four', 'all_fruits', 'all_bars', 'two_seven', 'three_seven', 'four_seven'], 'number'],
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
            'all_orange' => 'All Orange',
            'all_bell' => 'All Bell',
            'all_mango' => 'All Mango',
            'star_two' => 'Star Two',
            'star_three' => 'Star Three',
            'star_four' => 'Star Four',
            'all_fruits' => 'All Fruits',
            'all_bars' => 'All Bars',
            'two_seven' => 'Two Seven',
            'three_seven' => 'Three Seven',
            'four_seven' => 'Four Seven',
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
		'star_two' => array(
                        'name' => 'star_two',
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
                        'label'=>$this->getAttributeLabel('star_two'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'star_three' => array(
                        'name' => 'star_three',
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
                        'label'=>$this->getAttributeLabel('star_three'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'star_four' => array(
                        'name' => 'star_four',
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
                        'label'=>$this->getAttributeLabel('star_four'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_fruits' => array(
                        'name' => 'all_fruits',
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
                        'label'=>$this->getAttributeLabel('all_fruits'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_bars' => array(
                        'name' => 'all_bars',
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
                        'label'=>$this->getAttributeLabel('all_bars'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'two_seven' => array(
                        'name' => 'two_seven',
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
                        'label'=>$this->getAttributeLabel('two_seven'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'three_seven' => array(
                        'name' => 'three_seven',
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
                        'label'=>$this->getAttributeLabel('three_seven'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'four_seven' => array(
                        'name' => 'four_seven',
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
                        'label'=>$this->getAttributeLabel('four_seven'),
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
