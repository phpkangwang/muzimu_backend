<?php

namespace backend\controllers\dls;

use backend\controllers\MyController;
use backend\controllers\SendByMyController;
use backend\models\Account;
use backend\models\ErrorCode;
use backend\models\log\AppErrorLog;
use backend\models\MyException;
use backend\models\Tool;
use common\models\FastLaneDevices;
use common\models\game\FivepkAccount;

class IosDevicesController extends SendByMyController
{

    public function actionUpdateDevices()
    {
        $sql = "UPDATE fastlane_devices
INNER JOIN 
(SELECT app_id FROM fastlane_devices WHERE type=0 and status=1 ORDER BY update_time desc LIMIT 1) t
on t.app_id=fastlane_devices.app_id
SET status=1,update_time={$this->time}
WHERE status=0 AND type=1
";
        FastLaneDevices::query($sql);
        $this->sendJson();
    }


    public function actionDevicesAdd()
    {
        $FastLaneDevices = new FastLaneDevices();
        if (!Tool::isIssetEmpty($this->post['id'])) {
            $id  = $this->post['id'];
            $obj = $FastLaneDevices::findOne($id);
        } else {
            $obj = $FastLaneDevices;
        }

        unset($this->post['id']);

        $data = $obj->add($this->post);
        $this->setData($data);
        $this->sendJson();

    }

    public function actionAppIdOpen()
    {
        $FastLaneDevices = new FastLaneDevices;
        //查询开启的app_id
        $appIdData = $FastLaneDevices::queryAll('
SELECT app_id,max_num,iss,kid,bid,certificates FROM fastlane_devices WHERE type=0 and status=1 ORDER BY update_time desc LIMIT 1
');
        $this->setData($appIdData);
        $this->sendJson();
    }

    public function actionAppIdCount()
    {
        $appId           = $this->get['appId'];
        $FastLaneDevices = new FastLaneDevices;
        //查询开启的app_id
        $appIdData = $FastLaneDevices::queryAll("SELECT count(*) as num FROM fastlane_devices where app_id='{$appId}'");
        $this->setData($appIdData);
        $this->sendJson();
    }


    public function actionAppIdOne()
    {
        $udid            = $this->get['udid'];
        $FastLaneDevices = new FastLaneDevices;
        //查询开启的app_id
        $appIdData = $FastLaneDevices::find()->where(['udid' => $udid])->asArray()->one();
        $this->setData($appIdData);
        $this->sendJson();
    }

    public function actionAppId()
    {
        $appId     = $this->get['appId'];
        $appIdData = FastLaneDevices::queryAll("
SELECT app_id,max_num,itms FROM fastlane_devices WHERE type=0 and app_id='{$appId}' ORDER BY update_time desc LIMIT 1
");
        $this->setData($appIdData);
        $this->sendJson();
    }

    public function actionDataForId()
    {
        $id        = intval($this->get['id']);
        $appIdData = FastLaneDevices::queryAll("
SELECT * FROM fastlane_devices WHERE type=0 and id='{$id}' ORDER BY update_time desc LIMIT 1
");
        $this->setData($appIdData);
        $this->sendJson();
    }


}

?>