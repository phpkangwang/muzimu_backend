<?php
namespace common\models\game\big_plate;

use backend\models\MyException;
use common\services\Messenger;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "fivepk_player_bigplate_cardtypeandvalue".
 *
 * @property string $account_id
 * @property integer $prefab_royal_flush
 * @property integer $prefab_royal_flush_random
 * @property integer $prefab_royal_flush_fake
 * @property double $prefab_royal_flush_count
 * @property integer $prefab_five_of_a_kind
 * @property integer $prefab_five_of_a_kind_random
 * @property double $prefab_five_of_a_kind_count
 * @property integer $prefab_straight_flush
 * @property integer $prefab_straight_flush_random
 * @property integer $prefab_straight_flush_fake
 * @property double $prefab_straight_flush_count
 * @property integer $prefab_four_of_a_kind_T_T
 * @property double $prefab_four_of_a_kind_T_T_count
 * @property integer $prefab_four_of_a_kind_two_ten
 * @property integer $prefab_full_house
 * @property integer $prefab_full_house_jp
 * @property integer $prefab_flush
 * @property integer $prefab_flush_jp
 * @property integer $prefab_straight
 * @property integer $prefab_straight_jp
 * @property integer $prefab_three_of_a_kind
 * @property integer $prefab_three_of_a_kind_jp
 * @property integer $prefab_two_pairs
 * @property integer $prefab_two_pairs_jp
 * @property integer $prefab_seven_better
 * @property integer $prefab_seven_better_jp
 * @property integer $prefab_four_flush
 * @property integer $prefab_four_straight
 * @property integer $prefab_seven_better_keep
 * @property integer $prefab_joker
 * @property string $seo_machine_play_count
 * @property string $compare_history_cards
 * @property integer $prefab_force_seven_better
 * @property integer $prefab_force_seven_better_count
 * @property integer $prefab_compare_buff
 * @property integer $prefab_compare_cut_down
 * @property integer $prefab_compare_cut_down_count
 * @property integer $prefab_compare_seven_joker
 * @property integer $prefab_random_two_times
 * @property string $prefab_five_of_a_kind_double
 * @property string $prefab_royal_flush_double
 * @property string $prefab_straight_flush_double
 * @property string $prefab_four_of_a_kind_double
 */
class FivepkPlayerBigPlateCardTypeAndValue extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_bigplate_cardtypeandvalue';
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
            [['account_id', 'prefab_royal_flush', 'prefab_royal_flush_random', 'prefab_royal_flush_fake', 'prefab_five_of_a_kind', 'prefab_five_of_a_kind_random', 'prefab_straight_flush', 'prefab_straight_flush_random', 'prefab_straight_flush_fake', 'prefab_four_of_a_kind_T_T', 'prefab_four_of_a_kind_two_ten', 'prefab_full_house', 'prefab_full_house_jp', 'prefab_flush', 'prefab_flush_jp', 'prefab_straight', 'prefab_straight_jp', 'prefab_three_of_a_kind', 'prefab_three_of_a_kind_jp', 'prefab_two_pairs', 'prefab_two_pairs_jp', 'prefab_seven_better', 'prefab_seven_better_jp', 'prefab_four_flush', 'prefab_four_straight', 'prefab_seven_better_keep', 'prefab_joker', 'seo_machine_play_count', 'prefab_force_seven_better', 'prefab_force_seven_better_count', 'prefab_compare_buff', 'prefab_compare_cut_down', 'prefab_compare_cut_down_count', 'prefab_compare_seven_joker', 'prefab_random_two_times'], 'integer'],
            [['prefab_royal_flush_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_T_T_count'], 'number'],
            [['prefab_five_of_a_kind_double', 'prefab_royal_flush_double', 'prefab_straight_flush_double', 'prefab_four_of_a_kind_double'], 'string'],
            [['compare_history_cards'], 'string', 'max' => 50],
            [['prefab_four_of_a_kind_T_T_count','prefab_straight_flush_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_compare_buff'],'number','min'=>0,'max'=>'999999999'],
            [['prefab_four_of_a_kind_T_T_count','prefab_straight_flush_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_compare_buff'],'required'],
