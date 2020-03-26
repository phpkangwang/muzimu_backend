<?php
namespace common\models\game\sbb;

use backend\models\BaseModel;
use common\services\Messenger;
use Yii;


class FivepkPlayerSbbCardtypeandvalueDefault extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_super_big_boss_cardtypeandvalue_default';
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
//            [[ 'prefab_royal_flush_fake', 'prefab_straight_flush_fake', 'prefab_four_of_a_kind_two_ten', 'seo_machine_play_count', 'ty_machine_play_count', 'prefab_force_seven_better', 'prefab_force_seven_better_count', 'prefab_two_three_of_a_kind', 'prefab_six_straight', 'prefab_full_house', 'prefab_flush', 'prefab_three_pairs', 'prefab_straight', 'prefab_three_of_a_kind', 'prefab_two_pairs', 'prefab_seven_better', 'prefab_four_flush', 'prefab_four_straight', 'prefab_seven_better_keep', 'prefab_joker'], 'integer'],
//            [['prefab_five_bars_add_count', 'prefab_five_bars_count', 'prefab_royal_flush_six_add_count', 'prefab_royal_flush_six_count', 'prefab_royal_flush_add_count', 'prefab_royal_flush_count', 'prefab_five_of_a_kind_add_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_six_add_count', 'prefab_straight_flush_six_count', 'prefab_straight_flush_add_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_add_count', 'prefab_four_of_a_kind_count', 'prefab_four_of_a_kind_seven_better_add_count', 'prefab_four_of_a_kind_seven_better_count', 'prefab_full_house_aaakk_add_count', 'prefab_full_house_aaakk_count'], 'number'],
//            [['prefab_five_bars_add_count', 'prefab_five_bars_count', 'prefab_royal_flush_six_add_count', 'prefab_royal_flush_six_count', 'prefab_royal_flush_add_count', 'prefab_royal_flush_count', 'prefab_five_of_a_kind_add_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_six_add_count', 'prefab_straight_flush_six_count', 'prefab_straight_flush_add_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_add_count', 'prefab_four_of_a_kind_count', 'prefab_four_of_a_kind_seven_better_add_count', 'prefab_four_of_a_kind_seven_better_count', 'prefab_full_house_aaakk_add_count', 'prefab_full_house_aaakk_count'], 'required']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
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


}
