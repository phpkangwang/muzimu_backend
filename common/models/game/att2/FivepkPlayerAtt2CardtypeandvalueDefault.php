<?php

namespace common\models\game\att2;

use backend\models\BaseModel;
use common\services\Messenger;
use Yii;
use yii\db\Exception;

class FivepkPlayerAtt2CardtypeandvalueDefault extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_att2_cardtypeandvalue_default';
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
//            [['prefab_royal_flush', 'prefab_royal_flush_random', 'prefab_royal_flush_fake', 'prefab_five_of_a_kind',
//                'prefab_five_of_a_kind_random', 'prefab_straight_flush', 'prefab_straight_flush_random', 'prefab_straight_flush_fake',
//                'prefab_four_of_a_kind_T_T', 'prefab_four_of_a_kind_random_level', 'prefab_full_house', 'prefab_flush', 'prefab_straight',
//                'prefab_three_of_a_kind', 'prefab_two_pairs', 'prefab_seven_better', 'prefab_four_flush', 'prefab_four_straight','prefab_seven_better_keep',
//                'prefab_joker', 'seo_machine_play_count', 'prefab_force_seven_better', 'prefab_force_seven_better_count','prefab_compare_buff',
//                'four_of_kind_random_base_count','straight_flush_random_base_count','royal_flush_random_base_count','five_of_kind_random_base_count'
//            ], 'integer'],
//            [['prefab_royal_flush_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_T_T_count'], 'number'],
//            [['prefab_compare_buff','four_of_kind_random_base_count','straight_flush_random_base_count','royal_flush_random_base_count','five_of_kind_random_base_count'],'required'],
//            [['compare_history_cards'], 'string', 'max' => 50],
        ];
    }



}
