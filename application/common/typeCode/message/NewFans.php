<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/31
 * Time: 13:33
 */

namespace app\common\typeCode\message;


use app\common\typeCode\impl\MessageImpl;

//新粉
class NewFans implements MessageImpl
{
    private $type = 4;

    public function getType(): int
    {
        return $this->type;
    }
}