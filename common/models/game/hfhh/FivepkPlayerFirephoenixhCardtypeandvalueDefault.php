<?php
namespace common\models\game\hfhh;

use Yii;


class FivepkPlayerFirephoenixhCardtypeandvalueDefault extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_player_firephoenixh_cardtypeandvalue_default';
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
//            [['prefab_five_bars', 'prefab_royal_flush', 'prefab_royal_flush_fake', 'prefab_five_of_a_kind', 'prefab_five_of_a_kind_compare', 'prefab_straight_flush', 'prefab_straight_flush_fake', 'prefab_four_of_a_kind_joker_two', 'prefab_four_of_a_kind_Joker_two_fourteen_two', 'prefab_four_of_a_kind_J_A', 'prefab_four_of_a_kind_ja', 'prefab_four_of_a_kind_T_T', 'prefab_four_of_a_kind_two_ten', 'prefab_four_of_a_kind_two_ten_two', 'prefab_four_of_a_kind_two_ten_continue_rate', 'prefab_full_house', 'prefab_flush', 'prefab_straight', 'prefab_three_of_a_kind', 'prefab_two_pairs', 'prefab_seven_better', 'prefab_four_flush', 'prefab_four_straight', 'prefab_seven_better_keep', 'prefab_joker', 'seo_machine_play_count', 'machine_auto', 'prefab_force_seven_better', 'prefab_force_seven_better_count', 'prefab_compare_buff', 'prefab_compare_cut_down', 'prefab_compare_cut_down_count', 'prefab_compare_seven_joker'], 'integer'],
//            [['prefab_five_bars_count', 'prefab_royal_flush_count', 'prefab_five_of_a_kind_count', 'prefab_straight_flush_count', 'prefab_four_of_a_kind_Joker_count_two', 'prefab_four_of_a_kind_J_A_count', 'prefab_four_of_a_kind_T_T_count', 'prefab_four_of_a_kind_two_ten_continue_count'], 'number'],
//            [['prefab_five_of_a_kind_compare_record', 'prefab_four_of_a_kind_Joker_two_fourteen_record', 'prefab_four_of_a_kind_two_ten_continue_record'], 'string', 'max' => 2555],
//            [['prefab_four_of_a_kind_two_ten_continue'], 'string', 'max' => 255],
//            [['compare_history_cards'], 'string', 'max' => 50],
//            [['prefab_four_of_a_kind_T_T_count','prefab_four_of_a_kind_J_A_count','prefab_four_of_a_kind_Joker_count_two','prefab_compare_buff','prefab_four_of_a_kind_two_ten_continue_count','prefab_five_bars_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_straight_flush_count','prefab_four_of_a_kind_Joker_count_two'],'number','min'=>0,'max'=>999999999],
//            [['prefab_four_of_a_kind_T_T_count','prefab_four_of_a_kind_J_A_count','prefab_four_of_a_kind_Joker_count_two','prefab_compare_buff','prefab_four_of_a_kind_two_ten_continue_count','prefab_five_bars_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_straight_flush_count','prefab_four_of_a_kind_Joker_count_two'],'required'],
//            [['prefab_four_of_a_kind_T_T_count','prefab_four_of_a_kind_J_A_count','prefab_four_of_a_kind_Joker_count_two','prefab_compare_buff','prefab_four_of_a_kind_two_ten_continue_count','prefab_five_bars_count','prefab_royal_flush_count','prefab_five_of_a_kind_count','prefab_straight_flush_count','prefab_four_of_a_kind_Joker_count_two'],'default','value'=>0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'prefab_five_bars' => '五鬼',
            'prefab_five_bars_count' => '五鬼Buffer',
            'prefab_royal_flush' => '同花大顺',
            'prefab_royal_flush_fake' => '假大顺',
            'prefab_royal_flush_count' => '同花大顺Buffer',
            'prefab_five_of_a_kind' => '五梅',
            'prefab_five_of_a_kind_count' => '五梅Buffer',
            'prefab_five_of_a_kind_compare' => '五梅比倍',
            'prefab_five_of_a_kind_compare_record' => 'Prefab Five Of A Kind Compare Record',
            'prefab_straight_flush' => '同花小顺',
            'prefab_straight_flush_fake' => '假小顺',
            'prefab_straight_flush_count' => '小顺Buffer',
            'prefab_four_of_a_kind_joker_two' => '正宗大四梅',
            'prefab_four_of_a_kind_Joker_count_two' => '正宗大四梅累积Buffer',
            'prefab_four_of_a_kind_Joker_two_fourteen_two' => '正宗大四梅累积倍数',
            'prefab_four_of_a_kind_Joker_two_fourteen_record' => 'Prefab Four Of A Kind  Joker Two Fourteen Record',
            'prefab_four_of_a_kind_J_A' => '大四梅累积值',
            'prefab_four_of_a_kind_J_A_count' => '大四梅累积值Buffer',
            'prefab_four_of_a_kind_ja' => '大四梅出现率',
            'prefab_four_of_a_kind_T_T' => '小四梅累积值',
            'prefab_four_of_a_kind_T_T_count' => '小四梅累积值Buffer',
            'prefab_four_of_a_kind_two_ten' => '小四梅出现率',
            'prefab_four_of_a_kind_two_ten_two' => '连庄开关',
            'prefab_four_of_a_kind_two_ten_continue' => 'Prefab Four Of A Kind Two Ten Continue',
            'prefab_four_of_a_kind_two_ten_continue_count' => '连庄Buffer',
            'prefab_four_of_a_kind_two_ten_continue_record' => 'Prefab Four Of A Kind Two Ten Continue Record',
            'prefab_four_of_a_kind_two_ten_continue_rate' => '连庄数',
            'prefab_full_house' => '葫芦',
            'prefab_flush' => '同花',
            'prefab_straight' => '顺子',
            'prefab_three_of_a_kind' => '三条',
            'prefab_two_pairs' => '两对',
            'prefab_seven_better' => '一对',
            'prefab_four_flush' => '四张同花',
            'prefab_four_straight' => '四张顺',
            'prefab_seven_better_keep' => '小一对',
            'prefab_joker' => '鬼牌',
            'seo_machine_play_count' => 'Seo Machine Play Count',
            'machine_auto' => 'Machine Auto',
            'compare_history_cards' => 'Compare History Cards',
            'prefab_force_seven_better' => '强制一对',
            'prefab_force_seven_better_count' => 'Prefab Force Seven Better Count',
            'prefab_compare_buff' => '比倍Buffer',
            'prefab_compare_cut_down' => '比倍砍牌',
            'prefab_compare_cut_down_count' => 'Prefab Compare Cut Down Count',
            'prefab_compare_seven_joker' => '比倍7鬼翻倍',
        ];
    }


}
