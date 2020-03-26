<?php


namespace backend\controllers\activity;
use backend\models\MyException;
use backend\models\Tool;

class SignController extends \backend\controllers\MyController
{
    private $set=[];
    /**
     *   签到列表
     */
    public function actionSignList()
    {
        try {
            $signClass = new \common\models\activity\sign\ActivityListSign();
            $this->setData($signClass->getList($this->get));
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *   签到列表 增加/修改
     */
    public function actionSignAdd()
    {
        try {
            $signClass = new \common\models\activity\sign\ActivityListSign();
            $this->loginInfo;
            $this->setData($signClass->signAdd($this->post,$this->loginInfo['name']));
            $this->sendJson();
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  签到历史记录
     */
    public function actionGetActivitySignData()
    {
        try {
            $signClass = new \common\models\activity\sign\BackendSignData();

            $accuntId = '';

            $time = strtotime(Tool::examineEmpty($this->get['stime'], date('Y-m-d')));
            $week = date('w', $time);
            if ($week == 0) {
                $week = 7;
            }
            $oneDaySecond = 86400;
            $etime        = $time + $oneDaySecond;//当日24点
            $stime        = $etime - $week * $oneDaySecond;//周一凌晨
            $obj          = $signClass::find();

            $obj->select('id,account_id,nick_name,MAX(time) as max_time');
            $obj->orderBy('max_time desc');
            $obj->groupBy('account_id');
            $obj->andFilterWhere(['between', 'time', $stime * 1000, $etime * 1000]);
            $obj->andFilterWhere(['account_id' => Tool::examineEmpty($this->get['account_id'])]);
            $pageArr  = Tool::page(Tool::examineEmpty($this->get['pageNo'], 1), Tool::examineEmpty($this->get['pageSize'], 99999));
            $dataInfo = $obj->offset($pageArr['offset'])->limit($pageArr['limit'])->asArray()->all();


            $accuntIds = array_column($dataInfo, 'account_id');


            {
                /*填充数据*/
                $StoreItemListData = new \common\models\pay\StoreItemListData();
                $itemMap           = $StoreItemListData::find()->select('item_type,name')->asArray()->indexBy('item_type')->all();
                $itemMap[0]        = ['item_type' => 0, 'name' => '金币'];
                $obj               = $signClass::find();
                $obj->select('account_id,day,item_type,num');
//                $obj->indexBy('account_id');
                $obj->orderBy('day asc');
                $obj->andFilterWhere(['between', 'time', $stime * 1000, $etime * 1000]);
                $obj->andWhere(['in', 'account_id', $accuntIds]);
                $data = $obj->asArray()->all();

                $dataAccounts = [];
                foreach ($data as $key => $value) {
                    $dataAccounts[$value['account_id']][$value['day']] = [
                        'day'        => $value['day'],
                        'account_id' => $value['account_id'],
                        'item_type'  => $value['item_type'],
                        'num'        => $value['num'],
                    ];
                }

//                varDump($data);


                foreach ($dataInfo as &$val) {
                    $val['info'] = array(1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '', 7 => '');

                    //填充数据
                    foreach ($dataAccounts[$val['account_id']] as $key => $value) {
                        //初始化
                        $val['info'][$key]['item_name'] = $itemMap[$value['item_type']]['name'];
                        if (!isset($val['item']['道具总数'])) {
                            $val['item']['道具总数'] = 0;
                        }
                        //初始化
                        if (!isset($val['item']['金币'])) {
                            $val['item']['金币'] = 0;
                        }
                        if ($value['item_type'] > 0) {
                            $val['item']['道具总数'] += $value['num'];
                        } else {
                            $val['item']['金币'] += $value['num'];
                        }
                        $val['info'][$key]['num'] = $value['num'];
                    }
                }
            }

            if (isset($this->get['showCount'])) {
                //当日签到人数
                $obj = $signClass::find();
                $num = $obj->andFilterWhere(['between', 'time', $time * 1000, $etime * 1000])->groupBy('account_id')->count();
                $this->setData(['total' => $num, 'list' => $dataInfo]);
            } else {
                $this->setData($dataInfo);
            }

            $this->sendJson();

        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }

    }


}