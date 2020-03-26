<?php
namespace backend\models;

use Yii;
class Factory
{
    public static function __callStatic($name, $arguments)
    {
        $tool = new Tool();
        $nowPlatForm = Yii::$app->params['platForm'];
        //获取这个文件的路径
        $dir = Yii::getAlias("@platFormList").DIRECTORY_SEPARATOR.$nowPlatForm;
        //文件命名空间路径
        $defaultClassDir = "backend".DIRECTORY_SEPARATOR."controllers".DIRECTORY_SEPARATOR."platform".DIRECTORY_SEPARATOR.$nowPlatForm;
        //遍历这个文件夹下面的所有文件   然后找到这个文件
        $fileName = $name.".php";
        $modelsDir = $tool->findDir($dir, $fileName, $defaultClassDir);
        $modelUrl = $modelsDir.DIRECTORY_SEPARATOR.$name;
        $modelUrl = str_replace("/","\\",$modelUrl);
        return Yii::createObject($modelUrl);
    }

}
