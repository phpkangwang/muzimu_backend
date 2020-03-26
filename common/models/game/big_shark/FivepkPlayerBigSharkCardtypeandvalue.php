<?php
namespace common\models\game\big_shark;

use backend\models\ErrorCode;
use backend\models\MyException;
use common\services\Messenger;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "fivepk_player_bigshark_cardtypeandvalue".
 *
 * @property string $account_id
 * @property integer $prefab_five_bars
 * @property double $prefab_five_bars_count
 * @property integer $prefab_royal_flush
 * @property integer $prefab_royal_flush_fake
 * @property double $prefab_royal_flush_count
 * @property integer $prefab_five_of_a_kind
 * @property double $prefab_five_of_a_kind_count
 * @property integer $prefab_five_of_a_kind_compare
 * @property string $prefab_five_of_a_kind_compare_record
 * @property integer $prefab_straight_flush
 * @property integer $prefab_straight_flush_fake
 * @property double $prefab_straight_flush_count
 * @property integer $prefab_four_of_a_kind_joker_two
 * @property double $prefab_four_of_a_kind_Joker_count_two
 * @property integer $prefab_four_of_a_kind_Joker_two_fourteen_two
 * @property string $prefab_four_of_a_kind_Joker_two_fourteen_record
 * @property integer $prefab_four_of_a_kind_J_A
 * @property double $prefab_four_of_a_kind_J_A_count
 * @property integer $prefab_four_of_a_kind_ja
 * @property integer $prefab_four_of_a_kind_T_T
 * @property double $prefab_four_of_a_kind_T_T_count
 * @property integer $prefab_four_of_a_kind_two_ten
 * @property integer $prefab_four_of_a_kind_two_ten_two
 * @property string $prefab_four_of_a_kind_two_ten_continue
 * @property double $prefab_four_of_a_kind_two_ten_continue_count
 * @property string $prefab_four_of_a_kind_two_ten_continue_record
 * @property integer $prefab_four_of_a_kind_two_ten_continue_rate
 * @property integer $prefab_full_house
 * @property integer $prefab_flush
 * @property integer $prefab_straight
 * @property integer $prefab_three_of_a_kind
 * @property integer $prefab_two_pairs
 * @property integer $prefab_seven_better
 * @property integer $prefab_four_flush
 * @property integer $prefab_four_straight
 * @property integer $prefab_seven_better_keep
 * @property integer $prefab_joker
 * @property string $seo_machine_play_count
 * @property integer $machine_auto
 * @property string $compare_history_cards
 * @property integer $prefab_force_seven_better
 * @property integer $prefab_force_seven_better_count
 * @property integer $prefab_compare_buff
 * @property integer $prefab_compare_cut_down
 * @property integer $prefab_compare_cut_down_count
 * @property integer $prefab_compare_seven_joker
 */
class FivepkPlayerBigSharkCardtypeandvalue extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_bigshark_cardtypeandvalue';
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
            [['account_id'], 'required'],
            [['account_id', 'prefab_five_bars', 'prefab_royal_flush', 'prefab_royal_flush_fake', 'prefab_five_of_a_kind', 'prefab_five_of_a_kind_compare', 'prefab_straight_flush', 'prefab_straight_flush_fake', 'prefab_four_of_a_kind_joker_two', 'prefab_four_of_a_kind_Joker_two_fourteen_two', 'prefab_four_of_a_kind_J_A', 'prefab_four_of_a_kind_ja', 'prefab_four_of_a_kind_T_T', 'prefab_four_of_a_kind_two_ten', 'prefab_four_of_a_kind_two_ten_two', 'prefab_four_of_a_kind_two_ten_continue_rate', 'prefab_full_house', 'prefab_flush', 'prefab_straight', 'prefab_three_of_a_kind', 'prefab_two_pairs', 'prefab_seven_better', 'prefab_four_flush', 'prefab_four_straight', 'prefab_seven_better_keep', 'prefab_joker', 'seo_machine_play_count', 'machine_auto', 'prefab_force_seven_better', 'prefab_force_seven_better_count', 'prefab_compare_buff', 'prefab_compare_cut_down', 'prefab_compare_cut_down_count', 'prefab_compare_seven_joker'], 'integer'],
            [['prefab_five_bars_count', 'prefab_royal_flush_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_Joker_count_two', 'prefab_four_of_a_kind_J_A_count', 'prefab_four_of_a_kind_T_T_count', 'prefab_four_of_a_kind_two_ten_continue_count'], 'number'],
            [['prefab_five_of_a_kind_compare_record', 'prefab_four_of_a_kind_Joker_two_fourteen_record', 'prefab_four_of_a_kind_two_ten_continue_record'], 'string', 'max' => 2555],
            [['prefab_four_of_a_kind_two_ten_continue'], 'string', 'max' => 255],
            [['compare_history_cards'], 'string', 'max' => 50],
            [['prefab_four_of_a_kind_T_T_count','prefab_four_of_a_kind_J_A_count','prefab_four_of_a_kind_Joker_count_two','prefab_compare_buff','prefab_four_of_a_kind_two_ten_continue_count','prefab_five_bars_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_straight_flush_count','prefab_four_of_a_kind_Joker_count_two'],'number','min'=>0,'max'=>999999999],
            [['prefab_four_of_a_kind_T_T_count','prefab_four_of_a_kind_J_A_count','prefab_four_of_a_kind_Joker_count_two','prefab_compare_buff','prefab_four_of_a_kind_two_ten_continue_count','prefab_five_bars_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_straight_flush_count','prefab_four_of_a_kind_Joker_count_two'],'required'],
