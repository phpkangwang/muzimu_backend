<?php
namespace common\models;

use backend\models\Account;
use backend\models\AdminUser;
use Yii;

/**
 * This is the model class for table "room_path".
 *
 * @property integer $id
 * @property integer $id_target
 * @property string $before
 * @property string $after
 * @property integer $created_by
 * @property integer $created_at
 */
class RoomPath extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'room_path';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
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
            [['created_by', 'created_at'], 'integer'],
            [['id_target', 'before', 'after'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'id_target' => '关联id',
            'before' => '操作前',
            'after' => '操作后',
            'created_by' => '创建人',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 关联创建人
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Account::className(),['id'=>'created_by'])->select($this->Account->baseColumns);
    }

    /**
     * @desc 新增房间机率设定轨迹
     * @param $id_target
     * @param $before
     * @param $after
     * @param $created_by
     * @return bool
     */
    public static function Create($id_target,$before,$after,$created_by)
    {
        $model = new RoomPath();
        $model -> id_target = $id_target;
        $model -> before = $before;
        $model -> after = $after;
        $model -> created_by = $created_by;
        $model -> created_at = time();
        if($model->validate() && $model->save()){
            return true;
        }else{
            return false;
        }
    }

    /**
     *  分页
     * @param $pageNo
     * @param $pageSize
     * @param $where
     * @param $gameName  游戏名称
     * @return array
     */
    public function page($pageNo, $pageSize, $where, $gameName)
    {
        $chineseGameName = Yii::$app->params['game'][$gameName];
        $gameModelPath = Yii::$app->params[$chineseGameName]['roomModel'];
        $gameModel = new $gameModelPath;
        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 100 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo-1)*$pageSize;
        $data = self::find()->joinWith('user')->where($where)->orderBy('id DESC')->offset($offset)->limit($limit)->asArray()->all();;
        foreach ($data as $key=>$val){
            $data[$key]['path'] = array();
            $beforeDatas = json_decode($val['before'], true);
            $afterDatas  = json_decode($val['after'], true);
            foreach ($beforeDatas as $beforeDataKey => $beforeData){
                foreach ($afterDatas as $afterDataKey => $afterData){
                    if($beforeDataKey == $afterDataKey){
                        $data[$key]['path'][] = $gameModel->getAttributeLabel($beforeDataKey)." :".$beforeData." -> " .$afterData;
                    }
                }
            }
        }
        return $data;
    }

    /**
     *  获取最大条数
     */
    public function accountNum($where)
    {
        return self::find()->joinWith('user')->where($where)->count();
    }
 
}
