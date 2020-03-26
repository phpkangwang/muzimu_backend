<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/31
 * Time: 11:58
 */

namespace backend\models\services;


use common\models\game\fire_unicorn\FivepkSeoFireUnicorn;
use common\models\game\star97\MachineListStar97;
use common\services\big_shark\BigShark;
use common\services\big_word\BigWord;
use common\services\fire_phoenix\Phoenix;
use common\services\Messenger;
use yii\base\Model;
use yii\db\Exception;

class MachineService extends Model
{
    public $model;

    public function init()
    {
        $this->model = new Messenger();
    }

    public function deleteMachine($type,$machine_id ){

        $params = [];
        try {
            if(empty($machine_id)){
                throw new Exception('机台号参数ID不存在');
            }
            switch ($type) {
                case 'HFH':
                    $model = new Phoenix();
                    $params = ['auto_id'=>$machine_id];
                    break;
                case 'DZB':
                    $params = ['auto_id'=>$machine_id];
                    $model = new BigWord();
                    break;
                case 'MXJ':
                    $params = ['auto_id'=>$machine_id];
                    $model = new MachineListStar97();
                    break;
                case 'DBS':
                    $params = ['auto_id'=>$machine_id];
                    $model = new BigShark();
                    break;
                case 'HQL':
                    $params = ['auto_id'=>$machine_id];
                    $model = new FivepkSeoFireUnicorn();
                    break;
            }
            if(empty($model)){
                throw new Exception('游戏类型不存在');
            }

            $data = $model->deleteMachine($params);

            $this->model->data = $data;

        }catch(Exception $e){
            $this->model->message = $e->getMessage();
            $this->model->status = 0;
        }

        return $this->model;
    }
}