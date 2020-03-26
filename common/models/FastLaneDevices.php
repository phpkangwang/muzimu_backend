<?php

namespace common\models;

use backend\models\BaseModel;
use backend\models\Tool;
use Yii;

class FastLaneDevices extends BaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fastlane_devices';
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
//            [['code', 'menu_name', 'module_id', 'entry_url', 'action', 'controller'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
//            'id' => '主键',
        ];
    }

    //获取路径
    public static function getBasePath()
    {

        $fastLanePath = Yii::getAlias('@fastLaneDir');

        $AppFilePath     = $fastLanePath . '/' . 'Appfile';
        $FastFilePath    = $fastLanePath . '/' . 'Fastfile';
        $devicesFilePath = $fastLanePath . '/' . 'devices';

        return [
            'AppFilePath'     => $AppFilePath,
            'FastFilePath'    => $FastFilePath,
            'devicesFilePath' => $devicesFilePath,
        ];

    }

    //更新配置文件 更新完之后服务器要用fastlane Spaceship 登陆获取6位code
    public static function upConfigFile($appId, $password, $appIdentifier, $teamId)
    {
        $path = self::getBasePath();

        $AppFilePath     = $path['AppFilePath'];
        $FastFilePath    = $path['FastFilePath'];
        $devicesFilePath = $path['devicesFilePath'];

        $file = file_get_contents($AppFilePath);

        $str = $file;

        $pattern     = '/app_identifier ".*" # app的bundle identifier/';
        $replacement = 'app_identifier "' . $appIdentifier . '" # app的bundle identifier';
        $str         = preg_replace($pattern, $replacement, $str);

        $pattern     = '/apple_id ".*" # 你的Apple ID/';
        $replacement = 'apple_id "' . $appId . '" # 你的Apple ID';
        $str         = preg_replace($pattern, $replacement, $str);

        $pattern     = '/team_id ".*" # Team ID/';
        $replacement = 'team_id "' . $teamId . '" # Team ID';
        $str         = preg_replace($pattern, $replacement, $str);

        $str = preg_replace($pattern, $replacement, $str);

        $file = fopen($AppFilePath, 'w+') or die("Unable to open file!");
        fwrite($file, $str);
        fclose($file);


        $file        = file_get_contents($FastFilePath);
        $str2        = $file;
        $pattern     = '/ENV\["FASTLANE_PASSWORD"\]=".*" # 密码/';
        $replacement = 'ENV["FASTLANE_PASSWORD"]="' . $password . '" # 密码';
        $str2        = preg_replace($pattern, $replacement, $str2);

        $file = fopen($FastFilePath, 'w+') or die("Unable to open file!");
        fwrite($file, $str2);
        fclose($file);


        //每次开启都奖设备全写入
        $data = self::find()->where(
            "app_id='$appId' and type=1"
        )->asArray()->all();
        if (!empty($data)) {

            $str = 'Device ID	Device Name';

            foreach ($data as $value) {
                $str .= "
{$value['udid']}	Device{$value['id']}";
            }

            $file = fopen($devicesFilePath, 'w+') or die("Unable to open file!");
            fwrite($file, $str);
            fclose($file);

        }
    }

    //清除
    public static function clearDevicesFile()
    {
        $path = self::getBasePath();
//        $AppFilePath     = $path['AppFilePath'];
//        $FastFilePath    = $path['FastFilePath'];
        $devicesFilePath = $path['devicesFilePath'];
        $str             = '';
        $file = fopen($devicesFilePath, 'w+') or die("Unable to open file!");
        fwrite($file, $str);
        fclose($file);
    }


}
