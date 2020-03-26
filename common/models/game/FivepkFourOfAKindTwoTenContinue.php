<?php
namespace common\models\game;

use backend\models\BaseModel;
use backend\models\Tool;
use Yii;

class FivepkFourOfAKindTwoTenContinue extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_four_of_a_kind_two_ten_continue';
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
            'id' => 'ID主键',
            'fivepk_path_id' => 'Data Room List Info ID',
            'one' => '四梅1连庄',
            'two' => '四梅2连庄',
            'three' => '四梅3连庄',
            'four' => '四梅4连庄',
            'five' => '四梅5连庄',
            'six' => '四梅6连庄',
            'seven' => '四梅7连庄',
            'eight' => '四梅8连庄',
            'nine' => '四梅9连庄',
            'ten' => '四梅10连庄',
            'eleven' => '四梅11连庄',
            'twelve' => '四梅12连庄',
            'thirteen' => '四梅13连庄',
            'fourteen' => '四梅14连庄',
        ];
    }



    /**
     * 根据fivepk_path_id 获取数据
     * @param $fivepkPathIds
     * @return  array
     */
    public function findsByPathId($fivepkPathIds){
        $inStr = Tool::arrayToSqlInStr($fivepkPathIds);
        $sql = "select * from ".self::tableName()." where fivepk_path_id in ({$inStr})";
        $data = Yii::$app->game_db->createCommand($sql)->queryAll();
        return $data;
    }


}
