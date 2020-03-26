<?php
namespace common\models\game\star97\core;

use Yii;

/**
 * This is the model class for table "machine_reward_pool_star97".
 *
 * @property integer $id
 * @property integer $pool_id
 * @property string $seo_machine_id
 * @property double $play_add_buff_count
 * @property integer $current_reward_type
 * @property double $current_buff_count
 * @property double $all_watermelon_total_count
 * @property double $all_bell_total_count
 * @property double $all_orange_total_count
 * @property double $all_mango_total_count
 * @property double $five_seven_total_count
 * @property double $six_seven_total_count
 * @property double $seven_seven_total_count
 * @property double $eight_seven_total_count
 */
class MachineRewardPoolStar97 extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'machine_reward_pool_star97';
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
            [['pool_id', 'current_reward_type'], 'integer'],
            [['seo_machine_id'], 'required'],
            [['play_add_buff_count', 'current_buff_count', 'all_watermelon_total_count', 'all_bell_total_count', 'all_orange_total_count', 'all_mango_total_count', 'five_seven_total_count', 'six_seven_total_count', 'seven_seven_total_count', 'eight_seven_total_count'], 'number'],
            [['current_reward_type', 'play_add_buff_count', 'current_buff_count', 'all_watermelon_total_count', 'all_bell_total_count', 'all_orange_total_count', 'all_mango_total_count', 'five_seven_total_count', 'six_seven_total_count', 'seven_seven_total_count', 'eight_seven_total_count'], 'required'],
            [['seo_machine_id'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pool_id' => '奖池id',
            'seo_machine_id' => 'Seo Machine ID',
            'play_add_buff_count' => '每局累积的buff值',
            'current_reward_type' => '当前奖池累积出奖类型',
            'current_buff_count' => '当前奖池累积buff值',
            'all_watermelon_total_count' => '全盘西瓜触顶值',
            'all_bell_total_count' => '全盘铃铛触顶值',
            'all_orange_total_count' => '全盘橘子触顶值',
            'all_mango_total_count' => '全盘芒果触顶值',
            'five_seven_total_count' => '5个7触顶值',
            'six_seven_total_count' => '6个7触顶值',
            'seven_seven_total_count' => '7个7触顶值',
            'eight_seven_total_count' => '8个7触顶值',
        ];
    }


}
