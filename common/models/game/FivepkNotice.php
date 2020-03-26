<?php
namespace common\models\game;

use backend\models\redis\MyRedis;
use Yii;
use backend\models\Tool;

/**
 * This is the model class for table "fivepk_notice".
 *
 * @property integer $id
 * @property string $notice
 */
class FivepkNotice extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fivepk_notice';
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
            [['notice'], 'string', 'max' => 999]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'notice' => 'Notice',
        ];
    }

    /**
     * 添加修改
     * @param $data
     * @return array
     */
    public function add($data)
    {
        try{
            foreach ( $data as $key => $val )
            {
                $this->$key = $val;
            }
            if( $this->validate() && $this->save() )
            {
                $MyRedisObj = new MyRedis();
                $MyRedisObj->clear("game:FivepkNotice*");
                return $this->attributes;
            }else{
                throw new MyException( implode(",",$this->getFirstErrors()) );
            }
        }catch (MyException $e){
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     *  获取公告列表
     */
    public function getList()
    {
        $redisKey="game:FivepkNotice:getList";
        $redisData = $this->MyRedis->get($redisKey);
        if( empty($redisData) ) {
            $obj = self::find()->asArray()->all();
            $this->MyRedis->set($redisKey, json_encode($obj) );
            return $obj;
        }else{
            return json_decode($redisData);
        }
    }

    /**
     * 查找基本数据
     * @param $id
     * @return DataGameListInfo|mixed|null
     */
    public function findBase($id)
    {
        $redisKey="game:FivepkNotice:".$id;
        $redisData = $this->MyRedis->get($redisKey);
        if( empty($redisData) ) {
            $obj = self::find()->where(['id'=>$id])->asArray()->one();
            $this->MyRedis->set($redisKey, json_encode($obj));
            return $obj;
        }else{
            return json_decode($redisData, true);
        }
    }

    /**
     * 删除一个notice
     * @param $id
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function del($id)
    {
        $model = self::findOne($id);
        if( !empty($model) ){
            $model->delete();
        }
        //删除redis
        $MyRedisObj = new MyRedis();
        $MyRedisObj->clear("game:FivepkNotice*");
        return true;
    }

  /**
     * 返回数据库字段信息，仅在生成CRUD时使用，如不需要生成CRUD，请注释或删除该getTableColumnInfo()代码
     * COLUMN_COMMENT可用key如下:
     * label - 显示的label
     * inputType 控件类型, 暂时只支持text,hidden  // select,checkbox,radio,file,password,
     * isEdit   是否允许编辑，如果允许编辑将在添加和修改时输入
     * isSearch 是否允许搜索
     * isDisplay 是否在列表中显示
     * isOrder 是否排序
     * udc - udc code，inputtype为select,checkbox,radio三个值时用到。
     * 特别字段：
     * id：主键。必须含有主键，统一都是id
     * create_date: 创建时间。生成的代码自动赋值
     * update_date: 修改时间。生成的代码自动赋值
     */
    public function getTableColumnInfo(){
        return array(
        'id' => array(
                        'name' => 'id',
                        'allowNull' => false,
//                         'autoIncrement' => true,
//                         'comment' => '',
//                         'dbType' => "int(200)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => true,
                        'phpType' => 'integer',
                        'precision' => '200',
                        'scale' => '',
                        'size' => '200',
                        'type' => 'integer',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('id'),
                        'inputType' => 'hidden',
                        'isEdit' => true,
                        'isSearch' => true,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'notice' => array(
                        'name' => 'notice',
                        'allowNull' => true,
//                         'autoIncrement' => false,
//                         'comment' => '',
//                         'dbType' => "varchar(255)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '255',
                        'scale' => '',
                        'size' => '255',
                        'type' => 'string',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('notice'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		        );
        
    }
 
}
