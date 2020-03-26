<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-9-3
 * Time: 13:39
 */

namespace common\models;


use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $uploadFile;

    public function rules()
    {
        return [['uploadFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'text,png,jpg'];
    }

    public function upload()
    {
        if ($this->validate()) {
            $this->uploadFile->saveAs(__DIR__.'/upload/' . $this->uploadFile->baseName . '.' . $this->uploadFile->extension);
            return true;
        } else {
            return false;
        }
    }
}