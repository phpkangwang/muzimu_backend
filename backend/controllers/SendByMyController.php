<?php


namespace backend\controllers;

use backend\controllers\MyController;

class SendByMyController extends MyController
{
    //验证都可以通过
    public function checkAccess()
    {
        return true;
    }

}