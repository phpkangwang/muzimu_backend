<?php

namespace backend\models\remoteInterface;

use backend\models\BaseModel;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use common\models\DataServerList;
use Yii;

//调用远程接口
class remoteInterface extends BaseModel
{

    private $fileName = "remoteInterface.log";

    /**
     *   服务器状态改变
     */
    public function serverStatusUpdate($data,$gameType = 0)
    {
        $content = "serverStatusUpdate 参数是 :" . json_encode($data);
        $this->Tool->myLog($content, $this->fileName);
        $DataServerListModel = new DataServerList();
        $UrlPort = $DataServerListModel->findByGameType($gameType);
        $result = Tool::encryptByPublicKey($data);
        $url = $UrlPort . "/serverStatusUpdate?data={$result['data']}&sign={$result['sign']}";
        $this->doUrl($url);
    }
    
    /**
     *   刷新机台
     */
    public function refreshMachine($gameType = 0)
    {
        //现在要根据不同的游戏刷新不同的服务器缓存
        if( $gameType == 0){
            $url = Yii::$app->params['url'] . "/refreshMachine";
        }else{
            $DataServerListModel = new DataServerList();
            $UrlPort = $DataServerListModel->findByGameType($gameType);
            $url = $UrlPort . "/refreshMachine";
        }
        $content = "refreshMachine 参数是 :" . $url;
        $this->Tool->myLog($content, $this->fileName);
        $this->doUrl($url);
    }

    /**
     * 游戏缓存刷新
     */
    public function refreshGameCache($gameType = 0)
    {
        //现在要根据不同的游戏刷新不同的服务器缓存
        if( $gameType == 0){
            $url = Yii::$app->params['url'] . "/refreshGameCache";
        }else{
            $DataServerListModel = new DataServerList();
            $UrlPort = $DataServerListModel->findByGameType($gameType);
            $url = $UrlPort . "/refreshGameCache";
        }
        $content = "refreshMachine 参数是 :" . $url;
        $this->Tool->myLog($content, $this->fileName);
        $this->doUrl($url);
    }

    /**
     *   清除小洛
     */
    public function clearRobotTask()
    {
        $url = Yii::$app->params['url'] . "/clearRobotTask";
        $this->doUrl($url);
        $this->refreshMachine();
        $this->refreshGameCache();
    }



    /**
     * 捕鱼用户机率修改的时候调用接口刷新java缓存
     * accountIds 用户id用逗号隔开
     *  type  1修改单个或者多个用户  2修改所有的用户
     */
    public function refreshPlayerOddsByu($data)
    {
        $content = "refreshPlayerOddsByu 参数是 :" . json_encode($data);
        $this->Tool->myLog($content, $this->fileName);
        $url = Yii::$app->params['url'] . "/refreshPlayerOddsByu?data=".json_encode($data);
        $this->doUrl($url);
    }

    /**
     * 游戏房间奖刷新
     */
    public function refreshRoomAward($data)
    {
        $content = "refreshRoomAward 参数是 :" . json_encode($data);
        $this->Tool->myLog($content, $this->fileName);
        $result = Tool::encryptByPublicKey($data);
        $url    = Yii::$app->params['url'] . "/refreshRoomAward?data={$result['data']}&sign={$result['sign']}";
        $this->doUrl($url);
    }

    /**
     * 发送系统通告
     * @param $notice\
     */
    public function sendNotice($notice)
    {
        $content = "sendNotice 参数是 :" . json_encode($notice, JSON_UNESCAPED_UNICODE);
        $this->Tool->myLog($content, $this->fileName);
        $notice = urlencode($notice);
        $url    = Yii::$app->params['url'] . "/notice?notice={$notice}&type=2";
        $this->doUrl($url);
    }

    /**
     * 创建机台
     * @param $data
     */
    public function createMachine($data)
    {
        $content = "createMachine 参数是 :" . json_encode($data);
        $this->Tool->myLog($content, $this->fileName);
        $result = Tool::encryptByPublicKey($data);
        $url    = Yii::$app->params['url'] . "/createMachine?data={$result['data']}&sign={$result['sign']}";
        $this->doUrl($url);
    }

    /**
     * 删除机台
     * @param $data
     */
    public function deleteMachine($gameType, $machineId)
    {
        $url = Yii::$app->params['url'] . "/deleteMachine?gameType={$gameType}&seoMachineId={$machineId}";
        $this->doUrl($url);
    }

    /**
     *   玩家开洗分
     * @param $data
     */
    public function fraction($data)
    {
        $content = "fraction 参数是 :" . json_encode($data);
        $this->Tool->myLog($content, $this->fileName);
        $result = Tool::encryptByPublicKey($data);
        $url    = Yii::$app->params['url'] . "/fraction?data={$result['data']}&sign={$result['sign']}";
        $this->doUrl($url);
    }

