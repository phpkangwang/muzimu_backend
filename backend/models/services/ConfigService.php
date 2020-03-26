<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/23
 * Time: 15:47
 */

namespace backend\models\services;


use backend\models\BaseModel;
use backend\models\Tool;
use common\models\DataGameListInfo;

use common\models\game\star97\DataStar97Default;
use common\models\game\star97\DataStar97PrizeId;
use common\services\GameConfig\GameService;
use yii\base\Model;
use yii\helpers\ArrayHelper;

class ConfigService extends BaseModel
{
    //游戏类型
    //public static $game_list = [];
    //配置缓存的key
    public static $key = 'game_config_info';


    public static function getGameConfigList($cache_key = ''){
        //设置游戏类型
       return ConfigService::setGameList();
    }

    public static function setGameList(){

        if(!isset(\Yii::$app->session['game_list'] ) && empty(\Yii::$app->session['game_list'])){
            $data_game_list = DataGameListInfo::find()->filterWhere(['>','game_number',0])->andFilterWhere(['game_switch'=>0])->orderBy('game_index ASC')->all();
            //\Yii::$app->params['game_config_info']['game_list'] = ArrayHelper::map($data_game_list,'game_number','game_name');
            \Yii::$app->session['game_list'] = $data_game_list;
        }else{
            $data_game_list  = \Yii::$app->session['game_list'];
        }
        return  $data_game_list;
    }

    /**
     * 机台配置
     * @param string $type
     * @return array|mixed
     */
    public static function setMachineList($type = ''){
        $type = $type=="ATT" ? "ATT2":$type;
        $game_service = new GameService();
        $game_types = $game_service->game_type;
        return $game_service->getMachineList(array_search($type, $game_types),'room_info_list_id ASC,order_id ASC');
        //$machine_list = ArrayHelper::map($models, 'auto_id', 'seo_machine_id');
    }

    /**
     *   根据机台id获取机台名称
     */
    public static function getMachineName($MachineList,$autoId)
    {
        foreach ($MachineList as $val)
        {
            if($val['auto_id'] == $autoId)
            {
                return $val['seo_machine_id'];
            }
        }
        return "";
    }


    /**
     * 明星97奖项查询
     * @param $type
     * @return mixed
     */
    public static function setStar97PrizeType(){
        //轨迹的筛选奖项
        $prize_list['awards_prize_list'] = DataStar97Default::getPrizeTypeList('id','comments',[['fields' => 'win_type'],['in','item_id',[1,3,4,5]]]);
        //大奖筛选奖型
        $prize_list['prize_list'] = DataStar97Default::getPrizeTypeList('id','comments',[['fields' => 'win_type'],['in','item_id',[1,2,3,4,5]]]);
        //连线的奖项
        $prize_list['prize_line_list'] = DataStar97Default::getPrizeTypeList('item_id','comments',['fields' => 'sline']);
        //大奖奖项
        $prize_list['star97_prize_id_big'] = DataStar97PrizeId::getBigPrizeIdList();
        //大奖奖项
        $prize_list['star97_prize_id'] = DataStar97PrizeId::getPrizeIdList();
        return $prize_list;
    }

    /** 明星97奖的类型
     * @param $bigAward
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function Star97PrizeType($bigAward)
    {
        $data = array();
        if($bigAward == 2){
            //大奖
            $data = DataStar97PrizeId::getBigPrizeIdList();
        }else{
            $data = DataStar97PrizeId::getPrizeIdList();
        }
        foreach ($data as $key => $val){
            $data[$key]['prize_name'] = $val['comments'];
        }
        return $data;
    }

    /** 明星97奖的奖项
     * @param $bigAward
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function Star97PrizeItem($bigAward = 1)
    {
        $obj = new DataStar97Default();
        $data = $obj->tableList();
        $rs = array();
        foreach ($data as $val){
            if ($val['fields'] == "win_type" ) {
                if($bigAward == 2 && $val['item_id'] > 2) {
                    $rs[$val['id']] = $val['comments'];
                }else if($bigAward == 1 && $val['item_id'] > 0) {
                    $rs[$val['id']] = $val['comments'];
                }
            }
        }
        return $rs;
    }




}