<?php
namespace common\models;

use backend\models\BaseModel;
use Yii;


class DataReservation extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_reservation';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }
}
