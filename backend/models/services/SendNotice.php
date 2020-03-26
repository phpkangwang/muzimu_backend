<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-7-9
 * Time: 19:48
 */

namespace backend\services;


use common\services\ToolService;
use yii\queue\base\JobHandler;

class SendNotice extends JobHandler
{
    public function handle($job,$data)
    {
        if($job->getAttempts() > 3){
            $this->failed($job);
        }

        $payload = $job->getPayload();

        $data = unserialize($payload);

        $url = $data['data']['url'];

        $contents = json_decode(ToolService::curl($url));

        if (!empty($contents) && $contents->status == 10) {

        } else {
            $this->failed($job);
        }
        //$payload即任务的数据，你拿到任务数据后就可以执行发邮件了
        //TODO 发邮件
    }

    public function failed($job,$data)
    {
        die("发了3次都失败了，算了");
    }
}