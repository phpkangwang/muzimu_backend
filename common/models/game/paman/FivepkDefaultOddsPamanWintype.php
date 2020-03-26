<?php

namespace common\models\game\paman;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "fivepk_default_odds_paman_wintype".
 *
 * @property string $id ID主键
 * @property string $room_info_list_id
 * @property string $prize_name 名称
 * @property int $win_type 奖型
 * @property int $win_type_rate 奖型概率
 * @property int $plan_rate 一带二带概率
 * @property int $plan_rate_base 一带二带概率基础值
 * @property int $joker_rate_zero 随机小奖零张鬼牌概率
 * @property int $joker_rate_one 随机小奖一张鬼牌概率
 * @property int $joker_rate_two 随机小奖两张鬼牌概率
 * @property int $jp_rate jp奖概率
 * @property int $jp_rate_base jp奖概率基础值
 * @property int $one_rate 第一手押注
 * @property int $two_rate 第二手押注
 * @property int $three_rate 第三手押注
 * @property int $four_rate 第四手押注
 * @property double $add_count 累积值
 * @property string $card_type
 * @property int $rate_award_card_on_location15 一号位和五号位出现的位置
 * @property int $rate_award_card_on_location3 3号位出现的位置
 * @property int $is_big 0-小奖1-大奖
 */
class FivepkDefaultOddsPamanWintype extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_default_odds_paman_wintype';
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
            [['win_type', 'win_type_rate', 'plan_rate', 'plan_rate_base', 'joker_rate_zero', 'joker_rate_one', 'joker_rate_two', 'jp_rate', 'jp_rate_base', 'one_rate', 'two_rate', 'three_rate', 'four_rate', 'rate_award_card_on_location15', 'rate_award_card_on_location3', 'is_big','fake_wintype_statistics','fake_wintype_statistics_top_limit'], 'integer'],
            [['add_count'], 'number'],
            [['room_info_list_id', 'prize_name', 'card_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID主键',
            'room_info_list_id' => 'Room List Info ID',
            'prize_name' => '名称',
            'win_type' => '奖型',
            'win_type_rate' => '奖型概率',
            'plan_rate' => '一带二带概率',
            'plan_rate_base' => '一带二带概率基础值',
            'joker_rate_zero' => '随机小奖零张鬼牌概率',
            'joker_rate_one' => '随机小奖一张鬼牌概率',
            'joker_rate_two' => '随机小奖两张鬼牌概率',
            'jp_rate' => 'jp奖概率',
            'jp_rate_base' => 'jp奖概率基础值',
            'one_rate' => '第一手押注',
            'two_rate' => '第二手押注',
            'three_rate' => '第三手押注',
            'four_rate' => '第四手押注',
            'add_count' => '累积值',
            'card_type' => 'Card Type',
            'rate_award_card_on_location15' => '一号位和五号位出现的位置',
            'rate_award_card_on_location3' => '3号位出现的位置',
            'is_big' => '0-小奖1-大奖',
            'fake_wintype_statistics' => '伪奖的概率可配置',
            'fake_wintype_statistics_top_limit' => '伪奖的概率上限',
        ];
    }

    public function findBase($id)
    {
        return self::find()->filterWhere(['room_info_list_id'=>$id])->asArray()->all();
    }

    public function add($data)
    {
        $models = self::find()->filterWhere(['room_info_list_id'=> $data['id']])->all();
        foreach ($models as $model) {
            $model->load($data['data'][$model->prize_name]);
            $model->validate() && $model->save();
        }
        return ArrayHelper::toArray($models);
    }
}
