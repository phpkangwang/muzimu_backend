<?php
namespace backend\controllers\platform;


/**
 *  特质类
 */
trait PlatformTrait
{
    use PlatformPublicTrait;

    public function __call($name, $arguments)
    {
        return '';
    }

}
