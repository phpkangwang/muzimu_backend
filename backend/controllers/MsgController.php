<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/18
 * Time: 19:29
 */

namespace backend\controllers;

use backend\models\AdminUser;
use backend\models\CollentionAli;
use backend\models\CollentionBank;
use backend\models\CollentionWx;
use common\models\CollectAccount;
use common\models\DataGameListInfo;
use common\models\ExperienceReport;
use common\models\game\DataFunctionSwitch;
use common\models\game\DataServerLists;
use common\models\game\FivepkAccount;
use common\models\game\FivepkDefaultFivepk;
use common\models\game\FivepkDiamond;
use common\models\game\FivepkPlayerInfo;
use common\models\game\I18nVersion;
use common\models\UploadForm;
use common\models\WhiteIpLists;
use common\services\CaptchasService;
use common\services\ToolService;
use yii\base\Controller;
use common\utils\CommonFun;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class MsgController extends Controller
{
    public $modelClass = 'common\services\Messenger';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    /**
     * 客户端获取信息
        * @return string
            */
    public function actionNotice()
    {

        $ip = CommonFun::getClientIp() ;

        $white_ip_lists = ArrayHelper::map(WhiteIpLists::find()->all(),'id','white_ip');
        $notice = DataGameListInfo::find()->select('php_server_port,game_version,game_json,game_switch,game_notice,game_res_url,game_white_ip,game_server_port,game_server_ip,game_server_ip2,coin,score,game_version_id,skip_update,skip_game_update')->filterWhere(['game_number'=>0])->asArray()->one();
        $language = I18nVersion::find()->filterWhere(['id_parent'=>0])->all();
        $languages = [];
        foreach ($language as $value){
            if(empty($value->children)){
                continue;
            }
            $languages[$value->name] = ArrayHelper::map($value->children,'name','version');
        }
        $notice['server_lists'] = ArrayHelper::map(DataServerLists::find()->all(),'server_name','server_ip');
        $notice['languages'] = $languages;
        if(in_array($ip,$white_ip_lists)){
            $notice['game_switch'] = 0;
        }else{
            $ipregexp = implode('|',str_replace(array('*','.'),array('\d+','\.'),$white_ip_lists));
            $rs = preg_match("/^(".$ipregexp.")$/",$ip);
            if($rs){
                $notice['game_switch'] = 0;
            }
        }
        if($function = DataFunctionSwitch::findOne(['comments'=>'交易开关'])){
            $notice['function_switch'] = $function->switchs;
        }

        return json_encode($notice,JSON_UNESCAPED_UNICODE);
    }

    public function actionPay()
    {
        try {

            $accountId = \Yii::$app->request->get('accountId','');
            $signture = \Yii::$app->request->get('signture','');
            $signtureData = \Yii::$app->request->get('signtureData','');
            if (empty($accountId) || empty($signtureData) || empty($signture)) {
                return '传递参数不正确';
            }
//            dt([$accountId,$signtureData,$signture]);
            \Yii::warning('pay:'.json_encode([$accountId,$signtureData,$signture]),'msg');
            $accountId = urlencode($accountId);
            $signtureData = urlencode($signtureData);
            $signture = urlencode($signture);
            $url = \Yii::$app->params['game_url'] . "/pay/google?accountId={$accountId}&signture={$signture}&signtureData={$signtureData}";
            $content = ToolService::curl($url);
            \Yii::warning('return:'.$content,'msg');
            return $content;
        }catch (\Exception $e){
            \Yii::error("Pay---------->".$e->getMessage(),'msg');
            return 'Failed';
        }
    }

    public function actionMailReport()
    {
        try {
            $accountId    = \Yii::$app->request->get('accountId','');
            $type         = \Yii::$app->request->get('type','');
            $title        = \Yii::$app->request->get('title','');
            $emailAddress = \Yii::$app->request->get('emailAddress','');
            $content      = \Yii::$app->request->get('content','');
            if (empty($accountId) || empty($type) || empty($title) || empty($emailAddress) || empty($content)) {
                return '传递参数不正确';
            }
            \Yii::warning(json_encode([$accountId, $type, $title, $emailAddress, $content]), 'warning');
            $accountId    = urlencode($accountId);
            $type         = urlencode($type);
            $title        = urlencode($title);
            $emailAddress = urlencode($emailAddress);
            $content      = urlencode($content);
            $url          = \Yii::$app->params['game_url'] . "/mailReport?accountId={$accountId}&type={$type}&title={$title}&emailAddress={$emailAddress}&content={$content}";
            $content      = json_decode(ToolService::curl($url));
            if (!empty($content) && $content->status == 10) {
                $msg = 'Success';
            } else {
                $msg = 'Error';
            }
            return $msg;
        }catch (\Exception $e){
            \Yii::error($e->getMessage(),'error');
            return 'Failed';
        }
    }

    public function actionSetPicUrl()
    {
        $accountId = \Yii::$app->request->get('accountId','');
        $pic = \Yii::$app->request->get('pic','');

        if(empty($accountId) || empty($pic)){
            return '传递参数不正确';
        }

        \Yii::warning(json_encode([$accountId,$pic]),'warning');

        $accountId = urlencode($accountId);
        $pic       = urlencode($pic);
        $url = \Yii::$app->params['url']."/setPicUrl?accountId={$accountId}&pic={$pic}";
        $content = json_decode(ToolService::curl($url));
        if(!empty($content) && $content->status == 10){
            $msg = 'Success';
        }else{
            $msg = 'Error';
        }
        return $msg;
    }

    public function actionApple()
    {
        try{
            $accountId = \Yii::$app->request->get('accountId','');
            $receipt = \Yii::$app->request->get('receipt','');
            $chooseEnv = \Yii::$app->request->get('chooseEnv','');
            $innerOrderId = \Yii::$app->request->get('innerOrderId','');
            if(empty($accountId) || empty($receipt) || $chooseEnv=='' || empty($innerOrderId)){
                return '传递参数不正确';
            }
            \Yii::warning(json_encode($_GET),'warning');
            $accountId    = urlencode($accountId);
            $receipt      = urlencode($receipt);
            $chooseEnv    = urlencode($chooseEnv);
            $innerOrderId    = urlencode($innerOrderId);
            $url = \Yii::$app->params['game_url']."/pay/apple?accountId={$accountId}&receipt={$receipt}&chooseEnv={$chooseEnv}&innerOrderId={$innerOrderId}";
            \Yii::warning($url,'url');
            $content = ToolService::curl($url);
            return $content;
        }catch (\Exception $e){
            \Yii::error($e->getMessage(),'error');
            return 'Failed';
        }
    }

    public function actionLog()
    {
        try {
            $msg = \Yii::$app->request->get('msg');
            $time = date('YmdH');
            $dir = dirname(__FILE__) . '/../runtime/client_logs';
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $path = $dir . '/' . $time . '.log';
            if (file_put_contents($path, $msg . "\r\n", FILE_APPEND) <= 0) {
                throw new Exception('错误');
            }
            return "Success";
        }catch (\Exception $exception){
            \Yii::error($exception->getMessage(),'error');
            return "Failed";
        }
    }

    public function actionAccountIp()
    {
        $account_id = \Yii::$app->request->get('account_id','');
        if(empty($account_id)){
            return '传递参数不正确';
        }
        \Yii::info('玩家IP'.$account_id,'msg');
        $model = FivepkAccount::find()->filterWhere(['account_id'=>$account_id])->one();
        if(!empty($model)) {
            $ip = ToolService::getIp1();
            $model->account_ip = $ip;
            if($model->validate() && $model->save()){
                echo 'success';
            }
        }
    }

}