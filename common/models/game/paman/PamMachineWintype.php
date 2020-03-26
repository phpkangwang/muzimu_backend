<?php

namespace common\models\game\paman;

use backend\models\Tool;
use common\models\OddsChangePath;
use Yii;

class PamMachineWintype extends Pam
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_seo_paman_wintype';
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
//            [['jp_rate','jp_rate_base','win_type', 'win_type_rate', 'plan_rate', 'plan_rate_base', 'joker_rate_zero', 'joker_rate_one', 'joker_rate_two', 'one_rate', 'two_rate', 'three_rate', 'four_rate', 'gap', 'gap_random', 'min_bye', 'max_bye', 'rate_award_card_on_location15', 'rate_award_card_on_location3','fake_wintype_statistics','fake_wintype_statistics_top_limit'], 'integer'],
//            [['add_count', 'buff_count'], 'number'],
//            [['room_list_info_id', 'seo_machine_id', 'prize_name', 'card_type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_list_info_id' => '房间ID',
            'seo_machine_id' => '机台ID',
            'prize_name' => '奖名',
            'win_type' => '奖型',
            'win_type_rate' => '奖型概率',
            'plan_rate' => '一带二带概率',
            'plan_rate_base' => '一带二带概率基础值',
            'joker_rate_zero' => '随机小奖零张鬼牌概率',
            'joker_rate_one' => '随机小奖一张鬼牌概率',
            'joker_rate_two' => '随机小奖两张鬼牌概率',
            'one_rate' => '第一手押注',
            'two_rate' => '第二手押注',
            'three_rate' => '第三手押注',
            'four_rate' => '第四手押注',
            'add_count' => '累积值',
            'buff_count' => 'Buff值',
            'gap' => '间隔',
            'gap_random' => '间隔随机',
            'min_bye' => '最小局数',
            'max_bye' => '最大局数',
            'card_type' => 'Card Type',
            'rate_award_card_on_location15' => '一号位和五号位出现的位置',
            'rate_award_card_on_location3' => '3号位出现的位置',
            'jp_rate'=>'jp奖',
            'jp_rate_base'=>'jp',
            'fake_wintype_statistics' => '伪奖的概率可配置',
            'fake_wintype_statistics_top_limit' => '伪奖的概率上限',
        ];
    }

    public function getSeoPaman()
    {
        return $this->hasOne(FivepkSeoPaman::className(),['seo_machine_id'=>'seo_machine_id']);
    }

    public function add($data)
    {
        try {
            //修改这个值必须 记录 修改的值
            $arr = Tool::distinctArr($data,$this->attributes,self::attributeLabels(), "奖:".$this->prize_name);
            if(!empty($arr)){
                $OddsChangePathModel = new OddsChangePath();
                $postData = array(
                    'game_type' => $this->gameType,
                    'type'      => $OddsChangePathModel->typeMachine,
                    'type_id'      => $this->seo_machine_id,
                    'content'   => json_encode($arr,JSON_UNESCAPED_UNICODE),
                );
                $OddsChangePathModel->add($postData);

                foreach ($data as $key => $val) {
                    $this->$key = $val;
                }
                if ( $this->save() ) {
                    return $this->attributes;
                } else {
                    throw new MyException(implode(",", $this->getFirstErrors()));
                }
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }
}
