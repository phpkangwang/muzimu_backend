<?php


namespace common\models;

use backend\models\ErrorCode;
use backend\models\MyException;
use backend\models\Tool;
use Yii;
use yii\web\UploadedFile;
use yii\base\Model;

class UploadedFileRealize extends Model
{
    public $imageFile;

    public function rules()
    {
        return [
            [
                [
                    ['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png,jpg,gif,jpeg', 'maxSize' => 1024 * 1024
                ]
            ]
        ];
    }

    /**
     * @return bool
     */
    public function upload()
    {
        if ($this->validate()) {
            $this->imageFile = UploadedFile::getInstance('QRCode');
            $dir = Yii::getAlias('imageBaseDir') . Tool::getDirNameForDate();
            $name = time() . rand(10000, 99999) . '.' . $this->imageFile->extension;
            $this->imageFile->saveAs($dir . $name);
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param $loginId
     * @param $time
     * @return string
     * @throws MyException
     */
    public function QRCodeUploadedFile($loginId,$time)
    {
        //用图片流来上传图片
        $src = yii::$app->request->post('QRCode');
        $src = strstr($src, ",");
        $src = substr($src, 1);
        $src = base64_decode($src);
        $baseDir=$loginId . DIRECTORY_SEPARATOR;//文件目录下的分类文件夹
        $dir = Yii::getAlias('@imageBaseDir') . DIRECTORY_SEPARATOR .$baseDir ;
        //创建保存目录
        if (!Tool::createFolders($dir)) {
            throw new MyException(ErrorCode::ERROR_NOT_CREATE_DIR);
        }
        $imgUlr = $time .'_'. rand(10000, 99999). ".jpg";
        $url = $dir . $imgUlr ;
        //创建图片文件
        $myfile = fopen($url, "w");
        if (!$myfile) {
            throw new MyException(ErrorCode::ERROR_NOT_CREATE_FILE);
        }
        fwrite($myfile, $src);
        fclose($myfile);
        return $baseDir.$imgUlr;

    }



}