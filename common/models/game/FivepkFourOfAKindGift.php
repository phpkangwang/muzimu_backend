<?php
namespace common\models\game;

use backend\models\BaseModel;
use Yii;

/**
 * This is the model class for table "fivepk_four_of_a_kind_gift".
 *
 * @property string $id
 * @property integer $game_number
 * @property double $prefab_four_of_a_kind_T_T_count_gift
 * @property integer $gap_gift
 * @property integer $gap_random_gift
 * @property integer $min_bye_gift
 * @property integer $max_bye_gift
 */
class FivepkFourOfAKindGift extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_four_of_a_kind_gift';
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
            [['game_number'], 'required'],
            [['game_number', 'gap_gift', 'gap_random_gift', 'min_bye_gift', 'max_bye_gift'], 'integer'],
            [['prefab_four_of_a_kind_T_T_count_gift'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID主键',
            'game_number' => '游戏id',
            'prefab_four_of_a_kind_T_T_count_gift' => '四梅强补BUFFER',
            'gap_gift' => '四梅强补间隔',
            'gap_random_gift' => '四梅强补间隔随机',
            'min_bye_gift' => 'Min Bye Gift',
            'max_bye_gift' => 'Max Bye Gift',
        ];
    }

    /**
     *  根据字段查找一条数据
     * @param $gameType
     * @param $machineId
     * @return array
     */
   public function findByGameTypeMachineId($gameType, $machineId)
   {
        return self::find()->where('game_number = :game_number and seo_machine_id like \'%:seo_machine_id%\' ',array(
            ':game_number' => $gameType, ':seo_machine_id' => $machineId
        ))->one();
   }
 
}
