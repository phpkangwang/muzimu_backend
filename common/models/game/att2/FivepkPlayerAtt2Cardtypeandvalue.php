<?php

namespace common\models\game\att2;

use backend\models\BaseModel;
use common\services\Messenger;
use Yii;
use yii\db\Exception;

/**
 * This is the model class for table "fivepk_player_att2_cardtypeandvalue".
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
 * @property integer $prefab_four_of_a_kind_random_level
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
 * @property string $compare_history_cards
 * @property integer $prefab_force_seven_better
 * @property integer $prefab_force_seven_better_count
 * @property integer $prefab_compare_buff
 * @property integer $four_of_kind_random_base_count
 * @property integer $straight_flush_random_base_count
 * @property integer $royal_flush_random_base_count
 * @property integer $five_of_kind_random_base_count
 */
class FivepkPlayerAtt2Cardtypeandvalue extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_att2_cardtypeandvalue';
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
            [['account_id', 'prefab_royal_flush', 'prefab_royal_flush_random', 'prefab_royal_flush_fake', 'prefab_five_of_a_kind',
                'prefab_five_of_a_kind_random', 'prefab_straight_flush', 'prefab_straight_flush_random', 'prefab_straight_flush_fake',
                'prefab_four_of_a_kind_T_T', 'prefab_four_of_a_kind_random_level', 'prefab_full_house', 'prefab_flush', 'prefab_straight',
                'prefab_three_of_a_kind', 'prefab_two_pairs', 'prefab_seven_better', 'prefab_four_flush', 'prefab_four_straight','prefab_seven_better_keep',
                'prefab_joker', 'seo_machine_play_count', 'prefab_force_seven_better', 'prefab_force_seven_better_count','prefab_compare_buff',
                'four_of_kind_random_base_count','straight_flush_random_base_count','royal_flush_random_base_count','five_of_kind_random_base_count'
            ], 'integer'],
            [['prefab_royal_flush_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_T_T_count'], 'number'],
            [['prefab_compare_buff','four_of_kind_random_base_count','straight_flush_random_base_count','royal_flush_random_base_count','five_of_kind_random_base_count'],'required'],
            [['compare_history_cards'], 'string', 'max' => 50],
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

                $model = new FivepkPlayerAtt2Cardtypeandvalue();

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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_id' => 'Account ID',
            'prefab_royal_flush' => 'Prefab Royal Flush',
            'prefab_royal_flush_random' => 'Prefab Royal Flush Random',
            'prefab_royal_flush_fake' => 'Prefab Royal Flush Fake',
            'prefab_royal_flush_count' => 'Prefab Royal Flush Count',
            'prefab_five_of_a_kind' => 'Prefab Five Of A Kind',
            'prefab_five_of_a_kind_random' => 'Prefab Five Of A Kind Random',
            'prefab_five_of_a_kind_count' => 'Prefab Five Of A Kind Count',
            'prefab_straight_flush' => 'Prefab Straight Flush',
            'prefab_straight_flush_random' => 'Prefab Straight Flush Random',
            'prefab_straight_flush_fake' => 'Prefab Straight Flush Fake',
            'prefab_straight_flush_count' => 'Prefab Straight Flush Count',
            'prefab_four_of_a_kind_T_T' => 'Prefab Four Of A Kind  T  T',
            'prefab_four_of_a_kind_T_T_count' => 'Prefab Four Of A Kind  T  T Count',
            'prefab_four_of_a_kind_random_level' => 'Prefab Four Of A Kind Random Level',
            'prefab_full_house' => 'Prefab Full House',
            'prefab_flush' => 'Prefab Flush',
            'prefab_straight' => 'Prefab Straight',
            'prefab_three_of_a_kind' => 'Prefab Three Of A Kind',
            'prefab_two_pairs' => 'Prefab Two Pairs',
            'prefab_seven_better' => 'Prefab Seven Better',
            'prefab_four_flush' => 'Prefab Four Flush',
            'prefab_four_straight' => 'Prefab Four Straight',
            'prefab_seven_better_keep' => 'Prefab Seven Better Keep',
            'prefab_joker' => 'Prefab Joker',
            'seo_machine_play_count' => 'Seo Machine Play Count',
            'compare_history_cards' => 'Compare History Cards',
            'prefab_force_seven_better' => 'Prefab Force Seven Better',
            'prefab_force_seven_better_count' => 'Prefab Force Seven Better Count',
            'prefab_compare_buff' => '比倍Buff',
            'four_of_kind_random_base_count' => '四梅',
            'straight_flush_random_base_count' => '小顺',
            'royal_flush_random_base_count' => '大顺',
            'five_of_kind_random_base_count' => '五梅',
        ];
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
        $FivepkPlayerFirephoenixCardtypeandvalue = new \common\models\game\firephoenix\FivepkPlayerFirephoenixCardtypeandvalue();

        return $FivepkPlayerFirephoenixCardtypeandvalue->setInitUserOdds($accountId, $this);
    }

}
