<?php

namespace backend\models;

use common\models\utils\QRcode;
use common\services\Rsa;
use Ip\Ip2Region\Ip2Region;
use Yii;

/**
 * 这是一个工具类
 * Class Tool
 */
class Tool
{

    //常用时间格式
    const DATE_USUALLY_FORMAT = 'Y-m-d H:i:s';

    //一天的秒数
    const ONE_DAY_SECOND_NUM = '86400';

    //二维数组排序 冒泡排序
    public static function ArrSort($arr, $column, $sortType)
    {
        //重置数组下标
        $arr   = array_values($arr);
        $count = count($arr);
        for ($i = 0; $i < $count - 1; $i++) {
            for ($j = 0; $j < $count - 1 - $i; $j++) {
                if ($sortType == "asc") {
                    if ($arr[$j][$column] > $arr[$j + 1][$column]) {
                        $temp        = $arr[$j];
                        $arr[$j]     = $arr[$j + 1];
                        $arr[$j + 1] = $temp;
                    }
                } else {
                    if ($arr[$j][$column] < $arr[$j + 1][$column]) {
                        $temp        = $arr[$j];
                        $arr[$j]     = $arr[$j + 1];
                        $arr[$j + 1] = $temp;
                    }
                }

            }
        }
        return $arr;
    }


    //一维数组自定义排序  不在排序数组里面的插到数组的最后面
    public static function MySort($fromArr, $sortArr)
    {
        $toArr    = array();
        $errorArr = array();
        foreach ($sortArr as $val) {
            isset($fromArr[$val]) ? $toArr[$val] = $fromArr[$val] : "";
        }
        return array_merge($toArr, $errorArr);
    }

    //根据游戏类型计算盈利
    public static function getProfitByGame($gameType, $arr)
    {
        switch ($gameType) {
            case 1://火凤凰
                //$arr['盈利'] = (($arr['总玩分数'] + $arr['比倍总玩分']) - ($arr['比倍总赢分'] + $arr['总赢分数'] + $arr['过关分数'] + $arr['爆机分数'])) /100;
                break;
            case 2:
                break;
            case 3:
                break;
        }

    }

    //数组转换成sql语句中的in
    public static function arrayToSqlInStr($arr)
    {
        $inStr = implode(",", $arr);
        return $inStr;
    }

    //两个数组合并
    public static function arrayPushArray($arr1, $arr2, $site = 'top')
    {
        if ($site = 'top') {
            foreach ($arr2 as $val) {
                array_unshift($arr1, $val);
            }
        } else {
            foreach ($arr2 as $val) {
                array_push($arr1, $val);
            }
        }
        return $arr1;
    }


    // yii2对象转换成数组
    public static function objToArr($objs)
    {
        if (!empty($objs)) {
            foreach ($objs as $key => $obj) {
                $objs[$key] = $obj->attributes;
            }
        }
        return $objs;
    }

    /**
     * 生成一个不重复的随机数
     * @return string
     */
    public static function createRand()
    {
        return base64_encode(time() . rand(10000, 99999));
    }

    //前端验证码生成
    public static function getYzmCode()
    {
        $arr       = "1234567890";
        $YzmLength = 4;
        $code      = "";
        for ($i = 0; $i < $YzmLength; $i++) {
            $arrLength = strlen($arr);
            substr($arr, rand(0, $arrLength - 1), 1);
            $code .= ((String)substr($arr, rand(0, $arrLength - 1), 1));
        }
        return $code;
    }

