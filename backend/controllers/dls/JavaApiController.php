<?php

namespace backend\controllers\dls;

use backend\controllers\MyController;
use backend\models\Account;
use backend\models\log\AppErrorLog;
use backend\models\Tool;
use common\models\game\FivepkAccount;

class JavaApiController extends MyController
{

    public function checkToken()
    {
        return true;
    }

    /**
     *   获取总代理id
     */
    public function actionGetZdlId()
    {
        Tool::checkParam(['id'], $this->get);
        //玩家id
        $accountId    = $this->get['id'];
        $AccountModel = new Account();
        $zdlId        = $AccountModel->getZdlByAccountId($accountId);
        $this->setData($zdlId);
        $this->sendJson();
    }

    /**
     *  初始化所有用户的总代理id
     */
    public function actionInitAccountZdlId()
    {
        //获取所有的用户
        $FivepkAccountModel = new FivepkAccount();
        $FivepkAccountObjs  = $FivepkAccountModel->getAllSeoid();
        $arr = array();
        foreach ($FivepkAccountObjs as $val)
        {
            $arr[$val['seoid']] ['account_id'] = $val['account_id'];
            $arr[$val['seoid']] ['seoid']      = $val['seoid'];
        }

        $AccountModel = new Account();
        foreach ($arr as $key => $val)
        {
            $arr[$key]['zdlId'] = $AccountModel->getZdlByAccountId($val['account_id']);
            $sql = "update fivepk_account set backend_zdl_account_id = '{$arr[$key]['zdlId']}' where seoid = '{$val['seoid']}'";
            \Yii::$app->game_db->createCommand($sql)->query();
        }
        echo "success";
        return;
    }

    /**
     *  添加app错误日志
     */
    public function actionAddAppErrorLog()
    {
        $tool = new Tool();
        $tool::checkParam(['accountId', 'udid', 'date', 'ERROR_MSG'], $this->post);

        $content   = $this->post['ERROR_MSG'];
        $accountId = $this->post['accountId'];
        $udid      = $this->post['udid'];
        $date      = $this->post['date'];

        $content          = str_replace("\\t", "", $content);
        $content          = str_replace("\\", "", $content);
        $content          = explode(",", $content);
        $content          = implode("</br>", $content);
        $AppErrorLogModel = new AppErrorLog();
        $postData         = array(
            'content'     => $content,
            'account_id'  => $accountId,
            'udid'        => $udid,
            'app_time'    => $date,
            'create_time' => time()
        );
        $AppErrorLogModel->add($postData);
        $this->sendJson();
    }
}

?>