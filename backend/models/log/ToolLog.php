<?php
namespace backend\models\log;

use backend\models\BaseModel;
use backend\models\Tool;
use Yii;

class ToolLog
{
    private $Tool;
    public $logTitle;
    private $stime;
    private $etime;
    private $microStime;
    private $microEtime;

    public function __construct($logTitle)
    {
        $this->Tool = new Tool();
        $this->logTitle = $logTitle;
    }

    public function begin()
    {
        $this->stime = time();
        $this->microStime = microtime();
        if( !empty($this->logTitle) ){
            $this->Tool->myLog(" {$this->logTitle} 开始执行了");
        }
    }

    public function end()
    {
        $this->etime = time();
        $this->microEtime = microtime();
        if( !empty($this->logTitle) ){
            $this->Tool->myLog(" {$this->logTitle} 执行结束");
        }
    }

    public function useTime()
    {
        $time = $this->etime - $this->stime;
        if( !empty($this->logTitle) ){
            $this->Tool->myLog(" {$this->logTitle} 执行耗时".$time."秒");
        }
        return $time;
    }

    public function useMicroTime()
    {
        $time = $this->Tool->microtimeToStr( $this->microStime, $this->microEtime );
        if( !empty($this->logTitle) ){
            $this->Tool->myLog(" {$this->logTitle} 执行耗时".$time);
        }
        return $time;
    }
}
