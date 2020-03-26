<?php


namespace common\services;

use backend\models\Tool;
use JPush\Client;
use Yii;

require(__DIR__ . '/../../vendor/jpush/autoload.php');


class JgPush
{
    private $appKey = '2a939bdf71b2807eb482643c';
    private $masterSecret = '2dd8ea7ba2cfd22c86067652';
    static $client;
    private $logFile = '';

    public function __construct()
    {
        $this->setClient();
    }

    private function setClient()
    {
        if (empty(self::$client)) {
            $this->logFile = Yii::getAlias("@myLog") . DIRECTORY_SEPARATOR . 'JgPush';
            if (!file_exists($this->logFile)) {
                Tool::writeFile($this->logFile, '');
            }
            self::$client = new Client($this->appKey, $this->masterSecret, $this->logFile);
        }
    }

    /**
     * @param string $content
     * @param string $title
     * @param string|array $platform
     * @return mixed
     */
    public function push($content, $title, $platform = 'all')
    {
//        $pusher = self::$client->push();
//        $pusher->setPlatform($platform);//推送平台
//        $pusher->addAllAudience('all');//
//        $pusher->setNotificationAlert($notificationAlert);//简单推送
//        try {
//            $pusher->send();
//        } catch (\JPush\Exceptions\JPushException $e) {
//            // try something else here
//            print $e;
//        }

        //返回信息
        //        Array(
//            'body'    => Array(
//                'sendno' => 789318003,
//                'msg_id' => 67554014560832385
//            )
//            , 'http_code' => 200
//            , 'headers'   => Array(
//                '0'                    => 'HTTP/1.1 200 OK'
//                , 'Server'                 => 'nginx'
//                , 'Date'                   => 'Fri, 16 Aug 2019 07:39:36 GMT'
//                , 'Content-Type'           => 'application/json'
//                , 'Content-Length'         => 51
//                , 'Connection'             => 'keep-alive'
//                , 'X-Rate-Limit-Limit'     => 600
//                , 'X-Rate-Limit-Remaining' => 597
//                , 'X-Rate-Limit-Reset'     => 5
//                , 'X-Jpush-Timestamp'      => 1565941176975
//            )
//        );


        try {
            $response = self::$client->push()
                ->setPlatform($platform)//['ios', 'android']
                ->setNotificationAlert($content)// 全部设备信息
                ->addAllAudience('all')//全部设备
                ->iosNotification(['title' => $title, 'body' => $content])//ios设备
                ->androidNotification($content, ['title' => $title])//安卓设备
//                ->addWinPhoneNotification($notificationAlert, $title)
                ->send();
            return $response;
        } catch (\JPush\Exceptions\JPushException $e) {
            // try something else here
            print $e;
        }
    }


    /**
     * @param array $msgIds
     * @return mixed
     */
    public function getReceived(array $msgIds)
    {
        $report = self::$client->report();
        return $report->getReceived($msgIds);
    }


}