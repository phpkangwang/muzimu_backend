<?php

namespace common\models\game\big_shark;

use Yii;

/**
 * This is the model class for table "fivepk_default_fivepk_compare_att2".
 *
 * @property integer $id
 * @property string $fivepk_default_fivepk_id
 * @property integer $compare_bet_win
 * @property integer $compare_bet
 * @property integer $big_small
 * @property integer $compare_card
 * @property string $last_time
 */
class FivepkDefaultFivepkCompareBigShark extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_default_fivepk_compare_bigshark';
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
            [['fivepk_default_fivepk_id', 'compare_bet_win', 'compare_bet', 'big_small', 'compare_card', 'last_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fivepk_default_fivepk_id' => 'Fivepk Default Fivepk ID',
            'compare_bet_win' => 'Compare Bet Win',
            'compare_bet' => 'Compare Bet',
            'big_small' => 'Big Small',
            'compare_card' => 'Compare Card',
            'last_time' => 'Last Time',
        ];
    }

    public function getCompareByFivepkId($fivepkIdArr){
        return self::find()->andWhere(['in','fivepk_default_fivepk_id',$fivepkIdArr])->asArray()->all();
    }
}
