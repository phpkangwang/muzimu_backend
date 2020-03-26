<?php
namespace common\models\game\big_plate;

use common\services\Messenger;
use Yii;
use yii\db\Exception;


class FivepkPlayerBigPlateCardTypeAndValueDefault extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_bigplate_cardtypeandvalue_default';
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
//            [['prefab_royal_flush', 'prefab_royal_flush_random', 'prefab_royal_flush_fake', 'prefab_five_of_a_kind', 'prefab_five_of_a_kind_random', 'prefab_straight_flush', 'prefab_straight_flush_random', 'prefab_straight_flush_fake', 'prefab_four_of_a_kind_T_T', 'prefab_four_of_a_kind_two_ten', 'prefab_full_house', 'prefab_full_house_jp', 'prefab_flush', 'prefab_flush_jp', 'prefab_straight', 'prefab_straight_jp', 'prefab_three_of_a_kind', 'prefab_three_of_a_kind_jp', 'prefab_two_pairs', 'prefab_two_pairs_jp', 'prefab_seven_better', 'prefab_seven_better_jp', 'prefab_four_flush', 'prefab_four_straight', 'prefab_seven_better_keep', 'prefab_joker', 'seo_machine_play_count', 'prefab_force_seven_better', 'prefab_force_seven_better_count', 'prefab_compare_buff', 'prefab_compare_cut_down', 'prefab_compare_cut_down_count', 'prefab_compare_seven_joker', 'prefab_random_two_times'], 'integer'],
//            [['prefab_royal_flush_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_T_T_count'], 'number'],
//            [['prefab_five_of_a_kind_double', 'prefab_royal_flush_double', 'prefab_straight_flush_double', 'prefab_four_of_a_kind_double'], 'string'],
//            [['compare_history_cards'], 'string', 'max' => 50],
//            [['prefab_four_of_a_kind_T_T_count','prefab_straight_flush_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_compare_buff'],'number','min'=>0,'max'=>'999999999'],
//            [['prefab_four_of_a_kind_T_T_count','prefab_straight_flush_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_compare_buff'],'required'],
//            [['prefab_four_of_a_kind_T_T_count','prefab_straight_flush_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_compare_buff'],'default','value'=>0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
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

}
