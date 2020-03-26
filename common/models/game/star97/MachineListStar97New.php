<?php
namespace common\models\game\star97;

use common\models\DataRoomInfoList;
use common\models\game\FivepkPlayerInfo;
use common\models\game\star97\core\MachineRewardPoolStar97;
use Yii;
use yii\base\Exception;

/**
 * This is the model class for table "machine_list_star97".
 *
 * @property string $auto_id
 * @property integer $order_id
 * @property string $seo_machine_id
 * @property integer $all_fruits_base_count
 * @property integer $mixed_bars_base_count
 * @property integer $star_reward_appearance_base_count
 * @property integer $double_time_base_count
 * @property integer $three_time_base_count
 * @property integer $four_time_base_count
 * @property string $room_info_list_id
 * @property integer $grid_prefab_id
 * @property integer $seo_machine_type
 * @property string $account_id
 * @property integer $seo_machine_play_count
 * @property integer $gift
 * @property string $reservation_date
 * @property integer $machine_auto
 * @property string $create_date
 * @property integer $all_reward_base_count
 * @property integer $all_orange
 * @property integer $all_mango
 * @property integer $all_watermelon
 * @property integer $all_bell
 * @property integer $seven_reward_base_count
 * @property integer $five_seven
 * @property integer $six_seven
 * @property integer $seven_seven
 * @property integer $eight_seven
 * @property integer $current_reward_type
 */