    /**
     *  玩家添加分数
     * @param $data
     * @return bool|void
     */
    public function addScore($data)
    {
        $data = urlencode($data);
        $url  = Yii::$app->params['url'] . "/addScore?data={$data}";
        $this->doUrl($url);
    }

    /**
     *  玩家下线
     * @param $data
     */
    public function allowed($data, $gameType = 0)
    {
        $DataServerListModel = new DataServerList();
        $UrlPort = $DataServerListModel->findByGameType($gameType);
        $data = urlencode(json_encode($data));
        $url = $UrlPort . "/allowed?data={$data}";
        $this->doUrl($url);
    }

    /**
     *   所有玩家离线
     */
    public function offOnline($gameType = 0)
    {
        $DataServerListModel = new DataServerList();
        $UrlPort = $DataServerListModel->findByGameType($gameType);
        $url = $UrlPort . "/offlineAllPlayer";
        $this->doUrl($url);
    }

    /**
     *   获取所有在线用户当前正在的状态 playSpace字段表示状态 :[1 => '大厅',
     * 2 => '列表',
     * 3 => '游戏中',
     * 4 => '比倍',
     * 5 => '连庄×',
     * 6 => '不活跃',]
     * @return array
     */
    public function getOnlinePlayer()
    {
        $url = Yii::$app->params['url'] . "/getOnlineList";
        return $this->doUrl($url);
    }

    public function setReservationTime($time)
    {
        $data    = json_encode([
            'machineList' => [],
            'changeTime'  => $time,
            'isAll'       => 1,//0-非全部，1-全部
        ]);
        $content = "setReservationTime 参数是 :" . $data;
        $this->Tool->myLog($content, $this->fileName);
        $url = \Yii::$app->params['url'] . "/changeMachineReservationDate?data=" . $data;
        return $this->doUrl($url);
    }

    /**
     *  AI机器人
     * @param $data
     */
    public function AI($data)
    {
        $content = "AI 参数是 :" . json_encode($data);
        $this->Tool->myLog($content, $this->fileName);
        $result = Tool::encryptByPublicKey($data);
        $url    = Yii::$app->params['url'] . "/littleRo?data={$result['data']}&sign={$result['sign']}";
        $this->doUrl($url);
    }

    /**
     * 发送邮件
     * @param $data
     */
    public function sendMail($data)
    {
        $url = Yii::$app->params['url'] . "/saveEmailRecord?" . $data;
        $this->doUrl($url);
    }

    /**
     * 修改绑定银行卡
     * @param $data
     */
    public function cpayChangeBank($data)
    {
        $content = "cpayChangeBank 参数是 :" . json_encode($data);
        $this->Tool->myLog($content, $this->fileName);
        $result = Tool::encryptByPublicKey($data);
        $url    = Yii::$app->params['url'] . "/cpayChangeBank?" . "data={$result['data']}&sign={$result['sign']}";
        $this->doUrl($url);

    }

    /**
     * 道具修改
     * @param $data
     * @return mixed
     */
    public function UpdatePack($data)
    {
        $content = "UpdatePack 参数是 :" . json_encode($data);
        $this->Tool->myLog($content, $this->fileName);
        $head = Yii::$app->params['url'] . "/modifySotreStoragePackItems?";
        $url  = Tool::urlSetValue($head, $data);
        return $this->doUrl($url);
    }


    /**
     * 修改转出状态
     * @param $data
     * @return mixed
     */
    public function cpayStatus($data)
    {
        $content = "cpayStatus 参数是 :" . json_encode($data);
        $this->Tool->myLog($content, $this->fileName);
        $head   = Yii::$app->params['url'] . "/cpayStatus?";
        $result = Tool::encryptByPublicKey($data);
        $url    = $head . "data={$result['data']}&sign={$result['sign']}";
        return $this->doUrl($url);
    }

    /**
     * 执行url
     * @param $url
     */
    public function doUrl($url)
    {
        try {
            $rs      = Tool::getCurl($url);
            $content = "url: {$url}, \r\n返回的json :" . json_encode($rs);
            $this->Tool->myLog($content, $this->fileName);

            if (!isset($rs['status']) || $rs['status'] != 10) {
                $errorMessage = $this->DataErrorCode->getMessage($rs['message']);
                throw new MyException(ErrorCode::ERROR_CURL_STATUS);
            }
            return $rs;
        } catch (MyException $e) {
            echo $e->toJson($errorMessage);
        }
    }


    /**
     * 执行url
     * @param $url
     */
    public function doPostUrl($url, $data)
    {
        try {
            $rs       = Tool::postCurl($url, $data);
            $content  = "url: {$url}, \r\n返回的json :" . json_encode($rs);
            $fileName = "remoteInterface.log";
            $this->Tool->myLog($content, $fileName);
            if ($rs['status'] != 10) {
                $errorMessage = $this->DataErrorCode->getMessage($rs['message']);
                throw new MyException(ErrorCode::ERROR_CURL_STATUS);
            }
            return $rs;
        } catch (MyException $e) {
            echo $e->toJson($errorMessage);
        }
    }

}
