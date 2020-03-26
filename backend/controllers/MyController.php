<?php
namespace backend\controllers;

use backend\models\Account;
use Yii;
use yii\web\Controller;
use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\AccountLoginBind;
use backend\models\Tool;

/**
 *  这个所有Controller的父类Controller
 */
class MyController extends Controller
{
    //header('Content-Type:text/html;charset=utf-8');

    public $get  = array();
    public $post = array();
    public $config = array();
    public $models = null;
    /**
     * 免除角色权益限制访问的功能
     * @var array
     */
    private $NoAccessLimit = [
        'account/login',
        'account/login-out',
        'account/test',
        'fivepk/machine/backend-version-view',
        'dls/site/update-token',
        'dls/site/update-error-login',
        'fivepk/award/get-prize-item',
        'fivepk/award/get-prize-parent-list',
        'fivepk/award/get-prize-out-list',
    ];

    /**
     * 当前时间戳
     * @var int
     */
    public $time = 0;
    /**
     * 后台登录的用户id
     * @var int
     */
    public $loginId = 0;
    public $loginInfo = array();
    /**
     * 返回码
     * @var int
     */
    private $code = 200;

    /**
     * 错误信息
     * @var string
     */
    private $message = "";

    /**
     * 返回数据
     * @var array
     */
    private $data = array();

    /**
     * 分页所需要的数据
     * @var array
     */
    private $page = array();

    private $microStime = "";

    public function __construct($id, $module, array $config = [])
    {
        $this->microStime = microtime();
        //$this->Tool->myLog("接口".Yii::$app->requestedRoute."开始请求:".microtime());
        $this->time = time();
        $this->config = Yii::$app->params;
        $this->get  = $this->checkGet();
        $this->post = $this->checkPost();
        $this->getLoginId();
        $this->loginInfo = $this->Account->findBase($this->loginId);
        $this->checkAccess();
            $GLOBALS['user'] = $this->loginInfo;
        parent::__construct($id, $module, $config);
    }

    public function checkGet(){
        $data = Yii::$app->request->get();
        foreach ($data as $key=>$val ){
            if( !is_array($val)){
                $val = trim($val);
                $val = addslashes($val);
                $data[$key] = $val;
            }
        }
        return $data;
    }

    public function checkPost(){
        $data = Yii::$app->request->post();
        foreach ($data as $key=>$val ){
            if( !is_array($val)) {
                $val = trim($val);
                $val = addslashes($val);
                $data[$key] = $val;
            }
        }
        return $data;
    }

    public function __set($name, $value)
    {
        return parent::__set($name, $value); // TODO: Change the autogenerated stub
    }

    public function __get($className)
    {
        //模仿单例模式避免多次new
        if( isset( $this->models[$className] ) )
        {
            return $this->models[$className];
        }

        //class路径缓存到redis，避免重复循环目录
        $classDir = "";
        if( empty($classDir) ){
            $defaultClassDirs = ["backend".DIRECTORY_SEPARATOR."models","common".DIRECTORY_SEPARATOR."models"];
            $dirs = [Yii::getAlias("@backend_models"),Yii::getAlias("@common_models")];
            foreach ($defaultClassDirs as $key=>$defaultClassDir){
                $dir = $dirs[$key];
                $fileName = $className.".php";
                $modelsDir = $this->findDir($dir, $fileName, $defaultClassDir);
                if($modelsDir != "")
                {
                    $classDir = $modelsDir;
                    break;
                }
            }
        }

        if( $classDir != "")
        {
            $config = ['class' => $classDir.DIRECTORY_SEPARATOR.$className];
            $config = str_replace("/","\\",$config);
            $obj = Yii::createObject($config);
            $this->models[$className] = $obj;
            return $obj;
        }else{
            return parent::__get($className);
        }
    }

    //递归目录查找文件
    public function findDir($dir, $fileName, $ClassDir)
    {
        $modelsDirs = array();
        $dirArrs = scandir($dir);
        if( in_array( $fileName, $dirArrs) )
        {
            return $ClassDir;
        }else{
            //获取所有的子目录
            foreach ( $dirArrs as $dirArr )
            {
                if( strpos($dirArr, ".") === false )
                {
                    array_push($modelsDirs, DIRECTORY_SEPARATOR.$dirArr);
                }
            }
            foreach ($modelsDirs as $dirArr)
            {
                $rs = $this->findDir($dir.$dirArr, $fileName, $ClassDir.$dirArr);
                if( $rs != ""){
                    return $rs;
                }
            }
            return "";
        }
    }

