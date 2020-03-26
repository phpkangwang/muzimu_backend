<?php
namespace backend\controllers\platform\thwj\backend\controllers;

use backend\controllers\platform\hfh\backend\models\Tool;
use backend\models\BaseModel;
use backend\models\Factory;
use Yii;
use backend\models\Account;
use backend\models\ErrorCode;
use backend\models\MyException;

class GameClearController extends BaseModel
{
    public $time;
    public $db;
    /**
     * 引入特质类 主要用到__call
     */
    use \backend\controllers\platform\PlatformTrait;

    

}

?>