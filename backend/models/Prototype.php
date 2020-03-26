<?php

/*原型模式 （Prototype）       start*/

namespace backend\models;

class Prototype implements PrototypeInterface
{

    //浅复制：复制了对象的变量，对引用的对象不做复制，直接进行的内存地址引用。
    //深复制：复制了对象的变量和引用，对引用的对象也做复制，被引用的改变不会影响当前的值。

    private $Source;

    public function __construct($Source)
    {
        $this->Source = $Source;
    }

    /**
     * 浅复制
     */
    public function shallowCopy()
    {
        return clone $this->Source;
    }

    /**
     * 深复制
     */
    public function deepCopy()
    {
        //序列化 变成字节流 相当于做了一次深复制 对引用的值也做了复制 引用的值将会变成不同的内存地址
        $serializeObj = serialize($this->Source);
        $cloneObj     = unserialize($serializeObj);
        return $cloneObj;
    }

}