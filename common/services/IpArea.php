<?php
/**
 * Created by PhpStorm.
 * User: jipeng
 * Date: 2019/4/26/005
 * Time: 10:15
 */

namespace common\services;

use backend\models\MyException;
use common\models\IpAddress;
use Ip\Ip2Region\Ip2Region;
use backend\models\Tool;
use Yii;

class IpArea
{

    public $time;
    public $address;


    /**
     *  初始化
     * @param $ip string ip地址
     * @return mixed
     */
    public function init($ip)
    {
        //初始化
        $this->address = array(
            'ip' => $ip,//IP
            'country' => '',//国家
            'area' => '',//地区名称
            'region' => '',//省名称
            'city' => '',//市名称
            'county' => '',//县名称
            'address' => '',//省市区地址
            'isp' => '',//服务商名称
            'updated_at' => '',//修改时间
            'created_at' => '',//创建时间
        );
        $this->time = time();
    }


    /**
     *  设置数据库
     * @return mixed
     */

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * 获取ip地址
     * @return bool
     */
    public function getIpAddress()
    {
        try {
            $db = new IpAddress;
            $data = $db->findBase($this->address['ip']);

            if (!empty($data)) {
                $maxTime = &Yii::$app->params['ipMaxTime'];
                //如果在规定时间内则返回数据否则重新获取一下
                if (($this->time - $data['updated_at']) < $maxTime) {
                    return $data;
                }
            }

            if (!$this->getTaobaoAddress()) {
                if (!$this->getIp138Address()) {
                    if (!$this->getLocalDbIpAddress()) {
                        return false;
                    }
                }
            }

            return $this->saveDb($db, $data);

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 根据ip获取淘宝ip地址
     * @return bool
     */
    public function getTaobaoAddress()
    {
        $result = \common\services\ToolService::send_get("http://ip.taobao.com/service/getIpInfo.php?ip={$this->address['ip']}", []);
        if ($result !== false) {
            $result = json_decode($result);
            /*
            返回参数：
            region省名称
            area区域
            city城市名
            country国家
            county县名称
            isp运营商
            */

            if (!empty($result)) {
                $this->address['city'] = self::getVerifyContent($result->data->city, 'XX');
                $this->address['isp'] = self::getVerifyContent($result->data->isp, 'XX');
                $this->address['country'] = self::getVerifyContent($result->data->country, 'XX');
                $this->address['area'] = self::getVerifyContent($result->data->area, 'XX');
                $this->address['region'] = self::getVerifyContent($result->data->region, 'XX');
                $this->address['county'] = self::getVerifyContent($result->data->county, 'XX');
                $this->joinAddress();
                $this->getTool()->myLog("淘宝IP获取：{$this->address['ip']} ".json_encode($result));
                return true;
            }
            return false;
        }

        return false;
    }

    //获取$Tool类
    private function getTool()
    {
        static $Tool;
        if (empty($Tool)) {
            $Tool = new Tool();
        }
        return $Tool;
    }

    /**
     * 如果数据等于$verify则返回空
     * @param $data string 数据
     * @param $verify string 验证
     * @return string
     */
    static public function getVerifyContent(&$data, $verify = '')
    {
        return ($data === $verify ? '' : $data);
    }

    /**
     * 根据ip获取ip138付费ip地址
     * @return bool
     */
    public function getIp138Address()
    {
        //付费查找IP
        $result = \common\services\ToolService::send_get("http://api.ip138.com/query/?ip={$this->address['ip']}&datatype=jsonp&callback=&token=f9fde6fd09a0f55028ad22d251f70cfb", []);
        if ($result !== false) {
            $result = json_decode($result);
            /*
            [0]国家
            [1]省名称
            [2]市名称
            [3]运营商
            [4]
            [5]
            */
            if (!empty($result) && $result->ret == 'ok') {
                $this->address['city'] = $result->data[2];
                $this->address['isp'] = $result->data[3];
                $this->address['country'] = $result->data[0];
                $this->address['region'] = $result->data[1];
                $this->joinAddress();
                $this->getTool()->myLog("获取ip138付费ip地址：{$this->address['ip']} ".json_encode($result));
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * 拼接地址
     */
    private function joinAddress()
    {
        $this->address['address'] = $this->address['country'] . ' ' . $this->address['city'] . ' ' . $this->address['isp'];
    }

    /**
     * 根据ip获取本地包ip的地址
     * @return bool
     */
    public function getLocalDbIpAddress()
    {
        $obj = new Ip2Region("/ip2region.db");
        $ipData = $obj->btreeSearch($this->address['ip']);
        $arr = explode("|", $ipData['region']);

        if (is_array($arr) && (count($arr) > 4)) {
            $this->address['country'] = self::getVerifyContent($arr[0], 0);
            $this->address['area'] = self::getVerifyContent($arr[1], 0);
            $this->address['region'] = self::getVerifyContent($arr[2], 0);
            $this->address['city'] = self::getVerifyContent($arr[3], 0);
            $this->address['isp'] = self::getVerifyContent($arr[4], 0);
            $this->joinAddress();
            $this->getTool()->myLog("本地包ip的地址：{$this->address['ip']} ".json_encode($arr));
//            print_r($this->address);
            return true;
        }

        return false;
    }

    /**
     *  添加/修改 db数据
     * @param $data object 数据
     * @return mixed
     */
    public function saveDb(&$db, $data)
    {
        $addData = array(
            'ip' => $this->address['ip'],//IP
            'country' => $this->address['country'],//国家
            'area' => $this->address['area'],//地区名称
            'region' => $this->address['region'],//省名称
            'city' => $this->address['city'],//市名称
            'county' => $this->address['county'],//县名称
            'address' => $this->address['address'],//省市区地址
            'isp' => $this->address['isp'],//服务商名称
            'updated_at' => $this->time,//修改时间
            'created_at' => $data['created_at'],//创建时间
        );
        if (isset($data['id'])) {
            //修改
//            $addData['id'] = $data['id'];
            if (empty($data['created_at'])) {
                $addData['created_at'] = $this->time;
            }

            $db = $db->findObj($this->address['ip']);
        } else {
            //创建
            $addData['created_at'] = $this->time;
        }

        return $db->add($addData);
    }

    /**
     * 序列化数据
     * @param array $arr 需要的字段
     * @param string $str 分割符
     * @return string
     */
    public function serializeData($arr, $str = ' ')
    {
        try {
            if (empty($arr)) {
                return '';
            }
            $this->address = $this->getIpAddress();
            foreach ($arr as $key) {
                if (empty($this->address[$key])) {
                    continue;
                }
                $list[$key] = $this->address[$key];
            }

            if (isset($list)) {
                return implode($str, $list);
            }
            return '';

        } catch (MyException $e) {
            $this->getTool()->myLog("IP序列化失败：{$this->address['ip']} " . json_encode($arr) . "'$str'");
            return '';
        }

    }

    /**
     * 获取用户登录的ip地址
     * @param $ip
     * @return string
     */
    public function getLoginIpAddress( $ip )
    {
        //查询Ip
        $IpArea = new self();
        if( strpos($ip, "192.168") === false ){
            $IpArea->init($ip);
            $ip_address=$IpArea->getIpAddress();
            $address = $ip_address['country'].'-'.$ip_address['region'].'-'.$ip_address['city'].'-'.$ip_address['isp'];
        }else{
            $address = "内网登录";
        }
        return $address;
    }
}