class MachineListStar97New extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'machine_list_star97';
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
            [['order_id', 'all_fruits_base_count', 'mixed_bars_base_count', 'star_reward_appearance_base_count', 'double_time_base_count', 'three_time_base_count', 'four_time_base_count', 'grid_prefab_id', 'seo_machine_type', 'account_id', 'seo_machine_play_count', 'gift', 'machine_auto', 'all_reward_base_count', 'all_orange', 'all_mango', 'all_watermelon', 'all_bell', 'seven_reward_base_count', 'five_seven', 'six_seven', 'seven_seven', 'eight_seven','current_reward_type'], 'integer'],
            [['all_fruits_base_count', 'mixed_bars_base_count', 'star_reward_appearance_base_count', 'double_time_base_count', 'three_time_base_count', 'four_time_base_count', 'grid_prefab_id', 'seo_machine_type', 'account_id', 'seo_machine_play_count', 'gift', 'machine_auto', 'all_reward_base_count', 'all_orange', 'all_mango', 'all_watermelon', 'all_bell', 'seven_reward_base_count', 'five_seven', 'six_seven', 'seven_seven', 'eight_seven','current_reward_type'], 'required'],
            [['reservation_date', 'create_date'], 'safe'],
            [['seo_machine_id'], 'string', 'max' => 50],
            [['room_info_list_id'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'auto_id' => '自增id',
            'order_id' => '顺序',
            'seo_machine_id' => '机台编号',
            'all_fruits_base_count' => '全盘水果-随机基数',
            'mixed_bars_base_count' => '全盘BAR-随机基数',
            'star_reward_appearance_base_count' => '明星奖出奖几率基数（填100代表百分之一）',
            'double_time_base_count' => '两倍占比数',
            'three_time_base_count' => '三倍占比数',
            'four_time_base_count' => '四倍占比数',
            'room_info_list_id' => 'Room Info List ID',
            'grid_prefab_id' => 'Grid Prefab ID',
            'seo_machine_type' => '机台状态',
            'account_id' => '玩家id',
            'seo_machine_play_count' => '机台的总局数',
            'gift' => '机器的实时彩金，因房间不同累积速度和底值有较大差异',
            'reservation_date' => '留机到期时间',
            'machine_auto' => '自动状态:0-非自动 1-自动',
            'create_date' => 'Create Date',
            'all_reward_base_count' => '四个全盘奖的出现率',
            'all_orange' => '全盘橘子的出现率',
            'all_mango' => '全盘芒果的出现率',
            'all_watermelon' => '全盘西瓜的出现率',
            'all_bell' => '全盘铃铛的出现率',
            'seven_reward_base_count' => '七奖的出现率',
            'five_seven' => '五七奖的出现率',
            'six_seven' => '六七奖的出现率',
            'seven_seven' => '七七奖的出现率',
            'eight_seven' => '八七奖的出现率',
            'current_reward_type' => '手动放奖',
        ];
    }
    public function getPlayerInfo()
    {
        return $this->hasOne(FivepkPlayerInfo::className(),['account_id'=>'account_id']);
    }

    public function getRoomList()
    {
        return $this->hasOne(DataRoomInfoList::className(),['id'=>'room_info_list_id']);
    }

    public function getJiangchi1()
    {
        $jiangchi1 = MachineRewardPoolStar97::find()->filterWhere(['like','seo_machine_id',$this->seo_machine_id])->andFilterWhere(['pool_id'=>1])->one();
        return $jiangchi1;
    }

    public function getJiangchi2()
    {
        $jiangchi2 = MachineRewardPoolStar97::find()->filterWhere(['like','seo_machine_id',$this->seo_machine_id])->andFilterWhere(['pool_id'=>2])->one();
        return $jiangchi2;
    }

    public function getJiangchi3()
    {
        $jiangchi3 = TyMachinePrefabBigReward::find()->filterWhere(['like','seo_machine_id',$this->seo_machine_id])->one();
        return $jiangchi3;
    }
    /**
     * 获得状态
     * @return null|string
     */
    public function getStatus()
    {
        $status = null;
        if($this->seo_machine_type == 0){
            $status = '空闲';
        }elseif ($this->seo_machine_type == 1){
            if($this->machine_auto == 1){
                $status = '自动';
            }else {
                $status = '在线';
            }
        }elseif ($this->seo_machine_type == 2){
            $status = '留机';
        }
        return $status;
    }

    /**
     * 删除机台
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function deleteMachine($params = array()){
        $model = new MachineListStar97();

        $data = $model->findOne($params);

        if(empty($data)){
            throw new Exception('查询无机台号');
        }

        $row = $model->deleteAll($params);

        return $row;
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
        'auto_id' => array(
                        'name' => 'auto_id',
                        'allowNull' => false,
//                         'autoIncrement' => true,
//                         'comment' => '自增id',
//                         'dbType' => "bigint(20)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => true,
                        'phpType' => 'string',
                        'precision' => '20',
                        'scale' => '',
                        'size' => '20',
                        'type' => 'bigint',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('auto_id'),
                        'inputType' => 'hidden',
                        'isEdit' => true,
                        'isSearch' => true,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'order_id' => array(
                        'name' => 'order_id',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '顺序',
//                         'dbType' => "int(11)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('order_id'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'seo_machine_id' => array(
                        'name' => 'seo_machine_id',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '机台编号',
//                         'dbType' => "varchar(50)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '50',
                        'scale' => '',
                        'size' => '50',
                        'type' => 'string',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('seo_machine_id'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_fruits_base_count' => array(
                        'name' => 'all_fruits_base_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '全盘水果-随机基数',
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
                        'label'=>$this->getAttributeLabel('all_fruits_base_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'mixed_bars_base_count' => array(
                        'name' => 'mixed_bars_base_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '全盘BAR-随机基数',
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
                        'label'=>$this->getAttributeLabel('mixed_bars_base_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
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
		'room_info_list_id' => array(
                        'name' => 'room_info_list_id',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '0',
//                         'dbType' => "varchar(20)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '20',
                        'scale' => '',
                        'size' => '20',
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
		'grid_prefab_id' => array(
                        'name' => 'grid_prefab_id',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "int(11)",
                        'defaultValue' => '1',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('grid_prefab_id'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'seo_machine_type' => array(
                        'name' => 'seo_machine_type',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '机台状态',
//                         'dbType' => "smallint(1)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '1',
                        'scale' => '',
                        'size' => '1',
                        'type' => 'smallint',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('seo_machine_type'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'account_id' => array(
                        'name' => 'account_id',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '玩家id',
//                         'dbType' => "bigint(20)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '20',
                        'scale' => '',
                        'size' => '20',
                        'type' => 'bigint',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('account_id'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'seo_machine_play_count' => array(
                        'name' => 'seo_machine_play_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '机台的总局数',
//                         'dbType' => "int(10)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '10',
                        'scale' => '',
                        'size' => '10',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('seo_machine_play_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'gift' => array(
                        'name' => 'gift',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '机器的实时彩金，因房间不同累积速度和底值有较大差异',
//                         'dbType' => "int(10)",
                        'defaultValue' => '248800',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '10',
                        'scale' => '',
                        'size' => '10',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('gift'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'reservation_date' => array(
                        'name' => 'reservation_date',
                        'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '留机到期时间',
//                         'dbType' => "timestamp",
                        'defaultValue' => 'CURRENT_TIMESTAMP',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '',
                        'scale' => '',
                        'size' => '',
                        'type' => 'timestamp',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('reservation_date'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'machine_auto' => array(
                        'name' => 'machine_auto',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '自动状态:0-非自动 1-自动',
//                         'dbType' => "int(2)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '2',
                        'scale' => '',
                        'size' => '2',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('machine_auto'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'create_date' => array(
                        'name' => 'create_date',
                        'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "timestamp",
                        'defaultValue' => 'CURRENT_TIMESTAMP',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '',
                        'scale' => '',
                        'size' => '',
                        'type' => 'timestamp',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('create_date'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_prize_base_count' => array(
                        'name' => 'all_prize_base_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '四个全盘奖的出现率',
//                         'dbType' => "int(11)",
                        'defaultValue' => '10000',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_prize_base_count'),
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
//                         'comment' => '全盘橘子的出现率',
//                         'dbType' => "int(11)",
                        'defaultValue' => '1',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
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
//                         'comment' => '全盘芒果的出现率',
//                         'dbType' => "int(11)",
                        'defaultValue' => '1',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_mango'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'all_watermelon' => array(
                        'name' => 'all_watermelon',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '全盘西瓜的出现率',
//                         'dbType' => "int(11)",
                        'defaultValue' => '1',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_watermelon'),
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
//                         'comment' => '全盘铃铛的出现率',
//                         'dbType' => "int(11)",
                        'defaultValue' => '1',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('all_bell'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'seven_prize_base_count' => array(
                        'name' => 'seven_prize_base_count',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '七奖的出现率',
//                         'dbType' => "int(11)",
                        'defaultValue' => '10000',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('seven_prize_base_count'),
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
//                         'comment' => '五七奖的出现率',
//                         'dbType' => "int(11)",
                        'defaultValue' => '1',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
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
//                         'comment' => '六七奖的出现率',
//                         'dbType' => "int(11)",
                        'defaultValue' => '1',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
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
//                         'comment' => '七七奖的出现率',
//                         'dbType' => "int(11)",
                        'defaultValue' => '1',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
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
//                         'comment' => '八七奖的出现率',
//                         'dbType' => "int(11)",
                        'defaultValue' => '1',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('eight_seven'),
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
