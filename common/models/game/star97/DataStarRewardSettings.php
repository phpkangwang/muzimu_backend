<?php
namespace common\models\game\star97;

use Yii;

/**
 * This is the model class for table "data_star_reward_settings".
 *
 * @property integer $id
 * @property integer $star_reward_appearance_base_count
 * @property integer $double_time_base_count
 * @property integer $three_time_base_count
 * @property integer $four_time_base_count
 */
class DataStarRewardSettings extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_star_reward_settings';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('core_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['star_reward_appearance_base_count', 'double_time_base_count', 'three_time_base_count', 'four_time_base_count'], 'integer'],
            [['star_reward_appearance_base_count', 'double_time_base_count', 'three_time_base_count', 'four_time_base_count'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'star_reward_appearance_base_count' => '明星奖出奖几率基数（填100代表百分之一）',
            'double_time_base_count' => '两倍占比数',
            'three_time_base_count' => '三倍占比数',
            'four_time_base_count' => '四倍占比数',
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
		'star_reward_appearance_base_count' => array(
                        'name' => 'star_reward_appearance_base_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '明星奖出奖几率基数（填100代表百分之一）',
//                         'dbType' => "int(11)",
                        'defaultValue' => '100',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('star_reward_appearance_base_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'double_time_base_count' => array(
                        'name' => 'double_time_base_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '两倍占比数',
//                         'dbType' => "int(11)",
                        'defaultValue' => '50',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('double_time_base_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'three_time_base_count' => array(
                        'name' => 'three_time_base_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '三倍占比数',
//                         'dbType' => "int(11)",
                        'defaultValue' => '30',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('three_time_base_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'four_time_base_count' => array(
                        'name' => 'four_time_base_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '四倍占比数',
//                         'dbType' => "int(11)",
                        'defaultValue' => '20',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('four_time_base_count'),
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
