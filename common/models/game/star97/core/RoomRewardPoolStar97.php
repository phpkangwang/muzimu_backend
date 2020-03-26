<?php
namespace common\models\game\star97\core;

use Yii;

/**
 * This is the model class for table "room_reward_pool_star97".
 *
 * @property integer $id
 * @property string $room_info_list_id
 * @property double $play_add_buff_count
 * @property integer $current_reward_type
 * @property double $current_buff_count
 * @property double $all_seven_total_count
 * @property double $all_cherry_total_count
 * @property double $all_red_total_count
 * @property double $all_yellow_total_count
 * @property double $all_blue_total_count
 */
class RoomRewardPoolStar97 extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'room_reward_pool_star97';
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
            [['play_add_buff_count', 'current_buff_count', 'all_seven_total_count', 'all_cherry_total_count', 'all_red_total_count', 'all_yellow_total_count', 'all_blue_total_count'], 'number'],
            [['current_reward_type'], 'integer'],
            [['room_info_list_id'], 'string', 'max' => 25],
            [['current_reward_type','play_add_buff_count', 'current_buff_count', 'all_seven_total_count', 'all_cherry_total_count', 'all_red_total_count', 'all_yellow_total_count', 'all_blue_total_count'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_info_list_id' => '所属游戏的房间类型 6_4这种',
            'play_add_buff_count' => '每局累积的buff值',
            'current_reward_type' => '当前奖池累积出奖类型',
            'current_buff_count' => '当前奖池累积buff值',
            'all_seven_total_count' => '9个7触顶值',
            'all_cherry_total_count' => '全盘樱桃触顶值',
            'all_red_total_count' => '全盘红BAR触顶值',
            'all_yellow_total_count' => '全盘黄BAR触顶值',
            'all_blue_total_count' => '全盘蓝BAR触顶值',
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
		'room_info_list_id' => array(
                        'name' => 'room_info_list_id',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '所属游戏的房间类型 6_4这种',
//                         'dbType' => "varchar(25)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '25',
                        'scale' => '',
                        'size' => '25',
                        'type' => 'string',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('room_info_list_id'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'play_add_buff_count' => array(
                        'name' => 'play_add_buff_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '每局累积的buff值',
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
                        'label'=>$this->getAttributeLabel('play_add_buff_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'current_reward_type' => array(
                        'name' => 'current_reward_type',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '当前奖池累积出奖类型',
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
                        'label'=>$this->getAttributeLabel('current_reward_type'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'current_buff_count' => array(
                        'name' => 'current_buff_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '当前奖池累积buff值',
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
                        'label'=>$this->getAttributeLabel('current_buff_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_seven_total_count' => array(
                        'name' => 'all_seven_total_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '9个7触顶值',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '450800',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_seven_total_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_cherry_total_count' => array(
                        'name' => 'all_cherry_total_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '全盘樱桃触顶值',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '174800',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_cherry_total_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_red_total_count' => array(
                        'name' => 'all_red_total_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '全盘红BAR触顶值',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '234800',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_red_total_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_yellow_total_count' => array(
                        'name' => 'all_yellow_total_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '全盘黄BAR触顶值',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '174800',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_yellow_total_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_blue_total_count' => array(
                        'name' => 'all_blue_total_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '全盘蓝BAR触顶值',
//                         'dbType' => "double(11,2)",
                        'defaultValue' => '246800',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'double',
                        'precision' => '11',
                        'scale' => '2',
                        'size' => '11',
                        'type' => 'double',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_blue_total_count'),
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