//            [['prefab_four_of_a_kind_T_T_count','prefab_four_of_a_kind_J_A_count','prefab_four_of_a_kind_Joker_count_two','prefab_compare_buff','prefab_four_of_a_kind_two_ten_continue_count','prefab_five_bars_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_straight_flush_count','prefab_four_of_a_kind_Joker_count_two'],'default','value'=>0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_id' => '玩家ID',
            'prefab_five_bars' => '五鬼',
            'prefab_five_bars_count' => '五鬼Buffer',
            'prefab_royal_flush' => '同花大顺',
            'prefab_royal_flush_fake' => '假大顺',
            'prefab_royal_flush_count' => '同花大顺Buffer',
            'prefab_five_of_a_kind' => '五梅',
            'prefab_five_of_a_kind_count' => '五梅Buffer',
            'prefab_five_of_a_kind_compare' => '五梅比倍',
            'prefab_five_of_a_kind_compare_record' => 'Prefab Five Of A Kind Compare Record',
            'prefab_straight_flush' => '同花小顺',
            'prefab_straight_flush_fake' => '假小顺',
            'prefab_straight_flush_count' => '小顺Buffer',
            'prefab_four_of_a_kind_joker_two' => '正宗大四梅',
            'prefab_four_of_a_kind_Joker_count_two' => '正宗大四梅累积Buffer',
            'prefab_four_of_a_kind_Joker_two_fourteen_two' => '正宗大四梅累积倍数',
            'prefab_four_of_a_kind_Joker_two_fourteen_record' => 'Prefab Four Of A Kind  Joker Two Fourteen Record',
            'prefab_four_of_a_kind_J_A' => '大四梅累积值',
            'prefab_four_of_a_kind_J_A_count' => '大四梅累积值Buffer',
            'prefab_four_of_a_kind_ja' => '大四梅出现率',
            'prefab_four_of_a_kind_T_T' => '小四梅累积值',
            'prefab_four_of_a_kind_T_T_count' => '小四梅累积值Buffer',
            'prefab_four_of_a_kind_two_ten' => '小四梅出现率',
            'prefab_four_of_a_kind_two_ten_two' => '连庄开关',
            'prefab_four_of_a_kind_two_ten_continue' => 'Prefab Four Of A Kind Two Ten Continue',
            'prefab_four_of_a_kind_two_ten_continue_count' => '连庄Buffer',
            'prefab_four_of_a_kind_two_ten_continue_record' => 'Prefab Four Of A Kind Two Ten Continue Record',
            'prefab_four_of_a_kind_two_ten_continue_rate' => '连庄数',
            'prefab_full_house' => '葫芦',
            'prefab_flush' => '同花',
            'prefab_straight' => '顺子',
            'prefab_three_of_a_kind' => '三条',
            'prefab_two_pairs' => '两对',
            'prefab_seven_better' => '一对',
            'prefab_four_flush' => '四张同花',
            'prefab_four_straight' => '四张顺',
            'prefab_seven_better_keep' => '小一对',
            'prefab_joker' => '鬼牌',
            'seo_machine_play_count' => 'Seo Machine Play Count',
            'machine_auto' => 'Machine Auto',
            'compare_history_cards' => 'Compare History Cards',
            'prefab_force_seven_better' => '强制一对',
            'prefab_force_seven_better_count' => 'Prefab Force Seven Better Count',
            'prefab_compare_buff' => '比倍Buffer',
            'prefab_compare_cut_down' => '比倍砍牌',
            'prefab_compare_cut_down_count' => 'Prefab Compare Cut Down Count',
            'prefab_compare_seven_joker' => '比倍7鬼翻倍',
        ];
    }

    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try{
            foreach ( $data as $key => $val )
            {
                $this->$key = $val;
            }
            if( $this->save() )
            {
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    public function updateAllUserOdds($postData, $accountIdArr)
    {
        try {
            $obj = self::find()->one();
            foreach ( $postData as $key => $val )
            {
                $obj->$key = $val;
            }
            if(!$obj->validate())
            {
                $errorObj = $obj->getFirstErrors();
                $errMess = "";
                foreach ($errorObj as $v){
                    $errMess = $v;
                }
                throw new MyException( $errMess);
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }

        if( empty($accountIdArr) ){
            self::updateAll($postData,[]);
        }else{
            self::updateAll($postData,['not in','account_id',$accountIdArr]);
        }
    }

    /**
     *  获取老玩家几率
     * @param $accountId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getPlayerOdds($accountId)
    {
        $data = self::find()->where(['account_id'=>$accountId])->asArray()->one();
        $data = $this->Tool->clearFloatZero($data);
        return $data;
    }

    /**
     *   修改老玩家机率
     */
    public function updatePlayerOdds($postData, $accountIds)
    {
        self::updateAll($postData,['in','account_id',$accountIds]);
    }

    /**
     * 当老玩家没有数据的时候，首先得初始化数据
     * 每次初始化数据的时候首先得删除掉这个玩家的所有的旧数据
     * @param $accountId
     * @return bool
     */
    public function initUserOdds($accountId)
    {
        return $this->setInitUserOdds($accountId,$this);
    }

    public function setInitUserOdds($accountId,$_this)
    {
        $data = $_this::find()->asArray()->orderBy('account_id asc')->one();
        $_this::deleteAll("account_id=:account_id", [':account_id' => $accountId]);
        $data['account_id'] = $accountId;
        unset($data['id']);
//        varDump($data);
        $return = $_this->add($data);
        return $return;
    }

}
