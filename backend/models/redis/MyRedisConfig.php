<?php

namespace backend\models\redis;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use Yii;

class MyRedisConfig extends BaseModel
{
    public $config = array(
        'yzmCode'                                     => array('key' => "yzmCode", 'open' => true, 'time' => 600, 'select' => ''),
        'jsVersion'                                   => array('key' => "jsVersion", 'open' => true, 'time' => 9999999, 'select' => ''),
        'theme'                                       => array('key' => "theme", 'open' => true, 'time' => 9999999, 'select' => ''),
        'role'                                        => array('key' => "role", 'open' => true, 'time' => 604800, 'select' => ''),
        'account'                                     => array('key' => "account", 'open' => true, 'time' => 604800, 'select' => ''),
        'lightWinRecord'                              => array('key' => "lightWinRecord", 'open' => true, 'time' => 604800, 'select' => ''),//开服关服恢复雪豹机台设置

        'game:DataRoomInfoList'                       => array('key' => "game:DataRoomInfoList", 'open' => true, 'time' => 604800, 'select' => ''),
        'game:DataGameListInfo'                       => array('key' => "game:DataGameListInfo", 'open' => true, 'time' => 604800, 'select' => ''),
        'game:DataFunctionSwitch'                     => array('key' => "game:DataFunctionSwitch", 'open' => true, 'time' => 604800, 'select' => ''),
        'game:DataKeyValuePairs'                      => array('key' => "game:DataKeyValuePairs", 'open' => true, 'time' => 604800, 'select' => ''),
        'game:DataTypeCode'                           => array('key' => "game:DataTypeCode", 'open' => true, 'time' => 604800, 'select' => ''),//功能列表配置列表
        'game:DataErrorCode'                          => array('key' => "game:DataErrorCode", 'open' => true, 'time' => 604800, 'select' => ''),
        'game:DataDictionaryConfiguration'            => array('key' => "game:DataDictionaryConfiguration", 'open' => true, 'time' => 604800, 'select' => ''),
        'game:DataDictionaryConfigurationDetails'     => array('key' => "game:DataDictionaryConfigurationDetails", 'open' => true, 'time' => 604800, 'select' => ''),
        'game:DataGiftScoreSettings'                  => array('key' => "game:DataGiftScoreSettings", 'open' => true, 'time' => 604800, 'select' => ''),
        'game:DataDiffTypeCode'                       => array('key' => 'game:DataDiffTypeCode', 'open' => true, 'time' => 604800, 'select' => 0),//功能列表model
        'game:DataDiffDictionaryConfiguration'        => array('key' => 'game:DataDiffDictionaryConfiguration', 'open' => true, 'time' => 604800, 'select' => 0),//功能列表
        'game:DataDiffDictionaryConfigurationDetails' => array('key' => 'game:DataDiffDictionaryConfigurationDetails', 'open' => true, 'time' => 604800, 'select' => 0),//功能列表

        'game:FivepkNotice'          => array('key' => "game:FivepkNotice", 'open' => true, 'time' => 604800, 'select' => 'common'),
        'game:FivepkPrizeType'       => array('key' => "game:FivepkPrizeType", 'open' => true, 'time' => 604800, 'select' => 'common'), //奖项
        'game:FivepkPrizeParentType' => array('key' => "game:FivepkPrizeParentType", 'open' => true, 'time' => 604800, 'select' => 'common'),//奖项下拉列表
        'game:FivepkPrizeOut'        => array('key' => "game:FivepkPrizeOut", 'open' => true, 'time' => 604800, 'select' => 'common'),//出奖类型
        'game:FivepkPrizeItem'       => array('key' => "game:FivepkPrizeItem", 'open' => true, 'time' => 604800, 'select' => 'common'),//明星97奖项
        'game:UserOnlineInfo'        => array('key' => "game:UserOnlineInfo", 'open' => true, 'time' => 10, 'select' => 'common'),
        'game:TranslationConfig'     => array('key' => "game:TranslationConfig", 'open' => true, 'time' => 604800, 'select' => 'common'),//翻译模块
        'models:IpAddress'           => array('key' => "models:IpAddress", 'open' => true, 'time' => 604800, 'select' => 'common'),//IP缓存
        'ipAddressDb'                => array('key' => 'ipAddressDb', 'open' => true, 'time' => 604800, 'select' => 'common'),//ip地址查询
        'token'                      => array('key' => 'token', 'open' => true, 'time' => 86400, 'select' => 'forever'),//token 1天

        'FivepkNotice'        => array('key' => "FivepkNotice", 'open' => true, 'time' => 604800, 'select' => 'forever'),//定时发送系统公告
        //java对接的redis Key
        'storeExchangeRecord' => array('key' => 'storeExchangeRecord', 'open' => true, 'time' => 604800, 'select' => 'forever'),//定时执行兑换记录
        'activitySignData'    => array('key' => 'activitySignData', 'open' => true, 'time' => 604800, 'select' => 'forever'),//签到记录
        'CpayRecordRemind'    => array('key' => 'CpayRecordRemind', 'open' => true, 'time' => 604800, 'select' => 'forever'),//订单提醒
        'rankAwardAccount'    => array('key' => 'rankAwardAccount', 'open' => true, 'time' => 604800, 'select' => 'forever'),//排行榜记录

        'HitsReport'        => array('key' => 'HitsReport', 'open' => true, 'time' => 600, 'select' => 'common'),//人气报表
        'DiamondChangeTime' => array('key' => 'DiamondChangeTime', 'open' => true, 'time' => 5, 'select' => 'common'),//开洗分限制频率

        'locusHfh'      => array('key' => 'locusHfh', 'open' => true, 'time' => 604800, 'select' => 'forever'),//火凤凰轨迹
        'locusHfhMaxId' => array('key' => 'locusHfhMaxId', 'open' => true, 'time' => 604800, 'select' => 'forever'),//火凤凰轨迹最大id
        'compareHfh'    => array('key' => 'compareHfh', 'open' => true, 'time' => 604800, 'select' => 'forever'),//火凤凰比备

        'locusDzb'      => array('key' => 'locusDzb', 'open' => true, 'time' => 604800, 'select' => 'forever'),//大字版轨迹
        'locusDzbMaxId' => array('key' => 'locusDzbMaxId', 'open' => true, 'time' => 604800, 'select' => 'forever'),//火凤凰轨迹最大id
        'compareDzb'    => array('key' => 'compareDzb', 'open' => true, 'time' => 604800, 'select' => 'forever'),//大字板比备

        'locusDbs'      => array('key' => 'locusDbs', 'open' => true, 'time' => 604800, 'select' => 'forever'),//大白鲨轨迹
        'locusDbsMaxId' => array('key' => 'locusDbsMaxId', 'open' => true, 'time' => 604800, 'select' => 'forever'),//大白鲨轨迹最大id
        'compareDbs'    => array('key' => 'compareDbs', 'open' => true, 'time' => 604800, 'select' => 'forever'),//大白鲨比备

        'locusAtt'      => array('key' => 'locusAtt', 'open' => true, 'time' => 604800, 'select' => 'forever'),//att轨迹
        'locusAttMaxId' => array('key' => 'locusAttMaxId', 'open' => true, 'time' => 604800, 'select' => 'forever'),//att轨迹最大id
        'compareAtt'    => array('key' => 'compareAtt', 'open' => true, 'time' => 604800, 'select' => 'forever'),//att比备

        'locusMxj'      => array('key' => 'locusMxj', 'open' => true, 'time' => 604800, 'select' => 'forever'),//mxj轨迹
        'locusMxjMaxId' => array('key' => 'locusMxjMaxId', 'open' => true, 'time' => 604800, 'select' => 'forever'),//mxj轨迹最大id

        'locusSbb'      => array('key' => 'locusSbb', 'open' => true, 'time' => 604800, 'select' => 'forever'),//SBB轨迹
        'locusSbbMaxId' => array('key' => 'locusSbbMaxId', 'open' => true, 'time' => 604800, 'select' => 'forever'),//SBB轨迹最大id

        'locusPam'      => array('key' => 'locusPam', 'open' => true, 'time' => 604800, 'select' => 'forever'),//PAM轨迹
        'locusPamMaxId' => array('key' => 'locusPamMaxId', 'open' => true, 'time' => 604800, 'select' => 'forever'),//PAM轨迹最大id

        'locusBao'       => array('key' => 'locusBao', 'open' => true, 'time' => 604800, 'select' => 'forever'),//雪豹轨迹
        'locusBaoMaxId'  => array('key' => 'locusBaoMaxId', 'open' => true, 'time' => 604800, 'select' => 'forever'),//雪豹轨迹最大id
        'compareBao'     => array('key' => 'compareBao', 'open' => true, 'time' => 604800, 'select' => 'forever'),//雪豹比备
        'lightWinRecord' => array('key' => 'lightWinRecord', 'open' => true, 'time' => 604800, 'select' => 'forever'),//雪豹机台放奖记录

        'locusGhr'      => array('key' => 'locusGhr', 'open' => true, 'time' => 604800, 'select' => 'forever'),//赛马轨迹
        'locusGhrMaxId' => array('key' => 'locusGhrMaxId', 'open' => true, 'time' => 604800, 'select' => 'forever'),//赛马轨迹最大id
        'profitSum'     => array('key' => 'profitSum', 'open' => true, 'time' => 604800, 'select' => 'forever'),//赛马抽水总计
        'testProfitSum' => array('key' => 'testProfitSum', 'open' => true, 'time' => 604800, 'select' => 'forever'),//赛马关服抽水总计 用于测试用

        'locusByu'      => array('key' => 'locusByu', 'open' => true, 'time' => 604800, 'select' => 'forever'),//捕鱼轨迹
        'locusByuMaxId' => array('key' => 'locusByuMaxId', 'open' => true, 'time' => 604800, 'select' => 'forever'),//捕鱼轨迹最大id

        'userInfoArr' => array('key' => 'userInfoArr', 'open' => true, 'time' => 604800, 'select' => 'common'),//用户信息
        'findPrizeId' => array('key' => 'findPrizeId', 'open' => true, 'time' => 604800, 'select' => 'forever'),//奖型

        'upPayStatus' => array('key' => 'upPayStatus', 'open' => true, 'time' => 604800, 'select' => 'forever'),//支付成功

    );

