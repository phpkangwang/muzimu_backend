<?php
namespace common\models;

use Yii;


class OddsChangePath extends \backend\models\BaseModel
{
    /**
     *  机台的 type类型是1
     * @var int
     */
    public $typeMachine = 1;

    /**
     *  房间的 type类型是2
     * @var int
     */
    public $typeRoom = 2;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'odds_change_path';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
        ];
    }


    /**
     * 添加
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        try {
            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            $this->create_time = time();
            $this->admin_id    = $GLOBALS['user']['name'];
            if ( $this->save()) {
                return true;
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  游戏轨迹
     * @param $params
     * @return mixed
     */
    public function LocusPage($params)
    {
        $pageSize = $params['pageSize'];
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $lastId   = $params['lastId'];//上次请求最后一个id，防止分页数据重复

        $where = " game_type = {$params['gameType']} and type = {$params['type']} ";

        if ($lastId != "") {
            $where .= " and loc.id < '{$params['lastId']}'";
        }

        if ($params['adminId'] != "") {
            $where .= " and loc.admin_id = '{$params['adminId']}'";
        }

        if ($params['stime'] != "" && $params['etime'] != "") {
            $stime = strtotime($params['stime']);
            $etime = strtotime($params['etime']);
            $where .= " and loc.create_time >= '{$stime}' and loc.create_time < '{$etime}'";
        }

        $tableName = self::tableName();
        $sql       = " select loc.*
                 from  {$tableName}  loc 
                 where {$where}
                 order by loc.create_time desc,loc.id desc limit {$limit}
        ";

        $data      = self::getDb()->createCommand($sql)->queryAll();
        foreach ($data as $key => $val){
            $data[$key]['create_time'] = date("Y-m-d H:i:s", $val['create_time']);
            $data[$key]['created_at'] = $val['create_time'];
        }
        return $data;
    }
}
