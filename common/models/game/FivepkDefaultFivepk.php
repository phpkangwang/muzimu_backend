<?php
namespace common\models\game;

use common\models\FivepkDefault;
use common\models\FivepkPath;
use common\models\FivepkPrizeType;
use common\models\game\big_plate\FivepkSeoBigPlate;
use common\models\game\firephoenix\FivepkSeoFirephoenix;
use Yii;

/**
 * This is the model class for table "fivepk_default_fivepk".
 *
 * @property string $id
 * @property string $machine_auto_id
 * @property string $first_hand_cards
 * @property string $keep_cards
 * @property integer $first_win_type
 * @property string $second_hand_cards
 * @property integer $second_win_type
 * @property string $seo_machine_play_count
 * @property integer $random
 * @property string $last_time
 * @property integer $data_prize_type_id
 * @property integer $four_of_a_kind_gift_pool
 */
class FivepkDefaultFivepk extends \backend\models\BaseModel
{

    public static $card = array(
        1=>'方块A',2=>'方块2',3=>'方块3',4=>'方块4',5=>'方块5',6=>'方块6',7=>'方块7',8=>'方块8',9=>'方块9',10=>'方块10',11=>'方块J',12=>'方块Q',13=>'方块K',
        14=>'梅花A',15=>'梅花2',16=>'梅花3',17=>'梅花4',18=>'梅花5',19=>'梅花6',20=>'梅花7',21=>'梅花8',22=>'梅花9',23=>'梅花10',24=>'梅花J',25=>'梅花Q',26=>'梅花K',
        27=>'红桃A',28=>'红桃2',29=>'红桃3',30=>'红桃4',31=>'红桃5',32=>'红桃6',33=>'红桃7',34=>'红桃8',35=>'红桃9',36=>'红桃10',37=>'红桃J',38=>'红桃Q',39=>'红桃K',
        40=>'黑桃A',41=>'黑桃2',42=>'黑桃3',43=>'黑桃4',44=>'黑桃5',45=>'黑桃6',46=>'黑桃7',47=>'黑桃8',48=>'黑桃9',49=>'黑桃10',50=>'黑桃J',51=>'黑桃Q',52=>'黑桃K',
        53=>'鬼',54=>'鬼',55=>'鬼',56=>'鬼',""=>'',
    );

//    public $best_bet;
//
//    public function __construct(array $config = [])
//    {
//        $best_bet = FivepkPrizeType::find()->select('id')->filterWhere(['like','prize_name','喜从天降'])->all();
//        $result = [];
//        foreach ($best_bet as $value){
//            $result[] = $value->id;
//        }
//        $this->best_bet = $result;
//        parent::__construct($config);
//    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_default_fivepk';
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
            [['machine_auto_id', 'first_win_type', 'second_win_type', 'seo_machine_play_count', 'random', 'last_time','data_prize_type_id','four_of_a_kind_gift_pool'], 'integer'],
            [['first_hand_cards', 'keep_cards', 'second_hand_cards'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'machine_auto_id' => '机台id',
            'first_hand_cards' => '第一手牌',
            'keep_cards' => '保牌',
            'first_win_type' => '第一手得奖类型',
            'second_hand_cards' => '第二手牌',
            'second_win_type' => '第二手得奖类型',
            'seo_machine_play_count' => '中奖局数',
            'random' => '0-累积1-随机',
            'last_time' => 'Last Time',
            'data_prize_type_id' => '奖型id',
            'four_of_a_kind_gift_pool' => '四梅补偿分',
        ];
    }

    public function getCompare()
    {
        return $this->hasMany(FivepkDefaultFivepkCompare::className(),['fivepk_default_fivepk_id'=>'id']);
    }

    /**
     * @desc 关联火凤凰机台
     * @return \yii\db\ActiveQuery
     */
    public function getFirephoenixMachine()
    {
        return $this->hasOne(FivepkSeoFirephoenix::className(),['auto_id'=>'machine_auto_id']);
    }

    /**
     * 关联轨迹表
     * @return \yii\db\ActiveQuery
     */
    public function getFivepkDefault()
    {
        return $this->hasOne(FivepkDefault::className(),['fivepk_default_fivepk_id'=>'id']);
    }

    /**
     * @desc 关联奖型表
     * @return \yii\db\ActiveQuery
     */
    public function getPrizeType()
    {
        return $this->hasOne(FivepkPrizeType::className(),['id'=>'data_prize_type_id']);
    }

    /**
     * 关联大字板机台
     * @return \yii\db\ActiveQuery
     */
    public function getBigPlateMachine()
    {
        return $this->hasOne(FivepkSeoBigPlate::className(),['auto_id'=>'machine_auto_id']);
    }

    /**
     * 获得第一手牌
     * @return bool|string
     */
    public function getFirstCards()
    {
        if(in_array($this->data_prize_type_id,$this->best_bet)){
            return '';
        }else{
            return $this->getCards($this->first_hand_cards);
        }
    }

    /**
     * 获得第二手牌
     * @return bool|string
     */
    public function getSecondCards()
    {
        if(in_array($this->data_prize_type_id,$this->best_bet)){
            return '';
        }else{
            return $this->getCards($this->second_hand_cards);
        }

    }

