<?php


namespace common\models\pay\platform;


use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;

class PayManagement extends PayAbstract
{


    /**
     * 异步返回结果
     * @param $data
     * @return bool
     * @throws \yii\db\Exception
     */
    public function callBackUrl($data)
    {


    }



    /**
     * 异步验参
     * @param $data
     * @return string
     */
    public function getSignOfCallBackUrl($data)
    {

    }


}