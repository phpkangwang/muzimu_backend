<?php
namespace common\models\game\star97;

use Yii;

/**
 * This is the model class for table "star_card_type_and_value".
 *
 * @property integer $id
 * @property integer $random_prefab_id
 * @property integer $grid_prefab_id
 * @property string $account_id
 * @property integer $seo_machine_play_count
 * @property double $five_seven_count
 * @property double $six_seven_count
 * @property double $seven_seven_count
 * @property double $eight_seven_count
 * @property double $all_orange_count
 * @property double $all_cherry_count
 * @property double $all_mango_count
 * @property double $all_bell_count
 * @property double $all_watermelon_count
 * @property double $all_red_count
 * @property double $all_yellow_count
 * @property double $all_blue_count
 * @property double $all_seven_count
 */
class StarCardTypeAndValue extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'star_card_type_and_value';
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
            [[ 'grid_prefab_id', 'account_id', 'seo_machine_play_count'], 'integer'],
            [['five_seven_count', 'six_seven_count', 'seven_seven_count', 'eight_seven_count', 'all_orange_count', 'all_cherry_count', 'all_mango_count', 'all_bell_count', 'all_watermelon_count', 'all_red_count', 'all_yellow_count', 'all_blue_count', 'all_seven_count'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'grid_prefab_id' => 'Grid Prefab ID',
            'account_id' => '玩家id',
            'seo_machine_play_count' => '机台的总局数',
            'five_seven_count' => '5个7的累积值BUFF',
            'six_seven_count' => '6个7的累积值BUFF',
            'seven_seven_count' => '7个7的累积值BUFF',
            'eight_seven_count' => '8个7的累积值BUFF',
            'all_orange_count' => '全盘橘子的累积值BUFF',
            'all_cherry_count' => '全盘樱桃的累积值BUFF',
            'all_mango_count' => '全盘芒果的累积值BUFF',
            'all_bell_count' => '全盘铃铛的累积值BUFF',
            'all_watermelon_count' => '全盘西瓜的累积值BUFF',
            'all_red_count' => '全盘红BAR的累积值BUFF',
            'all_yellow_count' => '全盘黄BAR的累积值BUFF',
            'all_blue_count' => '全盘蓝BAR的累积值BUFF',
            'all_seven_count' => '全盘九七的累积值BUFF',
        ];
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
