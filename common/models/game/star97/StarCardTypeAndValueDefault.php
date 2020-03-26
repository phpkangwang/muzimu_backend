<?php
namespace common\models\game\star97;

use Yii;

class StarCardTypeAndValueDefault extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'star_card_type_and_value_default';
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
//            [['random_prefab_id', 'grid_prefab_id', 'seo_machine_play_count'], 'integer'],
//            [['five_seven_count', 'six_seven_count', 'seven_seven_count', 'eight_seven_count', 'all_orange_count', 'all_cherry_count', 'all_mango_count', 'all_bell_count', 'all_watermelon_count', 'all_red_count', 'all_yellow_count', 'all_blue_count', 'all_seven_count'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'random_prefab_id' => '随机档位',
            'grid_prefab_id' => 'Grid Prefab ID',
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
 
}
