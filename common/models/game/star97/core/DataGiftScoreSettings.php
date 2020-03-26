<?php
namespace common\models\game\star97\core;

use Yii;

/**
 * This is the model class for table "data_gift_score_settings".
 *
 * @property integer $id
 * @property string $room_info_list_id
 * @property integer $init_gift
 * @property integer $gift_one
 * @property integer $gift_two
 * @property integer $gift_three
 * @property integer $min_gift
 */
class DataGiftScoreSettings extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_gift_score_settings';
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
            [['init_gift', 'gift_one', 'gift_two', 'gift_three', 'min_gift'], 'integer'],
            [['room_info_list_id'], 'string', 'max' => 25]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_info_list_id' => 'Room Info List ID',
            'init_gift' => '机台的初始彩金值',
            'gift_one' => 'Gift One',
            'gift_two' => 'Gift Two',
            'gift_three' => 'Gift Three',
            'min_gift' => '彩金拉完后的初始值',
        ];
    }

    /**
     * 查找基本数据
     * @param $id
     */
    public function findBase($id)
    {
        $redisKey="game:DataGiftScoreSettings:".$id;
        $redisData = $this->MyRedis->get($redisKey);
        if( empty($redisData) ) {
            $obj = self::find()->where(['id'=>$id])->asArray()->one();
            $this->MyRedis->set($redisKey, json_encode($obj));
            return $obj;
        }else{
            return json_decode($redisData, true);
        }
    }

    /**
     * 根据data_room_info_list的id查找基本数据
     * @param $id
     */
    public function findByRoomId($id)
    {
        $redisKey="game:DataGiftScoreSettings:findByRoomId:".$id;
        $redisData = $this->MyRedis->get($redisKey);
        if( empty($redisData) ) {
            $obj = self::find()->where(['room_info_list_id'=>$id])->asArray()->one();
            $this->MyRedis->set($redisKey, json_encode($obj));
            return $obj;
        }else{
            return json_decode($redisData, true);
        }
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
//                         'comment' => '',
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
		'init_gift' => array(
                        'name' => 'init_gift',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '机台的初始彩金值',
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
                        'label'=>$this->getAttributeLabel('init_gift'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'gift_one' => array(
                        'name' => 'gift_one',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
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
                        'label'=>$this->getAttributeLabel('gift_one'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'gift_two' => array(
                        'name' => 'gift_two',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
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
                        'label'=>$this->getAttributeLabel('gift_two'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'gift_three' => array(
                        'name' => 'gift_three',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
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
                        'label'=>$this->getAttributeLabel('gift_three'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'min_gift' => array(
                        'name' => 'min_gift',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '彩金拉完后的初始值',
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
                        'label'=>$this->getAttributeLabel('min_gift'),
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
