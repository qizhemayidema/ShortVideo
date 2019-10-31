<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/29
 * Time: 18:29
 */
namespace app\common\typeCode\message;

use app\common\typeCode\impl\MessageImpl;

class VideoComment implements MessageImpl
{
    private $type = 2;

    public function getType(): int
    {
        // TODO: Implement getType() method.
        return $this->type;
    }
}