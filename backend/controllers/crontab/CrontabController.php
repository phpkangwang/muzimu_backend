<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/21
 * Time: 15:07
 */

namespace backend\controllers\crontab;

use Yii;
use yii\web\Controller;
use backend\models\Tool;


/**
 *  所有的定时任务都必须经过这个  Controller
 * Class CrontabController
 * @package backend\controllers\crontab\game
 */
class CrontabController extends Controller
{
    private $microStime;
    private $fileLog = "crontab.log";

    public function __construct($id, $module, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->microStime = microtime();
    }

    public function __destruct()
    {
        if (empty($this->microStime)) {
            return;
        }
        $tool    = new Tool();
        $useTime = $tool->microtimeToStr($this->microStime, microtime());
        $content = "\r\n\t执行" . Yii::$app->requestedRoute .
            "\r\n\t完整url是:" . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'] .
            "\r\n\tget参数是:" . json_encode($_GET) .
            "\r\n\tpost参数是:" . json_encode($_POST) .
            "\r\n\t耗时" . $useTime . "\r\n";
        $tool->myLog($content, $this->fileLog);
    }


}