<?php

namespace common\models;

use backend\models\Tool;
use common\services\JgPush;
use Yii;

/**
 * This is the model class for table "admin_group".
 *
 * @property integer $id
 * @property integer $id_parent
 * @property string $name
 * @property integer $status
 */
class PushManagement extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'push_management';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => '主键',
            'title'       => '标题',
            'content'     => '内容',
            'predict_num' => '预计数',
            'fact_num'    => '实际到达数',
            'admin_id'    => '操作的管理员id',
            'admin_name'  => '管理员名字',
            'updated_at'  => '修改时间',
            'created_at'  => '创建时间',
            'pushing_at'  => '发送时间',
        ];
    }

    /**
     * 获取列表
     * @param $pageNo
     * @param $pageSize
     * @return array|\yii\db\ActiveRecord[]
     */
    public function tablePageList($pageNo, $pageSize)
    {
        $obj = self::find()->orderBy('id desc');

        $page   = Tool::page($pageNo, $pageSize);
        $limit  = $page['limit'];
        $offset = $page['offset'];
        $obj->offset($offset);
        $obj->limit($limit);

        return $obj->asArray()->all();
    }

    /**
     * 获取列表
     * @param $pageNo
     * @param $pageSize
     * @return array|\yii\db\ActiveRecord[]
     */
    public function tableList()
    {
        $obj = self::find()->orderBy('id desc');
        return $obj->asArray()->all();
    }


    //定时推送
    public function runPushTiming()
    {
        $time   = time();
        $data   = $this::find()->where("timing_at<{$time} and status in(1,3)")->all();
        $JgPush = new JgPush();
        foreach ($data as $val) {
            $response     = $JgPush->push($val->content, $val->title);
            $responseData = ['response' => json_encode($response), 'status' => 3];
            if (isset($response['http_code']) && $response['http_code'] == 200 && isset($response['body']['msg_id'])) {
                //成功
                $responseData['status'] = 2;
                $responseData['msg_id'] = $response['body']['msg_id'];
            }
            $val->add($responseData);
        }
    }

    //定时获取
    public function runFactNumTimer()
    {
        $msgIds = [];
        $JgPush = new JgPush();
        $data   = $this::find()->where("fact_num_timer>0 and status=2")->indexBy('msg_id')->all();

        $msgIds = array_keys($data);

        $response = [];
        if ($msgIds) {
            $response = $JgPush->getReceived($msgIds);
        }

        if (isset($response['http_code']) && $response['http_code'] == 200 && isset($response['body'])) {
            foreach ($response['body'] as $value) {
                $data[$value['msg_id']]->add(
                    [
                        'fact_num'       => $value['android_received'],
                        'fact_ios_num'   => $value['ios_apns_sent'],
                        'fact_num_timer' => $data[$value['msg_id']]->fact_num_timer - 1,
                    ]
                );
            }
        }

    }


}