//            [['prefab_four_of_a_kind_T_T_count','prefab_straight_flush_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_compare_buff'],'default','value'=>0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_id' => 'Account ID',
            'prefab_royal_flush' => '同花大顺累计',
            'prefab_royal_flush_random' => '同花大顺随机',
            'prefab_royal_flush_fake' => '假同花大顺',
            'prefab_royal_flush_count' => '同花大顺累计值',
            'prefab_five_of_a_kind' => '五梅累计',
            'prefab_five_of_a_kind_random' => '五梅随机',
            'prefab_five_of_a_kind_count' => '五梅累计值',
            'prefab_straight_flush' => '同花小顺累计',
            'prefab_straight_flush_random' => '同花小顺随机',
            'prefab_straight_flush_fake' => '假同花小顺',
            'prefab_straight_flush_count' => '同花小顺累计值',
            'prefab_four_of_a_kind_T_T' => '四梅累计',
            'prefab_four_of_a_kind_T_T_count' => '四梅累计值',
            'prefab_four_of_a_kind_two_ten' => '四梅随机',
            'prefab_full_house' => '葫芦',
            'prefab_full_house_jp' => 'Prefab Full House Jp',
            'prefab_flush' => '同花',
            'prefab_flush_jp' => 'Prefab Flush Jp',
            'prefab_straight' => '顺子',
            'prefab_straight_jp' => 'Prefab Straight Jp',
            'prefab_three_of_a_kind' => '三条',
            'prefab_three_of_a_kind_jp' => 'Prefab Three Of A Kind Jp',
            'prefab_two_pairs' => '二对',
            'prefab_two_pairs_jp' => 'Prefab Two Pairs Jp',
            'prefab_seven_better' => '一对',
            'prefab_seven_better_jp' => 'Prefab Seven Better Jp',
            'prefab_four_flush' => '四张同花',
            'prefab_four_straight' => '四张顺',
            'prefab_seven_better_keep' => '小一对',
            'prefab_joker' => '鬼',
            'seo_machine_play_count' => 'Seo Machine Play Count',
            'compare_history_cards' => 'Compare History Cards',
            'prefab_force_seven_better' => '强制一对',
            'prefab_force_seven_better_count' => 'Prefab Force Seven Better Count',
            'prefab_compare_buff' => '比倍 Buffer',
            'prefab_compare_cut_down' => '比倍砍牌',
            'prefab_compare_cut_down_count' => 'Prefab Compare Cut Down Count',
            'prefab_compare_seven_joker' => 'Prefab Compare Seven Joker',
            'prefab_random_two_times' => '随机两倍',
            'prefab_five_of_a_kind_double' => 'Prefab Five Of A Kind Double',
            'prefab_royal_flush_double' => 'Prefab Royal Flush Double',
            'prefab_straight_flush_double' => 'Prefab Straight Flush Double',
            'prefab_four_of_a_kind_double' => 'Prefab Four Of A Kind Double',
        ];
    }



    /**
     * 新增老玩家默认机率
     * @param array $params
     * @return Messenger
     * @throws \Exception
     * @throws \Throwable
     */
    public static function addDefaultPlayerOdds($params = []){
        $message  = new Messenger();
        try {
            if (\Yii::$app->request->isPost) {

                $model = new FivepkPlayerBigPlateCardTypeAndValue();

                if ($model->load($params) && $model->validate()) {
                    $data = $model->insert();

                    $message->data = $data;
                } else {
                    $errors = [];
                    foreach($model->getErrors() as $key => $value){
                        $errors []= $value;
                    }
                    throw new Exception(json_encode($errors,JSON_UNESCAPED_UNICODE));
                }
            }
        }catch(Exception $e){
            $message->status = 0;
            $message->message = $e->getMessage();
        }

        return $message;
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
        return true;
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
        $FivepkPlayerFirephoenixCardtypeandvalue = new \common\models\game\firephoenix\FivepkPlayerFirephoenixCardtypeandvalue();

        return $FivepkPlayerFirephoenixCardtypeandvalue->setInitUserOdds($accountId, $this);
    }
}
