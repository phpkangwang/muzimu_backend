<?php
namespace common\models\game\hfhh;

use backend\models\MyException;
use common\models\odds\OddsHfh;
use common\services\Messenger;
use Yii;


class FivepkPlayerFirephoenixhCardtypeandvalue extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_firephoenix_cardtypeandvalue';
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

    //变成新老玩家机率修改了，
    public function updateAllUserOdds($postData, $accountIdArr)
    {
        $obj = new OddsHfh();
        $obj->initUserInfo($oddsType,$notIdArr);
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
