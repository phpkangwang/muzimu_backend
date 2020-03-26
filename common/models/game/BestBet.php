<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/3/003
 * Time: 11:18
 */

namespace common\models\game;

use Yii;
use yii\base\Model;


/**
 * @property string $auto_id
 * @property string $game_type
 * @property string $room_type
 * @property integer $is_open
 * @property integer $play_limit_time
 * @property integer $machine_first_reward_min_play_count
 * @property integer $machine_first_reward_max_play_count
 * @property integer $machine_diamond_one
 * @property double $machine_diamond_one_percent
 * @property integer $machine_diamond_two
 * @property double $machine_diamond_two_percent
 * @property integer $machine_diamond_three
 * @property double $machine_diamond_three_percent
 * @property integer $machine_diamond_min_play_count
 * @property integer $machine_diamond_max_play_count
 * @property integer $room_diamond_one
 * @property integer $room_diamond_one_play_count
 * @property integer $room_diamond_two
 * @property integer $room_diamond_two_play_count
 * @property integer $total_play_count_two
 */
class BestBet extends Model
{
    public $sum = 100;
    public $auto_id;
    public $is_open;
    public $play_limit_time;
    public $machine_first_reward_min_play_count;
    public $machine_first_reward_max_play_count;
    public $machine_diamond_one;
    public $machine_diamond_one_percent;
    public $machine_diamond_two;
    public $machine_diamond_two_percent;
    public $machine_diamond_three;
    public $machine_diamond_three_percent;
    public $machine_diamond_min_play_count;
    public $machine_diamond_max_play_count;
    public $room_diamond_one;
    public $room_diamond_one_play_count;
    public $room_diamond_two;
    public $room_diamond_two_play_count;
    public $total_play_count_two;
    public $total_play_count;

    public function rules()
    {
        return [
            [['auto_id','is_open', 'play_limit_time', 'machine_first_reward_min_play_count', 'machine_first_reward_max_play_count', 'machine_diamond_one', 'machine_diamond_two', 'machine_diamond_three', 'machine_diamond_min_play_count', 'machine_diamond_max_play_count', 'room_diamond_one', 'room_diamond_one_play_count', 'room_diamond_two', 'room_diamond_two_play_count', 'total_play_count_two', 'total_play_count'], 'integer'],
            [['machine_diamond_one_percent', 'machine_diamond_two_percent', 'machine_diamond_three_percent'], 'number','min'=>0],
            [['machine_first_reward_min_play_count','machine_diamond_min_play_count'],'compare', 'compareValue' => 0, 'operator' => '>' ,'message'=>'必须大于0'],
            [['is_open', 'play_limit_time', 'machine_first_reward_min_play_count', 'machine_first_reward_max_play_count', 'machine_diamond_one', 'machine_diamond_two', 'machine_diamond_three', 'machine_diamond_min_play_count', 'machine_diamond_max_play_count', 'room_diamond_one', 'room_diamond_one_play_count', 'room_diamond_two', 'room_diamond_two_play_count','machine_diamond_one_percent', 'machine_diamond_two_percent', 'machine_diamond_three_percent'], 'required'],
            [['machine_first_reward_min_play_count','machine_first_reward_max_play_count'],'validateBigLittle1'],
            [['machine_diamond_min_play_count','machine_diamond_max_play_count'],'validateBigLittle2'],
            [['machine_diamond_one_percent', 'machine_diamond_two_percent', 'machine_diamond_three_percent'], 'validatePercent'],
        ];
    }

    public function validateBigLittle1($attribute)
    {
        if($this->machine_first_reward_min_play_count >= $this->machine_first_reward_max_play_count){
            $this->addError($attribute, '首次最小局必须小于首次最大局');
        }
    }

    public function validateBigLittle2($attribute)
    {
        if($this->machine_diamond_min_play_count >= $this->machine_diamond_max_play_count){
            $this->addError($attribute, '机台局数最小值必须小于机台局数最大值');
        }
    }

    public function validatePercent($attribute)
    {
        $sum = $this->machine_diamond_one_percent+$this->machine_diamond_two_percent+$this->machine_diamond_three_percent;
        if($this->sum != $sum){
            $this->addError($attribute, '机台中奖率的和必须为100');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'auto_id' => 'ID主键',
            'game_type' => '游戏类型',
            'room_type' => '房间类型',
            'is_open' => '是否开启（0-关，1-开）',
            'play_limit_time' => '体验场体验限时（秒）',
            'machine_first_reward_min_play_count' => '体验场首次最小局数',
            'machine_first_reward_max_play_count' => '体验场首次最大局数',
            'machine_diamond_one' => '机台送钻1',
            'machine_diamond_one_percent' => '机台送钻1概率',
            'machine_diamond_two' => '机台送钻2',
            'machine_diamond_two_percent' => '机台送钻2概率',
            'machine_diamond_three' => '机台送钻3',
            'machine_diamond_three_percent' => '机台送钻3概率',
            'machine_diamond_min_play_count' => '机台送钻出奖局数最小值',
            'machine_diamond_max_play_count' => '机台送钻出奖局数最大值',
            'room_diamond_one' => '房间送钻1',
            'room_diamond_one_play_count' => '房间送钻1局数',
            'room_diamond_two' => '房间送钻2',
            'room_diamond_two_play_count' => '房间送钻2局数',
            'total_play_count_two'=>'房间累积局数',
        ];
    }

    public function LoadData($game_type,$room_type)
    {
        $game_type = Tool::gameNameToRealName($game_type);
        $data_ty_diamond_setting = DataTyDiamondSetting::find()->filterWhere(['game_type'=>$game_type,'room_type'=>$room_type])->asArray()->one();
        $fivepk_ty_diamond_setting = FivepkTyDiamondSetting::find()->filterWhere(['game_type'=>$game_type,'room_type'=>$room_type])->asArray()->one();
        return array_merge($data_ty_diamond_setting,$fivepk_ty_diamond_setting);
        //return $this->load(['BestBet'=>array_merge($data_ty_diamond_setting,$fivepk_ty_diamond_setting)]);
    }

    public function saveData($game_type,$room_type,$data)
    {
        //开启事务
        $tr = Yii::$app->game_db->beginTransaction();
        $data_ty_diamond_setting = DataTyDiamondSetting::find()->filterWhere(['game_type'=>$game_type,'room_type'=>$room_type])->one();
        $DataTyDiamondSettingColumns = $data_ty_diamond_setting->attributes();

        $fivepk_ty_diamond_setting = FivepkTyDiamondSetting::find()->filterWhere(['game_type'=>$game_type,'room_type'=>$room_type])->one();
        $FivepkTyDiamondSettingColumns = $fivepk_ty_diamond_setting->attributes();

        foreach ($data as $key=>$val){
            if( in_array($key, $DataTyDiamondSettingColumns)){
                $data_ty_diamond_setting->$key = $val;
            }
            if( in_array($key, $FivepkTyDiamondSettingColumns)){
                $fivepk_ty_diamond_setting->$key = $val;
            }
        }
        $data_ty_diamond_setting -> save();
        $fivepk_ty_diamond_setting -> save();
        $tr->commit();
        return true;
    }
}