    //生成验证码图片
    public static function getYzmImage($str)
    {
        //验证码图片的宽度
        $width = 50;
        //验证码图片的高度
        $height = 25;
        //声明需要创建的图层的图片格式
        //@ header("Content-Type:image/png");
        //创建一个图层
        $im = imagecreate($width, $height);
        //背景色
        $back = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
        //模糊点颜色
        $pix = imagecolorallocate($im, 187, 230, 247);
        //字体色
        $font = imagecolorallocate($im, 41, 163, 238);
        //绘模糊作用的点
        mt_srand();
        for ($i = 0; $i < 1000; $i++) {
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $pix);
        }
        //输出字符
        imagestring($im, 5, 7, 5, $str, $font);
        //输出矩形
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $font);
        //输出图片
        //$fullUrl = $url."/images/yzm.png";
        imagepng($im);
        imagedestroy($im);
        //$base64_img = self::base64EncodeImage($fullUrl);
        //return $base64_img;
    }

    //将图片base64化
    public static function base64EncodeImage($image_file)
    {
        $base64_image = '';
        $image_info   = getimagesize($image_file);
        $image_data   = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }

    /**
     * 获取mac地址，假如有多个只算一个
     * @return string
     */
    public static function getMac()
    {
        $match = "/(\S{2}-){5}\S{2}$/";
        @exec("ipconfig /all", $array);
        for ($Tmpa = 0; $Tmpa < count($array); $Tmpa++) {
            $arr = explode(":", $array[$Tmpa]);
            foreach ($arr as $a) {
                $a = trim($a);
                if (preg_match($match, $a)) {
                    if (mb_strlen($a, 'utf-8') <= 20) {
                        return $a;
                    }
                }
            }
        }
        return "";
    }

    /**
     * 获取浏览器信息
     * @return string
     */
    public static function getBrowser()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $br = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/Chrome/', $br)) {
                return "Chrome";
            } else if (preg_match('/Firefox/', $br)) {
                return "Firefox";
            } else if (preg_match('/Safar/', $br)) {
                return "Safar";
            } else if (preg_match('/Opera/', $br)) {
                return "Opera";
            } else {
                return "Other";
            }
        } else {
            return "";
        }
    }

    /**
     * 获取ip
     * @return mixed
     */
    public static function getIp()
    {
        //return Yii::$app->getRequest()->getUserIP();
        $onlineip = 'Unknown';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips     = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
            $real_ip = $ips['0'];
            if ($_SERVER['HTTP_X_FORWARDED_FOR'] && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $real_ip)) {
                $onlineip = $real_ip;
            } elseif ($_SERVER['HTTP_CLIENT_IP'] && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
                $onlineip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
        if ($onlineip == 'Unknown' && isset($_SERVER['HTTP_CDN_SRC_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CDN_SRC_IP'])) {
            $onlineip  = $_SERVER['HTTP_CDN_SRC_IP'];
            $c_agentip = 0;
        }
        if ($onlineip == 'Unknown' && isset($_SERVER['HTTP_NS_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER ['HTTP_NS_IP'])) {
            $onlineip  = $_SERVER ['HTTP_NS_IP'];
            $c_agentip = 0;
        }
        if ($onlineip == 'Unknown' && isset($_SERVER['REMOTE_ADDR']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['REMOTE_ADDR'])) {
            $onlineip  = $_SERVER['REMOTE_ADDR'];
            $c_agentip = 0;
        }
        return $onlineip;
    }

    /**
     * 根据ip获取ip地址
     * @param $ip
     * @return array
     */
    public static function getIpAddress($ip)
    {
        $obj    = new Ip2Region("/ip2region.db");
        $ipData = $obj->btreeSearch($ip);
        $arr    = explode("|", $ipData['region']);
        unset($arr[0]);
        unset($arr[1]);
        unset($arr[2]);
        foreach ($arr as $key => $val) {
            if (empty($val)) {
                unset($arr[$key]);
            }
        }
        return implode("-", $arr);
//         return  array(
//             'country'  => $arr[0],
//             'region'   => $arr[1] == 0 ? "" : $arr[1],
//             'province' => $arr[2] == 0 ? "" : $arr[2],
//             'city'     => $arr[3] == 0 ? "" : $arr[3],
//             'isp'      => $arr[4] == 0 ? "" : $arr[4],
//         );
    }

    /**
     * 获得设备信息
     * @return array
     */
    public static function getAgentInfo()
    {
        $UA                = $_SERVER['HTTP_USER_AGENT'];
        $result            = [
            'device'  => '',//设备
            'browser' => '',//浏览器
            'os'      => '',//操作系统
            'ip'      => '',//ip
            'address' => '',//地址
        ];
        $device_os         = substr($UA, stripos($UA, '(') + 1, stripos($UA, ')') - stripos($UA, '(') - 1);
        $device_os_explode = explode(';', $device_os);
        if (strpos($device_os, 'Android') !== false) {//android系统
            foreach ($device_os_explode as $val) {
                if (strpos($val, 'Android') !== false) {
                    $result['os'] = $val;
                } elseif (strpos($val, 'Build')) {
                    $result['device'] = $val;
                } else {
                    continue;
                }
            }
            foreach (explode(' ', $UA) as $key => $value) {
                if (strpos($value, 'Mobile') !== false) {
                    $result['browser'] = explode(' ', $UA)[$key - 1];
                } else {
                    continue;
                }
            }

        } elseif (strpos($device_os, 'iPhone') !== false) {//ios系统
            $result['device'] = 'iPhone';
            $result['os']     = 'IOS ' . explode(' ', $device_os_explode[1])[4];
            foreach (explode(' ', $UA) as $key => $value) {
                if (strpos($value, 'Mobile') !== false) {
                    $result['browser'] = explode(' ', $UA)[$key - 1];
                } else {
                    continue;
                }

            }
        } elseif (strpos($device_os, 'Windows') !== false) {//window系统
            $window = [
                'Windows NT 5.0'  => 'Windows 2000',
                'Windows NT 5.1'  => 'Windows XP',
                'Windows NT 5.2'  => 'Windows Server 2003',
                'Windows NT 5.2'  => 'Windows Server 2003 R2',
                'Windows NT 6.0'  => 'Windows Vista',
                'Windows NT 6.1'  => 'Windows 7',
                'Windows NT 6.2'  => 'Windows 8.0',
                'Windows NT 6.3'  => 'Windows 8.1',
                'Windows NT 10.0' => 'Windows 10',
            ];
            foreach ($window as $key => $value) {
                if (strpos($device_os, $key) !== false) {
                    $result['os'] = $value;
                }
            }
            $result['device'] = '1';
            foreach (explode(' ', $UA) as $key => $value) {
                if (strpos($value, 'Safari') !== false) {
                    $result['browser'] = explode(' ', $UA)[$key - 1];
                } else {
                    continue;
                }

            }
        }
        $result['ip']      = self::getIp();
        $result['address'] = self::getIpAddress($result['ip']);
        return $result;
    }

    /**
     *   写测试日志
     * @param $content
     * @param $fileName
     * @return bool
     */
    public static function myLog($content, $fileName = "")
    {
        if (empty($fileName)) {
            $url = Yii::getAlias("@myLog") . DIRECTORY_SEPARATOR . 'testlog' . date('Ymd', time()) . ".log";
        } else {
            $url = Yii::getAlias("@myLog") . DIRECTORY_SEPARATOR . $fileName;
        }

        $content = "执行时间:" . date('Y-m-d H:i:s', time()) . $content . "\r\n";

        self::writeFile($url, $content, 'a+');

        return true;
    }

    /**
     *  向文件里面写入数据
     * @param $filePath
     * @param $content
     * @param string $mode
     * @return bool
     */
    public static function writeFile($filePath, $content, $mode = "a+")
    {
        $file = fopen($filePath, $mode) or die("Unable to open file!");
        fwrite($file, $content);
        fclose($file);
        return true;
    }

    /**
     * 流上传文件
     * @param $dir
     * @param $fileName
     * @param $content
     * @return bool
     */
    public function streamUploadFile($dir, $fileName, $content)
    {
        //首先保证文件目录必须存在
        self::createFolders($dir);
        $filePath = $dir . $fileName;
        self::writeFile($filePath, $content);
        return true;
    }

    /**
     * 二维数组字段合并成一个一维数组,不改变原数组的排序
     * @param $array
     * @param $columnKey
     * @param $columnVal
     * @return array
     */
    public function arrayColumnToArr($array, $columnKey, $columnVal)
    {
        $rs = array();
        foreach ($array as $key => $val) {
            $rs[$val[$columnKey]] = $val[$columnVal];
        }
        return $rs;
    }

    /**
     * 去掉名字中的X
     * @param $name
     */
    public function clearNameX($name)
    {
        $arr = explode("X", $name);
        return $arr[0];
    }

    /**
     *  奖转奖名称
     * @param $prizeIdStr  奖id字符串 ,隔开多个将
     * @param $prizeList   奖列表
     */
    public function prizeIdStrToName($prizeIdStr, $prizeList)
    {
        if (empty($prizeIdStr)) {
            return "";
        }
        $rsArr      = array();
        $prizeIdArr = explode(",", $prizeIdStr);
        foreach ($prizeList as $prize) {
            foreach ($prizeIdArr as $prizeId) {
                if ($prize['id'] == $prizeId && $prize['prize_name'] != "乌龙") {
                    array_push($rsArr, $prize['prize_name']);
                }
            }
        }
        return implode(",", $rsArr);
    }

    /**
     *  奖转奖名称
     * @param $prizeIdStr  奖id字符串 ,隔开多个将
     * @param $prizeList   奖列表
     */
    public function prizeTypeStrToName($prizeTypeStr, $prizeList)
    {
        if (empty($prizeTypeStr)) {
            return "";
        }

        $rsArr        = array();
        $prizeTypeArr = explode(",", $prizeTypeStr);
        foreach ($prizeTypeArr as $prizeId) {
            foreach ($prizeList as $prize) {
                if ($prize['rate'] == $prizeId && $prize['parent'] == 0 && $prize['prize_name'] != "乌龙") {
                    array_push($rsArr, $prize['prize_name']);
                    break;
                }
            }
        }
        return implode(",", $rsArr);
    }

    /**
     * 获取curl的路径的返回数据 post
     * @param $url
     * @param $postData
     * @param bool $jsonDecode
     * @return bool|mixed|string
     */
    public static function postCurl($url, $postData, $jsonDecode = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Server Nginx 1.1');
        $output = curl_exec($ch);
        curl_close($ch);
        if ($jsonDecode) {
            $output = json_decode($output, true);
        }
        return $output;
    }

    /**
     * 获取curl的路径的返回数据 get
     * @param $url
     * @param string $data
     * @param bool $jsonDecode
     * @return bool|mixed|string
     */
    public static function getCurl($url, $data = '', $jsonDecode = true)
    {
        if ($data) {
            $url = self::makeGetUrl($url, $data);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Eduugo Server Nginx 1.1');
        $output = curl_exec($ch);
        curl_close($ch);
        if ($jsonDecode) {
            $output = json_decode($output, true);
        }
        return $output;
    }

    /**
     *  去掉浮点数后面的0
     */
    public function clearFloatZero($arr)
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $val) {

                //因为都是字符串，这里判断有小数点和0 的字符串就是浮点数
                if (is_string($val) && strstr($val, ".") && strstr($val, "0")) {
                    $arr[$key] = floatval($val);
                }
            }
        }
        return $arr;
    }

    //毫秒转换成字符串
    public function microtimeToStr($microStime, $microEtime)
    {
        $stimeArr = explode(" ", $microStime);
        $etimeArr = explode(" ", $microEtime);
        $second   = $etimeArr[1] - $stimeArr[1];
        $micro    = substr($etimeArr[0], 2, 3) - substr($stimeArr[0], 2, 3);
        if ($micro < 0) {
            $second = $second - 1;
            $micro  = $micro + 1000;
        }
        return $second . "秒" . $micro . "毫秒";
    }

    //秒转换成时分秒
    public function secToTime($num)
    {
        $H = (int)($num / 3600);
        $i = (int)(($num - 3600 * $H) / 60);
        $s = $num - 3600 * $H - 60 * $i;
        $H = $H > 0 ? $H . "时" : "";
        $i = $i > 0 ? $i . "分" : "";
        $s = $s > 0 ? $s . "秒" : "";
        return $H . $i . $s;
    }

    public function htmlAddVersion($url, $param)
    {
        if (strstr($url, "?")) {
            //有问号代表至少有一个参数
            $url = $url . "&v=" . $param;
        } else {
            $url = $url . "?v=" . $param;
        }
        return $url;
    }

    /**
     * 检查为空 返回自定义
     * @param $data
     * @param $emptyReturn string or any 如果检查为空默认添加参数
     * @return string or any
     */
    static public function examineEmpty(&$data, $emptyReturn = '')
    {
        return self::isIssetEmpty($data) ? $emptyReturn : $data;
    }

    /**
     * 检查是否为空
     * @param $data string
     * @param $type bool false为只检查存在参数
     * @return bool
     */
    static public function isIssetEmpty(&$data, $type = true)
    {
        return $type ? !(isset($data) && !empty($data)) : !isset($data);
    }

    /**
     * 检查一个变量是否 isset  是就返回，不是就给默认值
     * @param $data
     * @param int|string $emptyReturn
     */
    public static function issetInitValue(&$data, $emptyReturn = 0)
    {
        $data = isset($data) ? $data : $emptyReturn;
    }


    /**
     * 数组转换成mysql
     * @param $fields array 字段
     * @param $type int 类型
     * @return string
     */
    public static function ArrToMysqlInstr(&$fields, $type = 1)
    {
        switch ($type) {
            case 1:
                //in
                if (empty($fields)) {
                    $mysqlStr = " * ";
                } else {
                    $mysqlStr = " ('" . implode("','", $fields) . "') ";
                }

                break;
            case 2:
                //in | field
                if (empty($fields)) {
                    $mysqlStr = " * ";
                } else {
                    $mysqlStr = implode(",", $fields);
                }
                break;
            default:
                $mysqlStr = '';
        }

        return $mysqlStr;
    }

    public static function arrayToSql(&$where, &$option = [])
    {
        $objWhere['where'] = '';
        $objWhere['value'] = '';
        $join              = ' and ';

        foreach ($where as $filed => $value) {
            if ($value === '') {
                continue;
            }
            if (isset($option['likeArr']) && in_array($filed, $option['likeArr'])) {
                $objWhere['where'] .= "$filed like :$filed{$join}";
                $value             = "%$value%";
            } else {
                $objWhere['where'] .= "$filed=:$filed{$join}";
            }

            $objWhere['value'][":{$filed}"] = $value;
        }


        if ($objWhere['where']) {
            $objWhere['where'] = substr($objWhere['where'], 0, strlen($objWhere['where']) - strlen($join));
        }
        return $objWhere;
    }


    /**
     * 设置分页
     * @param $pageNo int 页数
     * @param $pageSize int 每页条数
     * @return array
     */
    public static function page($pageNo, $pageSize)
    {

        $pageNo   = $pageNo < 1 ? 1 : $pageNo;
        $pageSize = $pageSize < 1 || $pageSize > 999999 ? 8 : $pageSize;//默认一页8条数据
        $limit    = $pageSize;
        $offset   = ($pageNo - 1) * $pageSize;
        return ['limit' => $limit, 'offset' => $offset];
    }

    /**
     * 生成二维码图片
     * @param string $value
     * @param string $fileName
     */
    public function createQrcode($value = "", $fileName = "")
    {
        $errorCorrectionLevel = 'M';//容错级别
        $matrixPoinSize       = 4;//生成图片大小
        $path                 = dirname(dirname(__FILE__)) . '/web/images/' . $fileName . '.png';
        //生成二维码图片
        QRcode::png($value, $path, $errorCorrectionLevel, $matrixPoinSize, 2);
        //$logo = 'images/logo.png';//准备好的logo图片
        //$QR = $path;//已经生成的原始二维码图
        //$QR = imagecreatefromstring(file_get_contents($QR));
        //这里是给二维码添加水印的
//        if($logo !== FALSE){
//            $QR = imagecreatefromstring(file_get_contents($QR));
//            $logo = imagecreatefromstring(file_get_contents($logo));
//            $QR_width = imagesx($QR);
//            $QR_height = imagesy($QR);
//            $logo_width = imagesx($logo);
//            $logo_height = imagesy($logo);
//            $logo_qr_width = $QR_width / 4;
//            $scale = $logo_width / $logo_qr_width;
//            $logo_qr_height = $logo_height / $scale;
//            $from_width = ($QR_width - $logo_qr_width) / 2;
//            //重新组合图片并调整大小
//            imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
//        }

        //header('Content-Type: image/png');
        //输出图片
        //imagepng($QR);
        //echo self::base64EncodeImage();
        exit;
    }


    /**
     * url设置get值
     * @param string $url
     * @param array $data
     * @return mixed
     */
    public static function urlSetValue($url, $data)
    {
        $a     = explode('?', $url);
        $url_f = $a[0];
        $query = $a[1];
        parse_str($query, $arr);
        foreach ($data as $key => $value) {
            $arr[$key] = $value;
        }
        return $url_f . '?' . http_build_query($arr);
    }

    /**
     * 判断这个时间戳是否是今天
     * @param $time
     * @return bool
     */
    public function isToday($time)
    {
        $today = time();
        $stime = strtotime(date("Y-m-d 00:00:00", $today));
        $etime = $stime + 86400;
        if ($time >= $stime && $stime <= $etime) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 递归目录查找文件
     * @param $dir        文件夹路径
     * @param $fileName   文件名称
     * @param $ClassDir   当前递归文件的路径
     * @return string
     */
    //递归目录查找文件
    public function findDir($dir, $fileName, $ClassDir)
    {
        $modelsDirs = array();
        $dirArrs    = scandir($dir);
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
     * 检查数组比另一个数组多余
     * @param $arrOne
     * @param $ArrTwo
     * @return bool
     */
    public static function examineArrSurplusArr(&$arrOne, &$ArrTwo)
    {
        return count($arrOne) != count(array_intersect($arrOne, $ArrTwo)) ? true : false;
    }

    /**
     * 判断一个一维数组里面的值是否在另外一个一维数组里面 例如 func(['pageNo','pageSize','stime','etime'], $this->get)
     * @param $smallArr array  小数组  Array([0] => maxNum,[1] => exchangeType,[2] => awardNum,[3] => prize)
     * @param $bigArr array    大数组包含所有的小数组  Array([id] => 11,[maxNum] => 100,[exchangeType] => 1,[awardNum] => 50000,[prize] => 500)
     * @return false
     */
    public static function checkParam($smallArr, $bigArr)
    {
        try {
            foreach ($smallArr as $val) {
                if (!isset($bigArr[$val])) {
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }
            }
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
        return true;
    }

    /**
     * 根据时间获取目录名
     * @return string
     */
    public static function getDirNameForDate()
    {
        $diry = date("Y");
        $dirm = date("md");
        return $diry . DIRECTORY_SEPARATOR . $dirm . DIRECTORY_SEPARATOR;
    }

    //创建目录
    public static function createFolders($dir)
    {
        return is_dir($dir) or mkdir($dir, 0777, true);
    }


    /**
     *  这个时间距离今天范围内是 天还是周还是月
     * @param $stime  筛选条件的开始时间 时间戳
     * @return string
     */
    public function DayWeekMonth($stime)
    {
        $now = time();
        $rs  = $now - $stime;
        if ($rs <= 24 * 60 * 60) {
            return "day";
        } elseif (24 * 60 * 60 < $rs && $rs <= 7 * 24 * 60 * 60) {
            return "week";
        } else {
            return "month";
        }
    }


    /**
     * 验证base64上传图片
     * @param $fileString
     * @param $size integer kb
     * @param $extendArr
     * @return array
     */
    public static function &checkImageBase64($fileString, $size, $extendArr)
    {
        try {
            if (!preg_match('/^(data:\s*image\/(\w+);base64,)/', $fileString, $result)
                || !isset($result[2])
                || !in_array($result[2], $extendArr)
            ) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $src      = str_replace($result[1], '', $fileString);
            $imageStr = base64_decode($src);
            $imgLen   = strlen($imageStr);
            $fileSize = ($imgLen - ($imgLen / 8) * 2);
            if (($fileSize / 1024) > $size * 1024) {
                throw new MyException(ErrorCode::ERROR_PARAM);
            }
            $return = ['imageStr' => $imageStr, 'result' => $result];
            return $return;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }

    /**
     * 生成文件随机名字
     * @param $time
     * @return string
     */
    public static function createFileName($time)
    {
        return $time . '_' . rand(10000, 99999);
    }


    /**
     * 生成get方式的url
     * @param $url
     * @param $data
     * @return string
     */
    public static function makeGetUrl($url, $data)
    {
        if (empty($data)) {
            return $url;
        }
        $make_url = $url;
        $find     = strpos($url, "?");
        if ($find == false) {
            $make_url .= "?";
        }
        if (is_array($data)) {
            foreach ($data as $key => $va) {
                $make_url .= "&";
                $make_url .= $key;
                $make_url .= "=";
                $make_url .= $va;
            }
        } else {
            $make_url .= "&" . $data;
        }

        $make_url = str_replace("?&", "?", $make_url);
        return $make_url;
    }


    //获取毫秒
    public static function getCurrentTimeInMilliseconds()
    {
        return round(microtime(true) * 1000);
    }


    /**
     *  送钻报表专用初始化数组
     * @param $arr
     */
    public static function initDiamondArr(&$arr)
    {
        $arr['diamond_one']   = isset($arr['diamond_one']) ? $arr['diamond_one'] : 0;
        $arr['diamond_two']   = isset($arr['diamond_two']) ? $arr['diamond_two'] : 0;
        $arr['diamond_three'] = isset($arr['diamond_three']) ? $arr['diamond_three'] : 0;
        $arr['diamond_four']  = isset($arr['diamond_four']) ? $arr['diamond_four'] : 0;
        $arr['diamond_five']  = isset($arr['diamond_five']) ? $arr['diamond_five'] : 0;

        $arr['diamond_one_count']   = isset($arr['diamond_one_count']) ? $arr['diamond_one_count'] : 0;
        $arr['diamond_two_count']   = isset($arr['diamond_two_count']) ? $arr['diamond_two_count'] : 0;
        $arr['diamond_three_count'] = isset($arr['diamond_three_count']) ? $arr['diamond_three_count'] : 0;
        $arr['diamond_four_count']  = isset($arr['diamond_four_count']) ? $arr['diamond_four_count'] : 0;
        $arr['diamond_five_count']  = isset($arr['diamond_five_count']) ? $arr['diamond_five_count'] : 0;
    }

    /**
     *  一个字符串是否包含另外一个字符串
     * @param $longStr
     * @param $shortStr
     * @return bool  true 包含  false 不包含
     */
    public static function strHasStr($longStr, $shortStr)
    {
        if (strpos($longStr, $shortStr) !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *  获取房间id
     * @param $DataRoomInfoListObj
     * @param $seo_machine_id
     * @return int
     */
    public static function getRoomIndex($DataRoomInfoListObj, $seo_machine_id)
    {
        foreach ($DataRoomInfoListObj as $val) {
            if (self::strHasStr($seo_machine_id, $val['seo_machine_id'])) {
                return $val['room_index'];
            }
        }
        return 0;
    }

    /**
     * 常用时间格式
     * @param $time
     * @return false|string
     */
    public static function dateUsually(&$time)
    {
        return date(self::DATE_USUALLY_FORMAT, $time);
    }

    /**
     * 通过gameType获取对应的 对象地址
     * @param $gameType
     * @param $alias
     * @return mixed
     */
    public static function getClassPathForGameType($gameType, $alias)
    {
        $chineseGameName = Yii::$app->params['gameType'][$gameType];
        return Yii::$app->params[$chineseGameName][$alias];
    }

    /**
     * 通过gameName获取对应的游戏id
     * @param $gameName
     * @return mixed
     */
    public static function getGameTypeForGameName($gameName)
    {
        $chineseGameName = Yii::$app->params['game'][$gameName];
        $gameType        = Yii::$app->params[$chineseGameName]['gameType'];
        return $gameType;
    }

    /**
     * json合并
     * @param $fromJson
     * @param $prizeJson
     * @return false|string
     */
    public static function mergePrizeJson(&$fromJson, &$prizeJson)
    {
        $rsArr = array();

        if (!empty($fromJson)) {
            $fromArr = json_decode($fromJson, true);
            foreach ($fromArr as $key => $val) {
                $rsArr[$key] = isset($rsArr[$key]) ? $rsArr[$key] : 0;
                $rsArr[$key] += $val;
            }
        }
        if (!empty($prizeJson)) {
            $prizeJsonArr = json_decode($prizeJson, true);
            foreach ($prizeJsonArr as $key => $val) {
                $rsArr[$key] = isset($rsArr[$key]) ? $rsArr[$key] : 0;
                $rsArr[$key] += $val;
            }
        }
        return json_encode($rsArr);
    }


    //增加报错处理函数
    public static function exception($errorCode)
    {
        try {
            throw new MyException($errorCode);
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }


    //处理前端时间传递 开始时间
    public static function startTimeHandler(&$startTime, &$time)
    {
        return isset($startTime) ? (
        strstr($startTime, ':') ?
            ($startTime) : $startTime
        ) : date(self::DATE_USUALLY_FORMAT, $time);
    }

    //处理前端时间传递 结束时间
    public static function endTimeHandler(&$endTime, &$time)
    {
        return isset($endTime) ? (
        strstr($endTime, ':') ?
            ($endTime) : ($endTime . " 23:59:59")
        ) : date('Y-m-d 23:59:00', $time);
    }

    //统一json转换
    public static function json_encode($data)
    {
        return json_encode($data, JSON_NUMERIC_CHECK);
    }

    //验证分钟秒格式是否正确
    public static function verifyMS($time)
    {
        $time = explode(':', $time);

        if (
            !isset($time[0])
            || !isset($time[1])
            || !is_numeric($time[0])
            || !is_numeric($time[1])
        ) {
            return false;
        }

        if (
            $time[0] < 0
            || $time[0] > 24
            || $time[1] < 0
            || $time[1] > 60
        ) {
            return false;
        }
        return true;
    }

    //验证数字
    public static function verifyJsonNum($data)
    {
        if (
            !isset($data[0])
            || !isset($data[1])
            || !is_numeric($data[0])
            || !is_numeric($data[1])
            || $data[0] < 0
            || $data[1] > 100000000
        ) {
            return false;
        }
        return true;
    }

    //隐藏用户名
    public function hideName($name)
    {
        return "***" . mb_substr($name, -4, 4, 'utf-8');
    }

    //判断0或偶数
    public static function isEven(&$data)
    {
        if (is_numeric($data) && $data % 2 == 0) {
            return true;
        }
        return false;
    }

    public static function encryptByPublicKey($data)
    {
        $data = json_encode($data);
        $rsa = new Rsa();
        $sign = $rsa->encryptByPublicKey($data);
        return ['data'=>urlencode($data),'sign'=>$sign];
    }

    /**
     * 游戏 简称  在某种情况下使用的真是的简称
     * @param $gameName
     * @return string
     */
    public static function gameNameToRealName($gameName)
    {
        switch ($gameName){
            case "ATT2":
                    return "ATT";
            case "HFHH":
                return "H5H";
            default:
                return $gameName;
        }
    }

    /**
     * 分析两个数组相同字段不同的值 并且不允许 $updateArr有的字段$origin没有
     * @param $updateArr 修改后的数组
     * @param $origin    修改前的数组
     * @param $arrName   每个key对应中文名字
     * @param string $append    附加显示信息
     * @return array
     */
    public static function distinctArr($updateArr,$origin,$arrName,$append="")
    {
        try {
            $appendSign = " :";
            $rs = array();
            foreach ($updateArr as $key=>$val){
                if( !isset($origin[$key])){
                    throw new MyException(ErrorCode::ERROR_PARAM);
                }

                if( $origin[$key]!= $val ){
                    $column = isset($arrName[$key]) ? $arrName[$key] : $key;
                    $updateInfo = $column.":".$origin[$key]."=>".$val;
                    if(!empty($append)){
                        $updateInfo .= $appendSign.$append;
                    }
                    array_push($rs,$updateInfo);
                }
            }
            return $rs;
        } catch (MyException $e) {
            echo $e->toJson($e->getMessage());
        }
    }
}