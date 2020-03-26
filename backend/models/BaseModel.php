<?php
namespace backend\models;

use Yii;
abstract class BaseModel extends \yii\db\ActiveRecord
{
    public $models = array();
    public $where='';
    public $pdo=[];
    public $pageNo=1;
    public $pageSize=9999;
    public $potion=[];
    public $dbLink='';

    public function __get($className)
    {
        //模仿单例模式避免多次new
        if (isset($this->models[$className])) {
            return $this->models[$className];
        }

        //class路径缓存到redis，避免重复循环目录
        $classDir = "";
        if (empty($classDir)) {
            $defaultClassDirs = ["backend" . DIRECTORY_SEPARATOR . "models", "common" . DIRECTORY_SEPARATOR . "models"];
            $dirs = [Yii::getAlias("@backend_models"), Yii::getAlias("@common_models")];
            foreach ($defaultClassDirs as $key => $defaultClassDir) {
                $dir = $dirs[$key];
                $fileName = $className . ".php";
                $modelsDir = $this->findDir($dir, $fileName, $defaultClassDir);
                if ($modelsDir != "") {
                    $classDir = $modelsDir;
                    break;
                }
            }
        }

        if ($classDir != "") {
            $config = ['class' => $classDir . DIRECTORY_SEPARATOR . $className];
            $config = str_replace("/", "\\", $config);
            $obj = Yii::createObject($config);
            $this->models[$className] = $obj;
            return $obj;
        } else {
            return parent::__get($className);
        }
    }

    //递归目录查找文件
    public function findDir($dir, $fileName, $ClassDir)
    {
        $modelsDirs = array();
        $dirArrs = scandir($dir);
        if (in_array($fileName, $dirArrs)) {
            return $ClassDir;
        } else {
            //获取所有的子目录
            foreach ($dirArrs as $dirArr) {
                if (strpos($dirArr, ".") === false) {
                    array_push($modelsDirs, DIRECTORY_SEPARATOR . $dirArr);
                }
            }
            foreach ($modelsDirs as $dirArr) {
                $rs = $this->findDir($dir . $dirArr, $fileName, $ClassDir . $dirArr);
                if ($rs != "") {
                    return $rs;
                }
            }
            return "";
        }
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
            if ($this->validate() && $this->save()) {
                return $this->attributes;
            } else {
                throw new MyException(implode(",", $this->getFirstErrors()));
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 查找基本数据
     * @param $id
     * @return DataGameListInfo|mixed|null
     */
    public function findBase($id)
    {
        $obj = self::find()->where(['id' => $id])->asArray()->one();
        return $obj;
    }

    /**
     *  根据自定义字段 查找基本数据
     * @param $column  key
     * @param $value   值
     * @param string $returnType  返回类型
     * @return array|null|\yii\db\ActiveRecord
     */
    public function findObj($column, $value, $returnType="obj"){
        $query = self::find()->where([$column => $value]);
        if($returnType != "obj"){
            $query = $query->asArray();
        }
        return $query->one();
    }

    /**
     * 查找基本数据
     * @param $Field
     * @param $value
     * @param $isObj
     * @return mixed
     */
    public static function findOneByField($Field, $value,$isObj = true)
    {
        $obj = self::find()->where("$Field = :$Field", array(":$Field" => $value));

        if (!$isObj) {
            $obj->asArray();
        }
        return $obj->one();
    }


    /**
     * 分页
     * @param $page int 页数
     * @param $pageSize  int 每页条数
     * @param $option mixed 条件
     * @return mixed
     */
    public function pageList($page, $pageSize = 8, $option = [])
    {
        $obj = self::find();
        if (isset($option['where']) && !empty($option['where'])) {
            if (isset($option['whereOption'])) {
                $objWhere = Tool::arrayToSql($option['where'], $option['whereOption']);
                if (!Tool::isIssetEmpty($objWhere['where'])) {
                    $obj->where($objWhere['where'], $objWhere['value']);
                }
            } elseif (isset($option['pdo'])) {
                $obj->where($option['where'], $option['pdo']);
            } else {
                //必需要PDO否则返回空
                return [];
            }
        }
        if (isset($option['select']) && !empty($option['select'])) {
            $obj->select($option['select']);
        }

        if (isset($option['order']) && !empty($option['order'])) {
            $obj->orderBy($option['order']);
        }

        if (isset($option['groupBy']) && !empty($option['groupBy'])) {
            $obj->groupBy($option['groupBy']);
        }

        if (isset($option['indexBy']) && !empty($option['indexBy'])) {
            $obj->indexBy($option['indexBy']);
        }

        if(!Tool::isIssetEmpty($option['obj'])){
            $option['obj']($obj);
        }

        if (isset($option['leftJoin']) && !empty($option['leftJoin'])) {
            $obj->leftJoin($option['leftJoin']['table'],$option['leftJoin']['on']);
        }

        $page = Tool::page($page, $pageSize);
        $data = $obj->offset($page['offset'])->limit($page['limit'])->asArray()->all();
//echo $obj->createCommand()->getRawSql().PHP_EOL;
        return $data;
    }

    /**
     * 优化后的分页
     * @return mixed
     */
    public function pageData()
    {
        if (empty($this->dbLink)) {
            $this->getDbLink();
        }

        if (!empty($this->where)) {
            $this->dbLink->where($this->where, $this->pdo);
        }

        if (!Tool::isIssetEmpty($this->potion['link'])) {
            $this->potion['link']($this->dbLink);
        }
        $page = Tool::page($this->pageNo, $this->pageSize);
        $data = $this->dbLink->offset($page['offset'])->limit($page['limit'])->asArray()->all();
//echo $this->dbLink->createCommand()->getRawSql().PHP_EOL;
        return $data;
    }

    /**
     * 设置where 这里只做简单的=和like 例:->setWhere('account_id', $this->get)
     * @param $fields string|array 字段
     * @param $array  array 数组
     * @param $noLike bool like查询
     * @return object
     */
    public function setWhere($fields,&$array,$noLike=true)
    {
        if (is_string($fields)) {
            $fields = [$fields, $fields];
        }
        foreach ($fields as $fieldKey => $fieldString) {
            if (!Tool::isIssetEmpty($array[$fieldKey])) {
                if (!empty($this->where)) {
                    $this->where .= ' and ';
                }
                if ($noLike) {
                    $this->where .= "$fieldString =:$fieldString";
                    $this->pdo[":$fieldString"] = ($array[$fieldKey]);
                } else {
                    $this->where .= "$fieldString like :$fieldString";
                    $this->pdo[":$fieldString"] = "%{$array["$fieldKey"]}%";
                }
            }
        }
        return $this;
    }

    public function setPage($get)
    {
        $this->pageNo = Tool::examineEmpty($get['pageNo'], 1);
        $this->pageSize = Tool::examineEmpty($get['pageSize'], 99999);
    }

    public function getDbLink()
    {
        return $this->dbLink = self::find();
    }

    public static function query($sql)
    {
        return self::getDb()->createCommand($sql)->query();
    }

    public static function queryAll($sql)
    {
        return self::getDb()->createCommand($sql)->queryAll();
    }


}

?>