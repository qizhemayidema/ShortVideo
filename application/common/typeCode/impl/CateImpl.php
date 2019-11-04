<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/29
 * Time: 13:30
 */
namespace app\common\typeCode\impl;

interface CateImpl
{
    public function getCateType() :int;     //获取分类类型

    public function getCateCacheName() :string ;    //获取分类缓存名

    public function getLevelType(): string;     //获取层级类型 one | more 两个值

}