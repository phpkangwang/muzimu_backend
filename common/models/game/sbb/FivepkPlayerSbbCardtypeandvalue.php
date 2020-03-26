<?php
namespace common\models\game\sbb;

use backend\models\BaseModel;
use common\services\Messenger;
use Yii;

/**
 * This is the model class for table "fivepk_player_super_big_boss_cardtypeandvalue".
 *
 * @property string $account_id
 * @property double $prefab_five_bars_add_count
 * @property double $prefab_five_bars_count
 * @property double $prefab_royal_flush_six_add_count
 * @property double $prefab_royal_flush_six_count
 * @property double $prefab_royal_flush_add_count
 * @property integer $prefab_royal_flush_fake
 * @property double $prefab_royal_flush_count
 * @property double $prefab_five_of_a_kind_add_count
 * @property double $prefab_five_of_a_kind_count
 * @property double $prefab_straight_flush_six_add_count
 * @property double $prefab_straight_flush_six_count
 * @property double $prefab_straight_flush_add_count
 * @property integer $prefab_straight_flush_fake
 * @property double $prefab_straight_flush_count
 * @property double $prefab_four_of_a_kind_add_count
 * @property double $prefab_four_of_a_kind_count
 * @property integer $prefab_four_of_a_kind_two_ten
 * @property double $prefab_four_of_a_kind_seven_better_add_count
 * @property double $prefab_four_of_a_kind_seven_better_count
 * @property double $prefab_full_house_aaakk_add_count
 * @property double $prefab_full_house_aaakk_count
 * @property string $seo_machine_play_count
 * @property string $ty_machine_play_count
 * @property integer $prefab_force_seven_better
 * @property integer $prefab_force_seven_better_count
 * @property integer $prefab_two_three_of_a_kind
 * @property integer $prefab_six_straight
 * @property integer $prefab_full_house
 * @property integer $prefab_flush
 * @property integer $prefab_three_pairs
 * @property integer $prefab_straight
 * @property integer $prefab_three_of_a_kind
 * @property integer $prefab_two_pairs
 * @property integer $prefab_seven_better
 * @property integer $prefab_four_flush
 * @property integer $prefab_four_straight
 * @property integer $prefab_seven_better_keep
 * @property integer $prefab_joker
 */
class FivepkPlayerSbbCardtypeandvalue extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_super_big_boss_cardtypeandvalue';
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
            [['account_id', 'prefab_royal_flush_fake', 'prefab_straight_flush_fake', 'prefab_four_of_a_kind_two_ten', 'seo_machine_play_count', 'ty_machine_play_count', 'prefab_force_seven_better', 'prefab_force_seven_better_count', 'prefab_two_three_of_a_kind', 'prefab_six_straight', 'prefab_full_house', 'prefab_flush', 'prefab_three_pairs', 'prefab_straight', 'prefab_three_of_a_kind', 'prefab_two_pairs', 'prefab_seven_better', 'prefab_four_flush', 'prefab_four_straight', 'prefab_seven_better_keep', 'prefab_joker'], 'integer'],
            [['prefab_five_bars_add_count', 'prefab_five_bars_count', 'prefab_royal_flush_six_add_count', 'prefab_royal_flush_six_count', 'prefab_royal_flush_add_count', 'prefab_royal_flush_count', 'prefab_five_of_a_kind_add_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_six_add_count', 'prefab_straight_flush_six_count', 'prefab_straight_flush_add_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_add_count', 'prefab_four_of_a_kind_count', 'prefab_four_of_a_kind_seven_better_add_count', 'prefab_four_of_a_kind_seven_better_count', 'prefab_full_house_aaakk_add_count', 'prefab_full_house_aaakk_count'], 'number'],
            [['prefab_five_bars_add_count', 'prefab_five_bars_count', 'prefab_royal_flush_six_add_count', 'prefab_royal_flush_six_count', 'prefab_royal_flush_add_count', 'prefab_royal_flush_count', 'prefab_five_of_a_kind_add_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_six_add_count', 'prefab_straight_flush_six_count', 'prefab_straight_flush_add_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_add_count', 'prefab_four_of_a_kind_count', 'prefab_four_of_a_kind_seven_better_add_count', 'prefab_four_of_a_kind_seven_better_count', 'prefab_full_house_aaakk_add_count', 'prefab_full_house_aaakk_count'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_id' => '玩家ID',
            'prefab_five_bars_add_count' => '五鬼累积值',
            'prefab_five_bars_count' => '五鬼buff值',
            'prefab_royal_flush_six_add_count' => '六大顺累积值',
            'prefab_royal_flush_six_count' => '六大顺buff值',
            'prefab_royal_flush_add_count' => '大顺累积值',
            'prefab_royal_flush_fake' => 'Prefab Royal Flush Fake',
            'prefab_royal_flush_count' => '大顺buff值',
            'prefab_five_of_a_kind_add_count' => '五梅累积值',
            'prefab_five_of_a_kind_count' => '五梅buff值',
            'prefab_straight_flush_six_add_count' => '六小顺累积值',
            'prefab_straight_flush_six_count' => '六小顺buff值',
            'prefab_straight_flush_add_count' => '小顺累积值',
            'prefab_straight_flush_fake' => 'Prefab Straight Flush Fake',
            'prefab_straight_flush_count' => '小顺buff值',
            'prefab_four_of_a_kind_add_count' => '四梅累积值',
            'prefab_four_of_a_kind_count' => '四梅buff值',
            'prefab_four_of_a_kind_two_ten' => '四梅随机档位',
            'prefab_four_of_a_kind_seven_better_add_count' => '四梅加一对累积值',
            'prefab_four_of_a_kind_seven_better_count' => '四梅加一对buff值',
            'prefab_full_house_aaakk_add_count' => 'aaakk累积值',
            'prefab_full_house_aaakk_count' => 'aaakkbuff值',
            'seo_machine_play_count' => 'Seo Machine Play Count',
            'ty_machine_play_count' => '体验场总玩局数',
            'prefab_force_seven_better' => 'Prefab Force Seven Better',
            'prefab_force_seven_better_count' => 'Prefab Force Seven Better Count',
            'prefab_two_three_of_a_kind' => '2个三条',
            'prefab_six_straight' => '6顺子',
            'prefab_full_house' => 'Prefab Full House',
            'prefab_flush' => 'Prefab Flush',
            'prefab_three_pairs' => '3对',
            'prefab_straight' => 'Prefab Straight',
            'prefab_three_of_a_kind' => 'Prefab Three Of A Kind',
            'prefab_two_pairs' => 'Prefab Two Pairs',
            'prefab_seven_better' => 'Prefab Seven Better',
            'prefab_four_flush' => 'Prefab Four Flush',
            'prefab_four_straight' => 'Prefab Four Straight',
            'prefab_seven_better_keep' => 'Prefab Seven Better Keep',
            'prefab_joker' => 'Prefab Joker',
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

                $model = new FivepkPlayerFirephoenixCardtypeandvalue();

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

    public function updateAllInColumns($params, $accountIdArr)
    {
        try {
            $obj = self::find()->one();
            foreach ( $params as $key => $val )
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
            self::updateAll($params,[]);
        }else{
            self::updateAll($params,['not in','account_id',$accountIdArr]);
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
        $FivepkPlayerFirephoenixCardtypeandvalue = new \common\models\game\firephoenix\FivepkPlayerFirephoenixCardtypeandvalue();

        return $FivepkPlayerFirephoenixCardtypeandvalue->setInitUserOdds($accountId, $this);
    }

}
