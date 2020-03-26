<?php
namespace common\models;

use backend\models\MyException;
use backend\models\redis\MyRedis;
use backend\models\Tool;
use Yii;

/**
 * This is the model class for table "ip_address".
 *
 * @property integer $id
 * @property string $ip
 * @property string $country
 * @property string $area
 * @property string $region
 * @property string $city
 * @property string $county
 * @property string $address
 * @property string $isp
 */
class IpAddress extends \backend\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ip_address';
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
            [[ 'ip', 'updated_at'], 'required'],
            [['ip', 'country', 'area', 'region', 'city', 'county'], 'string', 'max' => 50],
            [['address'], 'string', 'max' => 255],
//            [['isp'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'IP',
            'country' => '国家',
            'area' => '地区名称',
            'region' => '省名称',
            'city' => '市名称',
            'county' => '县名称',
            'address' => '省市区地址',
            'isp' => '服务商名称',
            'updated_at' => '修改时间',
            'created_at' => '创建时间',
        ];
    }


    /**
     * 添加修改
     * @param $data
     * @return array
     */
    public function add($data)
    {
        try {

            foreach ($data as $key => $val) {
                $this->$key = $val;
            }
            if ($this->validate() && $this->save()) {

                //添加修改的时候要修改单个redis值即可
                $attributes = $this->attributes;
                $this->MyRedis->writeCacheHash($this->getRedisCacheName(), $attributes['ip'], $attributes);
                return $attributes;
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 查找基本数据
     * @param $ip
     * @return DataGameListInfo|mixed|null
     */
    public function findBase($ip)
    {
        $redisData = $this->MyRedis->readCacheHash($this->getRedisCacheName(), $ip);
        if (empty($redisData)) {
            $arr = self::find()->where('ip = :ip',array( ':ip'=>$ip))->asArray()->one();
            if (!empty($arr)) {
                $this->MyRedis->writeCacheHash($this->getRedisCacheName(), $ip, $arr);
            }
            return $arr;
        } else {
            return $redisData;
        }
    }

    /**
     * 查找基本数据
     * @param $ip
     * @return DataGameListInfo|mixed|null
     */
    public function findObj($ip)
    {
        return self::find()->where('ip = :ip', array(':ip' => $ip)) -> one();
    }

    //获取key
    private function getRedisCacheName(){
        $key = 'ipAddressDb';
        return $key;
    }

    //删除redis缓存
    public function delRedisAll()
    {
        $key = $this->getRedisCacheName();
        $keyVal = MyRedis::getRedisConfig($key);
        $this->MyRedis->DEL($keyVal['name']);//删除本key
    }

    /**
     * 获得ip数据库数据不需要去包里面查询
     * @param $ips array ip
     * @param $keys array keys
     * @return array
     */

    public function getMysqlDbList($ips,$keys='')
    {
//        $maxNum = 10000;
//        if (count($ips) > $maxNum) {
//            return false;
//        }
        //由于是内部用所以这里暂时不校验

        //$Tool = new Tool();
        $inStr = Tool::ArrToMysqlInstr($ips, 1);
        $columnStr = Tool::ArrToMysqlInstr($keys, 2);
        $tableName = self::tableName();
        $sql = "select {$columnStr} from {$tableName}  where ip in {$inStr}";
        $data = Yii::$app->db->createCommand($sql)->queryAll();
        return $data;
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
//                         'dbType' => "int(11)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => true,
                        'phpType' => 'integer',
                        'precision' => '11',
                        'scale' => '',
                        'size' => '11',
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
		'ip' => array(
                        'name' => 'ip',
                        'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => 'IP',
//                         'dbType' => "varchar(50)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '50',
                        'scale' => '',
                        'size' => '50',
                        'type' => 'string',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('ip'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'country' => array(
                        'name' => 'country',
                        'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '国家',
//                         'dbType' => "varchar(50)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '50',
                        'scale' => '',
                        'size' => '50',
                        'type' => 'string',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('country'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'area' => array(
                        'name' => 'area',
                        'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '地区名称',
//                         'dbType' => "varchar(50)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '50',
                        'scale' => '',
                        'size' => '50',
                        'type' => 'string',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('area'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'region' => array(
                        'name' => 'region',
                        'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '省名称',
//                         'dbType' => "varchar(50)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '50',
                        'scale' => '',
                        'size' => '50',
                        'type' => 'string',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('region'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'city' => array(
                        'name' => 'city',
                        'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '市名称',
//                         'dbType' => "varchar(50)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '50',
                        'scale' => '',
                        'size' => '50',
                        'type' => 'string',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('city'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'county' => array(
                        'name' => 'county',
                        'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '县名称',
//                         'dbType' => "varchar(50)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '50',
                        'scale' => '',
                        'size' => '50',
                        'type' => 'string',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('county'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'address' => array(
                        'name' => 'address',
                        'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '省市区地址',
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
                        'label'=>$this->getAttributeLabel('address'),
                        'inputType' => 'text',
                        'isEdit' => true,
                        'isSearch' => false,
                        'isDisplay' => true,
                        'isSort' => true,
//                         'udc'=>'',
                    ),
		'isp' => array(
                        'name' => 'isp',
                        'allowNull' => false,
//                         'autoIncrement' => false,
//                         'comment' => '服务商名称',
//                         'dbType' => "varchar(10)",
                        'defaultValue' => '',
                        'enumValues' => null,
                        'isPrimaryKey' => false,
                        'phpType' => 'string',
                        'precision' => '10',
                        'scale' => '',
                        'size' => '10',
                        'type' => 'string',
                        'unsigned' => false,
                        'label'=>$this->getAttributeLabel('isp'),
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