    /**
     *   给用户id赋值
     */
    public function getLoginId()
    {
        if( isset( $this->get['token'] ) )
        {
            $arr = json_decode( base64_decode(  $this->get['token'] ) ,true);
            $this->loginId = isset( $arr['id'] ) ? $arr['id'] : 0;
        }else{
            $this->loginId = 0;
        }
    }

    //判断当前账户是否有权限访问这个url
    public function checkAccess()
    {
        try{
            $route = Yii::$app->requestedRoute;
            if( in_array($route, $this->NoAccessLimit))
            {
                return true;
            }else{
                $this->checkToken();
                $logObj        = $this->Log;
                $FunListObj    = $this->FunList;
                $FunAccountObj = $this->FunAccount;
                $gameName      = isset($_REQUEST['gameName']) ? $_REQUEST['gameName'] : "";
                $FunListObj    = $FunListObj->findByUrl($route, $gameName);
                if( empty($FunListObj) || $FunListObj['type'] == "查" )
                {
                    return true;
                }else{
                    //证明这个请求是需要验证权限的
                    $FunAccountObj = $FunAccountObj->findByAcMe($this->loginId, $FunListObj['id']);
                    //添加访问日志
                    $logData = array(
                        'function_list_id' => $FunListObj['id'],
                        'admin_id'   => $this->loginId,
                        'status'     => 1,
                        'created_at' => $this->time,
                    );
                    if( empty($FunAccountObj) ){
                        $logData['status'] = 2;
                        if( $FunListObj['type'] == "增" || $FunListObj['type'] == "删" || $FunListObj['type'] == "改" ) {
                            $logObj->add($logData);
                        }
                        throw new MyException( ErrorCode::ERROR_ACCOUNT_FUN_NOT_ACCESS );
                    }else{
                        if( $FunListObj['type'] == "增" || $FunListObj['type'] == "删" || $FunListObj['type'] == "改" ) {
                            $logObj->add($logData);
                        }
                        return true;
                    }
                }
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
        return false;
    }

    /**
     * 检查token是否正确
     * @return bool
     */
    public function checkToken()
    {
        try{
            $token = isset( $this->get['token'] ) ? $this->get['token'] : "";
            $accountObj = $this->loginInfo;
            if( empty($accountObj) || $accountObj['token'] != $token)
            {
                if( empty($token) || $token == "undefined" ){
                    throw new MyException( ErrorCode::ERROR_RELOGIN );
                }else{
                    throw new MyException( ErrorCode::ERROR_ACCOUNT_RE_LOGIN );
                }

            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
        return true;
    }

    public function sendJson()
    {
        header('Content-type:application/json; charset=utf-8');
        $json_data = json_encode(
            array(
                "code"    => $this->code,
                "message" => $this->message,
                "data"    => $this->data,
                "page"    => $this->page
            )
        );
        if( isset($_REQUEST['jsonpcallback']) )
        {
            $jsoncallback = htmlspecialchars($_REQUEST['jsonpcallback']);
            $json_data = $jsoncallback . "(".$json_data.")";
        }
        //为了保证正式环境正常运行，所有的操作都记录操作日志
        $useTime   = $this->Tool->microtimeToStr($this->microStime, microtime());
        $loginName = isset( $this->loginInfo['name'] ) ?  $this->loginInfo['name'] : "未登录";
        $account   = isset( $this->loginInfo['account'] ) ?  $this->loginInfo['account'] : "未登录";
        $loginId   = isset( $this->loginInfo['id'] ) ?  $this->loginInfo['id'] : "未登录";
        $content = "    ".$loginName.":账号:".$account.":ID:".$loginId.
            "\r\n执行".Yii::$app->requestedRoute.
            "\r\n完整url是:".$_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'].
            "\r\nget参数是:".json_encode($this->get).
            "\r\npost参数是:".json_encode($this->post).
            "\r\n返回码是:".$this->code.
            ":\r\n耗时".$useTime."\r\n";
        $this->Tool->myLog($content, "requestLog".date('Ymd', time()).".log");
        echo $json_data;
        exit;
    }

    /**
     * 设置返回信息
     * @param $message
     * @return bool
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return true;
    }

    /**
     * 设置返回数据
     * @param $data
     * @return bool
     */
    public function setData($data)
    {
        $this->data = $data;
        return true;
    }

    /**
     * 设置分页
     * @param $page
     * @return bool
     */
    public function setPage($page)
    {
        $this->page = $page;
        return true;
    }

    /**
     * 跳转
     * @param $funcName
     * @param $param
     * @return mixed
     */
    public static function platform($funcName, $param)
    {
        $className = '\backend\controllers\platform\\' . strtolower(\Yii::$app->params['platForm']) . '\\' . get_called_class();

        if(!class_exists($className)){
            return '';
        }

        $class     = new $className();
        return call_user_func_array(array($class, $funcName), $param);
    }


}