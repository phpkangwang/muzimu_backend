<?php

namespace common\models;

use backend\models\redis\MyRedis;
use backend\models\Tool;
use common\models\game\base\GameBase;
use common\models\game\big_plate\BigPlateService;
use common\models\game\big_shark\BigSharkService;
use common\models\game\star97\Star97Service;
use common\services\fire_unicorn\FireUnicornService;
use common\services\fire_phoenix\PhoenixService;
use Yii;

/**
 * This is the model class for table "data_game_list_info".
 *
 * @property string $id
 * @property integer $game_number
 * @property string $game_name
 * @property string $game_version
 * @property string $game_json
 * @property integer $game_switch
 * @property string $machine_icon
 * @property string $icon_download
 * @property string $icon_two
 * @property string $icon_three
 * @property string $machine_close_icon
 * @property string $title_effect_id
 * @property string $room_effect_id
 * @property integer $score
 * @property integer $coin
 * @property string $game_notice
 * @property string $game_white_ip
 * @property string $game_res_url
 * @property string $game_server_port
 * @property string $game_server_ip
 * @property integer $game_index
 * @property integer $game_version_id
 * @property integer $activity_switch
 */
class DataGameListInfo extends \backend\models\BaseModel
{
    public $gameSwitchOpen = 0;
    public $gameSwitchClose = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'data_game_list_info';
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
//            [['id', 'game_number', 'game_name', 'game_version'], 'required'],
//            [['id', 'game_number', 'game_switch', 'score', 'coin', 'game_index', 'game_version_id', 'activity_switch'], 'integer'],
//            [['game_name', 'game_version'], 'string', 'max' => 25],
//            [['game_json', 'machine_icon', 'icon_download', 'icon_two', 'icon_three', 'machine_close_icon', 'title_effect_id', 'room_effect_id', 'game_notice', 'game_res_url', 'game_white_ip', 'game_server_port', 'game_server_ip'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'game_number'        => '游戏id',
            'game_name'          => '游戏名称',
            'game_version'       => '游戏版本号',
            'game_json'          => '版本文件名',
            'game_switch'        => '0-开启1-关闭',
            'machine_icon'       => 'Machine Icon',
            'icon_download'      => 'Icon Download',
            'icon_two'           => 'Icon Two',
            'icon_three'         => 'Icon Three',
            'machine_close_icon' => 'Machine Close Icon',
            'title_effect_id'    => '标题特效',
            'room_effect_id'     => '房间标题特效',
            'score'              => '分数上限',
            'coin'               => '钻石上限',
            'game_notice'        => '系统公告',
            'game_res_url'       => '客户端资源地址',
            'game_white_ip'      => '白名单',
            'game_server_port'   => '游戏服端口号',
            'game_server_ip'     => '游戏服ip',
            'game_index'         => '游戏索引',
            'game_version_id'    => '控制版本开关',
            'activity_switch'    => '活动开关',
        ];
    }

    /**
     * 添加修改
     * @param $data
     * @return array
     */
    public function add($data)
    {
        try {
            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            if ( $this->save()) {
                $MyRedis = new MyRedis();
                $MyRedis->clear("game:DataGameListInfo*");
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 获得玩家记录
     * @param $account_id
     * @return null|string
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function getPlayerRecord($account_id)
    {
        $result = null;
        switch ($this->game_number) {
            case Yii::$app->params['fire_phoenix']:
                $result = PhoenixService::getPlayerRecord($account_id);
                break;
            case Yii::$app->params['big_plate']:
                $result = BigPlateService::getPlayerRecord($account_id);
                break;
            case Yii::$app->params['star97']:
                $result = Star97Service::getPlayerRecord($account_id);
                break;
            case Yii::$app->params['big_shark']:
                $result = BigSharkService::getPlayerRecord($account_id);
                break;
            case Yii::$app->params['fire_unicorn']:
                $result = FireUnicornService::getPlayerRecord($account_id);
                break;
        }

        return $result;
    }

    /**
     * 玩家更新
     * @param $account_id
     * @return array|null
     */
    public function getPlayerUpdate($account_id)
    {
        $result = null;
        switch ($this->game_number) {
            case Yii::$app->params['fire_phoenix']:
                $result = PhoenixService::getPlayerOdds($account_id);
                break;
            case Yii::$app->params['big_plate']:
                $result = BigPlateService::getPlayerOdds($account_id);
                break;
            case Yii::$app->params['star97']:
                $result = Star97Service::getPlayerOdds($account_id);
                break;
            case Yii::$app->params['big_shark']:
                $result = BigSharkService::getPlayerOdds($account_id);
                break;
            case Yii::$app->params['fire_unicorn']:
                $result = FireUnicornService::getPlayerOdds($account_id);
                break;
        }
        return $result;
    }


    public function getDefaultProbability($account_id)
    {
        $result = null;

        switch ($this->game_number) {
            case Yii::$app->params['fire_phoenix']:
                $result = PhoenixService::getPlayerOddsDefault($account_id);
                break;
            case Yii::$app->params['big_plate']:
                $result = BigPlateService::getPlayerOddsDefault($account_id);
                break;
            case Yii::$app->params['star97']:
                $result = Star97Service::getPlayerOdds($account_id);
                break;
            case Yii::$app->params['big_shark']:
                $result = BigSharkService::getPlayerOddsDefault($account_id);
                break;
            case Yii::$app->params['fire_unicorn']:
                $result = FireUnicornService::getPlayerOddsDefault($account_id);
                break;
        }

        return $result;
    }

    /**
     * 获取所有开启的游戏
     * @return array
     */
    public function getOpenGame()
    {
        $redisKey  = "game:DataGameListInfo:getopenGame";
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            //$data = self::find()->filterWhere(['game_switch' => 0])->andFilterWhere(['>', 'game_number', 0])->andFilterWhere(['<', 'game_number', 100])->orderBy('sort ASC')->asArray()->all();
            $data = self::find()->andFilterWhere(['>', 'game_number', 0])->andFilterWhere(['<', 'game_number', 100])->orderBy('sort ASC')->asArray()->all();
            foreach ($data as $key => $val) {
                $configGame = Yii::$app->params['game'];
                foreach ($configGame as $gameKey => $game) {
                    if ($val['game_name'] == $game) {
                        $data[$key]['shortGame'] = $gameKey;
                    }
                }
            }
            $this->MyRedis->set($redisKey, json_encode($data));
            return $data;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
     * 获取FG所有开启的游戏
     * @return array
     */
    public function getFgOpenGame()
    {
        $redisKey  = "game:DataGameListInfo:getFgOpenGame";
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            $data = self::find()->filterWhere(['game_switch' => 0])->andFilterWhere(['>', 'game_number', 100])->orderBy('game_index ASC')->asArray()->all();
            $this->MyRedis->set($redisKey, json_encode($data));
            return $data;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
     *  获取列表
     * @return array
     */
    public function tableList()
    {
        $redisKey  = "game:DataGameListInfo:tableList";
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            $data = self::find()->orderBy('sort ASC')->asArray()->all();
            $this->MyRedis->set($redisKey, json_encode($data));
            return $data;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
     * 查找基本数据
     * @param $id
     * @return DataGameListInfo|mixed|null
     */
    public function findBase($id)
    {
        $redisKey  = "game:dataGameListInfo:" . $id;
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            $obj = self::find()->where(['id' => $id])->asArray()->one();
            $this->MyRedis->set($redisKey, json_encode($obj));
            return $obj;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
 * 通过游戏名称获取详情
 * @param $gameName 游戏名称
 * @return array
 */
    public function findByGameName($gameName)
    {
        $redisKey  = "game:DataGameListInfo:findByGameName:" . $gameName;
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            $data = self::find()->where(['game_name' => $gameName])->asArray()->one();
            $this->MyRedis->set($redisKey, json_encode($data));
            return $data;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
     * 通过游戏名称获取详情
     * @param $gameType 游戏类型
     * @return array
     */
    public function findByGameType($gameType)
    {
        $redisKey  = "game:DataGameListInfo:findByGameType:" . $gameType;
        $redisData = $this->MyRedis->get($redisKey);
        if (empty($redisData)) {
            $data = self::find()->where(['game_number' => $gameType])->asArray()->one();
            $this->MyRedis->set($redisKey, json_encode($data));
            return $data;
        } else {
            return json_decode($redisData, true);
        }
    }

    /**
     *   获取关服时间
     */
    public function getGameOpenTime($id)
    {
        $obj             = self::findOne($id);
        $last_open_time  = $obj->last_open_time;
        $last_close_time = $obj->last_close_time;
        $time            = $last_open_time - $last_close_time;
        return $time > 0 ? $time : 0;
    }

    /**
     *   游戏是否开启
     */
    public function gameIsOpen()
    {
        $id  = 0;
        $obj = self::findOne($id);
        if ($obj->game_switch == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *  获取这一天体验场总玩局数 只包含新轨迹
     * @param $day
     */
    public function getTyPlayTotalCount($day){
        $playNum = 0;
        $games = $this->getOpenGame();
        $GameBaseObj = new GameBase();
        foreach ($games as $val)
        {
            if( $val['shortGame'] == "BYU" ){
                continue;
            }
            $GameObj = $GameBaseObj->initGameObj($val['shortGame']);
            //查询体验场所有记录
            $tySql = "select sum(play_number) as sum
                    from {$GameObj->tablePrizeDay}
                    where account_id <> 0 and (machine_auto_id = 0 or machine_auto_id = -1) and room_index = 1
                    and create_time = '{$day}'
            ";
            $obj = Yii::$app->game_db->createCommand($tySql)->queryOne();
            $playNum += $obj['sum'];
        }
        return $playNum;
    }


}
