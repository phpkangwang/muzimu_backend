<?php
Yii::setAlias('backend_models', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'models');
Yii::setAlias('common_models', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'models');
Yii::setAlias('common_services', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'services');
Yii::setAlias('myLog', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'logs');
Yii::setAlias('platFormList', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'platform');
Yii::setAlias('imageBaseDir', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR.'backend'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'images');