    public function get($key)
    {
        foreach ($this->config as $val) {
            if (strpos($key, $val['key']) !== false) {
                if ($val['open']) {
                    if ($val['select'] == "common") {
                        Yii::$app->redis->select(Yii::$app->params['redisCommonDatabase']);
                    } else if ($val['select'] == "forever") {
                        Yii::$app->redis->select(Yii::$app->params['redisForeverDatabase']);
                    } else {
                        Yii::$app->redis->select(Yii::$app->redis->database);
                    }
                    return $val;
                }
            }
        }
        return "";
    }

    /**
     * 读取 hash redis key
     * @param $key string 缓存名
     * @return mixed
     */
    public function getRedisConfig(&$key)
    {
        if ($this->config[$key]['select'] == "common") {
            Yii::$app->redis->select(Yii::$app->params['redisCommonDatabase']);
        } else if ($this->config[$key]['select'] == "forever") {
            Yii::$app->redis->select(Yii::$app->params['redisForeverDatabase']);
        } else {
            Yii::$app->redis->select(Yii::$app->redis->database);
        }

        if (!$this->config[$key]['open']) {
            return '';
        }

        return $this->config[$key];
    }


    /**
     *  获取这个key的配置
     * @param $key
     */
    public function getKeyConfig($key)
    {
        try {
            foreach ($this->config as $val) {
                if (strpos($key, $val['key']) !== false) {
                    return $val;
                }
            }
            throw new MyException(ErrorCode::ERROR_REDIS_KEY);
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }

}



