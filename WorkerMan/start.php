<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

use PHPSocketIO\SocketIO;
use Workerman\Worker;
use Workerman\Lib\Timer;

class WorkerManTimer
{
    public $config;
    public $timerArr = [];
    public $socketIO;
    public $headUrl;
    public $platForm;

    public function init()
    {
        $this->timerArr = [];
        $this->headUrl  = $this->config['params']['wokermanUrl'];
        $this->platForm = $this->config['params']['platForm'];
        echo "this platForm is:" . $this->platForm . "\r\n";
        $this->socketIO = new SocketIO($this->config['params']['socket']);
    }

    //运行
    public function runAll()
    {
        $this->getConfig();
        $this->init();
        $this->socketIO->on('workerStart', function () {
            $this->selectPlatFormData();
            $this->addTimer();
            //$this->test();
        });
        Worker::runAll();
    }

    //根据平台选择定时任务
    public function selectPlatFormData()
    {
        $timerArr       = yii\helpers\ArrayHelper::merge(
            require("config/common.php"),
            require("config/{$this->platForm}.php")
        );
        $this->timerArr = $timerArr;
    }

    //添加SocketIO客户端Notice 的提示成功
    public function test()
    {
        Timer::add(5, function () {
            $time = time();
            echo $time . "\r\n";
            $this->socketIO->emit('notice', $time);
        });

    }


    //添加定时任务
    public function addTimer()
    {
        foreach ($this->timerArr as &$timer) {
            $url  = $timer['url'];
            $time = $timer['time'];
            $type = $timer['type'];

            Timer::add(1, function ($url) use ($time, $type) {
                $hour   = date('H');
                $minute = date('i');
                $second = date('s');
                //echo $hour.":".$minute.":".$second."\r\n";
                $rs = "";
                switch ($type) {
                    case "second" :
                        if (($second % $time['second']) == 0) {
                            $rs = $this->curl($url);
                        }
                        break;
                    case "minute" :
                        if (($minute % $time['minute']) == 0 && $time['second'] == $second) {
                            $rs = $this->curl($url);
                        }
                        break;
                    case  "day" :
                        if ($hour == $time['hour'] && $minute == $time['minute'] && $second == $time['second']) {
                            $rs = $this->curl($url);
                        }
                        break;
                    default :
                }
                $this->sendEmit($rs);
            }, array($url));
        }
    }

    //对于返回值的处理
    public function sendEmit($content)
    {
        $rs           = json_decode($content, true);
        $accountIdArr = $rs['accountId'];
        if (!empty($accountIdArr)) {
            $content = implode(",", $accountIdArr);
            echo "推送notice的用户是:" . $content;
            $this->socketIO->emit('notice', $content);
        }
    }

    //获取配置
    public function getConfig()
    {
        //构造一个wokerman专用的配置文件,因为wokerman只用到redis，所以从backend/config/中抽取需要的进行配置
        $this->config = yii\helpers\ArrayHelper::merge(
            require __DIR__ . '/../backend/config/WokermanMain.php',
            require __DIR__ . '/../backend/config/main-local.php'
        );

        try {
            $app = new yii\console\Application($this->config);
            $app->init();
        } catch (\yii\base\InvalidConfigException $e) {
            Worker::log("yii2的Application对象无法启动（配置参数错误）");
        }
        return $this->config;
    }

    function curl($url)
    {
        $startMemory = memory_get_usage();
        $startTime   = time();
        $url         = $this->headUrl . $url;
        $fileName    = "crontab.log";
        $ch          = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);


        echo "执行url:" . $url . "\r\n";

        $endMemory = memory_get_usage();
        $endTime   = time();

        $log = "\r\n\t定时任务执行url:" . $url .
                "\r\n\t开始内存". $startMemory.
                "\r\n\t结束内存". $endMemory.
                "\r\n\t使用内存". ($endMemory - $startMemory).
                "\r\n\t开始时间". $startTime.
                "\r\n\t结束时间". $endTime.
                "\r\n\t使用时间". ($endTime - $startTime)." 秒\r\n"
            ;

        $this->myLog($log, $fileName);

        return $content;
    }

    function myLog($content, $fileName = "")
    {
        $filePath = dirname(__DIR__) . '/backend/runtime/logs';
        if (empty($fileName)) {
            $url = $filePath . '/testlog' . date('Ymd', time()) . ".log";
        } else {
            $url = $filePath . '/' . $fileName;
        }
        $content = "执行时间:" . date('Y-m-d H:i:s', time()) . $content;
        $myfile = fopen($url, "a+") or die("Unable to open file!");
        fwrite($myfile, $content . "\r\n");
        fclose($myfile);
        return true;
    }

}

if (!defined('GLOBAL_START')) {
    $WorkerManTimer = new WorkerManTimer();
    $WorkerManTimer->runAll();
}