    /**
     * 获得出奖类型
     * @return null|string
     */
    public static function getRandomType($random)
    {
        $user = Yii::$app->user->identity;
        $type = null;
        if($user->white_list == 1){
            switch ($random) {
                case 0: $type = '机台累积';break;
                case 1: $type = '机台随机';break;
                case 2: $type = '房间累积';break;
                case 3: $type = '新人奖';break;
                case 4: $type = '四梅加难';break;
                case 5: $type = '四梅强补';break;
            }
        }
        return $type;
    }

    /**
     * @desc 获得牌型
     * @param $cards
     * @return bool|string
     */
    public static function getCards($cards)
    {
        $arr = explode(",", $cards);
        $str = '';
        foreach ($arr as $val){
            $str .= ','.self::$card[trim($val)];
        }
        $str = substr($str,1);
        return $str;
    }

    /**
     * 获得一手牌型
     * @return string
     */
    public function getFirstCardType($game_type,$first_win_type)
    {
        if ($game_type == Yii::$app->params['fire_phoenix']) {
            switch($first_win_type){
                case -3: $result="小一对"; break;
                case 1: $result="大一对"; break;
                case 2: $result="两对"; break;
                case 3: $result="三条"; break;
                case 5: $result="顺子"; break;
                case 7: $result="同花"; break;
                case 10: $result="葫芦"; break;
                case 50: $result="小四梅"; break;
                case 80: $result="大四梅"; break;
                case 120: $result="同花小顺"; break;
                case 250: $result="五梅"; break;
                case 500: $result="同花大顺"; break;
                case 1000: $result="五鬼"; break;
                case -1: $result="四张同花"; break;
                case -2: $result="四张顺"; break;
                case -4: $result="乌龙"; break;
                case -5: $result="乌龙"; break;
                default:$result=""; break;

            }
        }elseif ($game_type == Yii::$app->params['att']) {
            switch($first_win_type){
                case -3: $result="小一对"; break;
                case 1: $result="一对"; break;
                case 2: $result="两对"; break;
                case 3: $result="三条"; break;

                case 5: $result="顺子"; break;
                case 7: $result="同花"; break;
                case 10: $result="葫芦"; break;
                case 40:
                case 50:
                case 60: $result="小四梅"; break;
                case 100:
                case 120:
                case 140: $result="同花小顺"; break;
                case 150:
                case 200:
                case 250: $result="同花大顺"; break;
                case 300:
                case 400:
                case 500: $result="五梅"; break;
                case -1: $result="四张同花"; break;
                case -2: $result="四张顺"; break;
                case -4: $result="乌龙"; break;
                case -5: $result="乌龙"; break;
                default:$result=""; break;

            }
        }elseif ($game_type == Yii::$app->params['big_plate']) {
            switch($first_win_type){
                case -3: $result="小一对"; break;
                case 1: $result="一对"; break;
                case 2: $result="两对"; break;
                case 3: $result="三条"; break;
                case 5: $result="顺子"; break;
                case 7: $result="同花"; break;
                case 15: $result="葫芦"; break;
                case 65: $result="四梅"; break;
                case 150: $result="同花小顺"; break;
                case 250: $result="同花大顺"; break;
                case 350: $result="五梅"; break;
                case -1: $result="四张同花"; break;
                case -2: $result="四张顺"; break;
                case -4: $result="乌龙"; break;
                case -5: $result="乌龙"; break;
                default:$result=""; break;

            }
        }else{
            $result = null;
        }
        return $result;
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
                        'label'=>$this->getAttributeLabel('id'),
                        'inputType' => 'hidden',
                        'isEdit' => true,
                        'isSearch' => true,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'machine_auto_id' => array(
                        'name' => 'machine_auto_id',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '机台id',
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
                        'label'=>$this->getAttributeLabel('machine_auto_id'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'first_hand_cards' => array(
                        'name' => 'first_hand_cards',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '第一手牌',
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
                        'label'=>$this->getAttributeLabel('first_hand_cards'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'keep_cards' => array(
                        'name' => 'keep_cards',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '保牌',
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
                        'label'=>$this->getAttributeLabel('keep_cards'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'first_win_type' => array(
                        'name' => 'first_win_type',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '第一手得奖类型',
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
                        'label'=>$this->getAttributeLabel('first_win_type'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'second_hand_cards' => array(
                        'name' => 'second_hand_cards',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '第二手牌',
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
                        'label'=>$this->getAttributeLabel('second_hand_cards'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'second_win_type' => array(
                        'name' => 'second_win_type',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '第二手得奖类型',
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
                        'label'=>$this->getAttributeLabel('second_win_type'),
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
//                         'comment' => '中奖局数',
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
                        'label'=>$this->getAttributeLabel('seo_machine_play_count'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'random' => array(
                        'name' => 'random',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '0-累积1-随机',
//                         'dbType' => "tinyint(4)",
                        'defaultValue' => '0',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'integer',
                        'precision' => '4',
                        'scale' => '',
                        'size' => '4',
                        'type' => 'smallint',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('random'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'last_time' => array(
                        'name' => 'last_time',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
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
                        'label'=>$this->getAttributeLabel('last_time'),
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
