<?php
namespace backend\controllers\platform\thwj\backend\models;

/**
 * 这是一个工具类
 * Class Tool
 */
class Tool extends \backend\models\Tool{

    /**
     * 引入特质类 主要用到__call
     */
    use \backend\controllers\platform\PlatformTrait;

//    public function hideName($name){
//        return  "***".mb_substr($name,-5,5,'utf-8');
//    }
}