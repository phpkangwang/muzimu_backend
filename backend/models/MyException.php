<?php
namespace backend\models;

use Yii;
/**
 *  自定义的异常处理
 */
class MyException extends \Exception
{
    private $myCode = 0;
    private $myMessage = "";
    public function __construct($message)
    {
        parent::__construct($message);
    }

    public function toJson($code)
    {
        $obj = new ErrorCode();
        if( is_numeric($code) )
        {
            $this->myCode    = $code;
            $this->myMessage = $obj->getMessage($code);
        }else{
            $this->myCode    = ErrorCode::ERROR_SYSTEM;
            $this->myMessage = $code;
        }
        if(isset( $_REQUEST['jsonpcallback'] )){
            $jsoncallback = htmlspecialchars($_REQUEST['jsonpcallback']);
            echo $jsoncallback . "(".json_encode(['code'=>$this->myCode, 'message'=>$this->myMessage]).")";
        }else{
            header('Content-type:application/json; charset=utf-8');
            echo json_encode(['code'=>$this->myCode, 'message'=>$this->myMessage]);
        }

        $content =
            "\r\n执行".Yii::$app->requestedRoute.
            "\r\nget参数是:".json_encode($_REQUEST).
            "\r\n报错文件是:".$this->file.
            "\r\n报错行数是:".$this->line.
            "\r\n返回码是:".$this->myCode.
            "\r\n返回信息是:".$this->myMessage."\r\n";
        $tool = new Tool();
        $tool->myLog($content, "requestErrorLog".date('Ymd', time()).".log");
        exit;
    }
}
