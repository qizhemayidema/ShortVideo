<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/30
 * Time: 15:06
 */

namespace app\common\typeCode\cate\complain\impl;

//类型接口
interface TypeImpl
{
    //获取
    public function getType() : int;
}