<?php
namespace common\models\game;

use backend\models\BaseModel;
use phpDocumentor\Reflection\Types\Self_;
use Yii;

/**
 * This is the model class for table "data_fivepk_newer".
 *
 * @property string $id
 * @property integer $game_type
 * @property integer $share_count
 * @property string $sub_seo_machine_id
 * @property integer $newer_contribution_rate_id
 * @property integer $win_type_id
 * @property integer $rate
 * @property string $last_time
 */
class DataFivepkNewer extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_fivepk_newer';
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
            [['game_type', 'share_count', 'newer_contribution_rate_id', 'win_type_id', 'rate'], 'integer'],
            [['last_time'], 'safe'],
            [['room_index'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'game_type' => '游戏类型',
            'share_count' => '分享次数',
            'sub_seo_machine_id' => '场次',
            'newer_contribution_rate_id' => '门槛data_newer_contribution_rate表id',
            'win_type_id' => '奖型id',
            'rate' => '占比',
            'last_time' => 'Last Time',
        ];
    }

    public function getContribution(){
        return $this->hasOne(DataFivepkNewerContributionRate::className(),['id'=>'newer_contribution_rate_id']);
    }

    public function getWinType(){
        return $this->hasOne(DataFivepkNewerWinType::className(),['id'=>'win_type_id']);
    }

    public function tableList($gameType, $params)
    {
        $level = $params['level'];
        $share = $params['share'];

        return Self::find()->joinWith(['winType'])->joinWith(['contribution'])->filterWhere(['data_fivepk_newer.game_type'=> $gameType,'room_index'=>$level])
            ->andFilterWhere(['share_count'=>$share])
            ->orderBy('share_count ASC')
            ->asArray()
            ->all();
    }
 
}
