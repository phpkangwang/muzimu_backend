<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/3/003
 * Time: 16:09
 */

namespace common\services;


class ToolService
{
    /**
     * 全格式打印
     * @param $data
     */
    public static function varDump($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    public static function send_post($url, $post_data)
    {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

    public static function send_get($url, $post_data)
    {
        try {
            $postdata = http_build_query($post_data);
            $options = array(
                'http' => array(
                    'method' => 'GET',
                    'header' => 'Content-type:application/x-www-form-urlencoded;charset=utf-8',
                    'content' => $postdata,
                    'timeout' => 3, // 超时时间（单位:s）
                    'charset' => 'utf-8',
                )
            );
            $context = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            return $result;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * 调用接口
     * @param $url
     * @return mixed
     */
    public static function curl_post($url,$data)
    {
        $curl = curl_init();
             //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,"POST");
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        //执行命令
        $contents = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据

        $a = strpos($contents,'{');
        $b = strpos($contents,'}');
        $contents = substr($contents,$a,$b+1-$a);
        return $contents;
    }

    /**
     * 调用接口
     * @param $url
     * @return mixed
     */
    public static function curl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $contents = curl_exec($ch);
        curl_close($ch);
        return $contents;
    }

    /**
     * PDO查询数据
     * @param $db 连接的数据库名
     * @param $sql sql语句
     * @param $params 参数
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public static function SelectPDO($db,$sql,$params,$asArray=false)
    {
        $connection = \Yii::$app->get($db);
        $command = $connection->createCommand($sql, $params);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * 驼峰装换成下划线
     * @param $camelCaps
     * @param string $separator
     * @return string
     */
    public static function uncamelize($camelCaps,$separator='_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    /**
     * 下划线装换成驼峰
     * @param $uncamelized_words
     * @param string $separator
     * @return string
     */
    public static function camelize($uncamelized_words,$separator='_')
    {
        $uncamelized_words = $separator. str_replace($separator, " ", strtolower($uncamelized_words));
        return ltrim(str_replace(" ", "", ucwords($uncamelized_words)), $separator );
    }

    public static function UsernameHidden($username)
    {
        $username = substr($username,-4);
        return '***'.$username;
    }

    public static function encryptByPublicKey($data)
    {
        $data = json_encode($data);
        $rsa = new Rsa();
        $sign = $rsa->encryptByPublicKey($data);
        return ['data'=>urlencode($data),'sign'=>$sign];
    }

    public static function getIpAddress($client_ip)
    {
        $model = new IpSearch(__DIR__.'/../../backend/web/IpAddress/qqzeng-ip-utf8.dat');
        $area_info = $model->get($client_ip);
        list($continent,$country,$province, $city, $district,$isp,$areacode,$en,$cc,$lng,$lat) = explode("|", $area_info);

        $ip =array(
            'ip'=>$client_ip,
            'continent'=>$continent,
            'country'=>$country,
            'province'=>$province,
            'city'=>$city,
            'district'=>$district,
            'isp'=>$isp,
            'areacode'=>$areacode,
            'en'=>$en,
            'cc'=>$cc,
            'lng'=>$lng,
            'lat'=>$lat
        );
        return $ip;
    }

    public static function GetIP($ip)
    {

        try {
            $address = null;
            $model = \common\models\Ip::find()->where("INET_ATON('{$ip}') BETWEEN ip_start_num AND ip_end_num")->one();
            if (!empty($model) && !empty($model->city)) {
                $address = $model->city . ' ' . str_replace('/基站WiFi', '', $model->isp);
            }
            $result = \common\services\ToolService::send_get("http://ip.taobao.com/service/getIpInfo.php?ip={$ip}", []);
            if ($result !== false) {
                $result = json_decode($result);
                $city = $result->data->city == "XX" ? '' : $result->data->city;
                $isp = $result->data->isp == "XX" ? '' : $result->data->isp;
                $county = $result->data->county == "XX" ? '' : $result->data->county;
                if(!empty($model)) {
                    $model->city = empty($model->city) ? $city : $model->city;
                    $model->isp = empty($model->isp) ? $isp : $model->isp;
                    $model->district = empty($model->district) ? $county : $model->district;
                    $model->validate() && $model->save();
                }
                $address = ($result->data->country!='中国'?$result->data->country:'').$city . ' ' . $isp;
            }
            $result = \common\services\ToolService::send_get("http://ip.ws.126.net/ipquery?ip={$ip}", []);
            if ($result !== false) {
                $result = iconv('gb2312', 'utf-8//IGNORE', $result);
                $start = strpos($result, '{');
                $end = strpos($result, '}');
                $result = substr($result, $start + 1, $end - $start - 1);
                $result = substr($result, $start + 1, $end - $start - 1);
                $arr = explode(',', $result);
                if (isset(explode(':', $arr[0])[1])) {
                    $str = explode(':', $arr[0])[1];
                    $city = str_replace('"', '', $str);
                    if(!empty($model)) {
                        $model->city = empty($model->city) ? $city : $model->city;
                        $model->validate() && $model->save();
                    }
                    $address = $city . ' ' . str_replace('/基站WiFi', '', empty($model)?'':$model->isp);
                }
            }
            $result = \common\services\ToolService::send_get("http://api.ip138.com/query/?ip={$ip}&datatype=jsonp&callback=&token=f9fde6fd09a0f55028ad22d251f70cfb", []);
            if ($result !== false) {
                $result = json_decode($result);
                if ($result->ret == 'ok') {
                    if(!empty($model)) {
                        $model->city = empty($model->city) ? $result->data[2] : $model->city;
                        $model->isp = empty($model->isp) ? $result->data[3] : $model->isp;
                        $model->validate() && $model->save();
                    }
                    $address = ($result->data[0]!='中国'?$result->data[0]:'').$result->data[2] . ' ' . $result->data[3];
                }
            }
            return $address;
        }catch (\Exception $e){
            var_dump($model);
        }

    }

    /**
     * 获取ip
     * @return mixed
     */
    public static function getIp1()
    {
        //return Yii::$app->getRequest()->getUserIP();
        $onlineip = 'Unknown';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ips = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
            $real_ip = $ips['0'];
            if ($_SERVER['HTTP_X_FORWARDED_FOR'] && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $real_ip))
            {
                $onlineip = $real_ip;
            }
            elseif ($_SERVER['HTTP_CLIENT_IP'] && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP']))
            {
                $onlineip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
        if ($onlineip == 'Unknown' && isset($_SERVER['HTTP_CDN_SRC_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CDN_SRC_IP']))
        {
            $onlineip = $_SERVER['HTTP_CDN_SRC_IP'];
            $c_agentip = 0;
        }
        if ($onlineip == 'Unknown' && isset($_SERVER['HTTP_NS_IP']) && preg_match ( '/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER ['HTTP_NS_IP'] ))
        {
            $onlineip = $_SERVER ['HTTP_NS_IP'];
            $c_agentip = 0;
        }
        if ($onlineip == 'Unknown' && isset($_SERVER['REMOTE_ADDR']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['REMOTE_ADDR']))
        {
            $onlineip = $_SERVER['REMOTE_ADDR'];
            $c_agentip = 0;
        }
        return $onlineip;
    }
}