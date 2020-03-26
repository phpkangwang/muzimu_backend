<?php
namespace backend\services;

use backend\models\AdminLog;
use common\models\DataErrorCode;
use common\services\Messenger;
use common\services\ToolService;

class AdminLogService extends AdminLog
{
    /**
     * 增加留机时间
     * @return mixed|null
     */
    public static function AddReservationTime()
    {
        $message = new Messenger();
        $maintenance_time = AdminLog::find()->select(['create_date'])
            ->filterWhere(['controller_id'=>'machine','action_id'=>'game-update'])
            ->andFilterWhere(['like','content','"gameNumber":0'])
            ->andFilterWhere(['like','content','"gameSwitch":"1"'])
            ->andFilterWhere(['relevance_data'=>'0-1'])
//            ->andFilterWhere(['>','create_date',date('Y-m-d H:i:s',time())])
            ->orderBy('id DESC')
            ->one();
        if(!empty($maintenance_time)){
            $reservation_time = time()-strtotime($maintenance_time->create_date);
            $data['data'] = json_encode([
                'machineList' => [],
                'changeTime' => $reservation_time,
                'isAll'=> 1,//0-非全部，1-全部
            ]);

            $url = \Yii::$app->params['url']."/changeMachineReservationDate";
            $contents = json_decode(ToolService::send_post($url,$data));
            if(!empty($contents)) {
                $message->status = $contents->status;
                $error_code = empty($contents->message)?0:$contents->message;
                $message->message = DataErrorCode::getMessage($error_code);
            }else{
                $message->status = 0;
                $message->message = '请求不到服务器数据';
            }
        }else{
            $message -> status = 0;
            $message -> message = '找不到时间！';
        }
        return $message;
    }
   
}
