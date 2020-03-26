<?php
namespace common\models\game\byu;

use Yii;

class ByuDefaultOdds extends Byu
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'odds_byu';
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
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [

        ];
    }

    /**
     * 获取默认机率
     * @param $type
     */
    public function findDefault($type)
    {
        return self::find()->where('is_default = 1 and odds_type = :odds_type',array(":odds_type"=>$type))->asArray()->all();
    }

    /**
     *  获取默认值
     */
    public function findDefaultData()
    {
        return self::find()->select('correction_z,pre_correction,suf_correction,float_parameter')->where('is_default = 1 ' )->limit(1)->asArray()->one();

    }
}
