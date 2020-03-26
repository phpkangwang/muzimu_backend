<?php

/*原型模式 （Prototype）       start*/

namespace backend\models;

interface PrototypeInterface
{
    public function shallowCopy();

    public function deepCopy();
}
