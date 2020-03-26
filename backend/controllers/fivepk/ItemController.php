<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-5
 * Time: 18:27
 */

namespace backend\controllers\fivepk;

use Yii;
use backend\controllers\MyController;
use backend\models\ErrorCode;
use backend\models\MyException;

class ItemController extends MyController
{

    /**
     *   兑换奖品列表
     */
    public function actionGetItemExchangeList()
    {
        $data = $this->StoreItemExchangeList->tableList();
        $this->setData($data);
        $this->sendJson();
    }

}