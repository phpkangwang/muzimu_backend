<?php
namespace common\models\game\snow_leopard;

use Yii;


class SnowLeopardSeoMachineWintypeDefault extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'snow_leopard_seo_machine_wintype_default';
    }


    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [];
    }

 
}
