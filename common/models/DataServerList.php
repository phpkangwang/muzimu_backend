<?php
namespace common\models;

use backend\models\BaseModel;
use backend\models\remoteInterface\remoteInterface;
use common\models\game\base\GameBase;
use Yii;


class DataServerList extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_service_list';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('game_db');
    }

    /**
     * 查询调用的java接口
     * @param $gameType
     * @return string
     */
    public function findByGameType($gameType)
    {
        $obj = self::find()->where('game_type = :gameType', array(':gameType'=>$gameType))->asArray()->one();
        if( empty($obj) || $obj['game_type'] == 0 ){
            return Yii::$app->params['url'];
        }else{
            return "http://".$obj['in_ip'].":".$obj['tomcat_port']."/api";
            //return "106.14.204.137".":".$obj['tomcat_port'];
        }
    }

    public function getServiceList()
    {
        return array(
            array(
                'gameName'=>"平台游戏",
                'gameType'=>"0",
                ),
            array(
                'gameName'=>"捕鱼游戏",
                'gameType'=>"13",
            ),
        );
    }

}
