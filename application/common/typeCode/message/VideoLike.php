<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/31
 * Time: 10:55
 */

namespace app\common\typeCode\message;


use app\common\typeCode\impl\MessageImpl;

class VideoLike implements MessageImpl
{
    private $type = 1;

    public function getType(): int
    {
        return $this->type;
    }
}