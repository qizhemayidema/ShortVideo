<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/31
 * Time: 15:46
 */

namespace app\common\typeCode\message;

use app\common\typeCode\impl\MessageImpl;

class PrivateMessage implements MessageImpl
{
    private $type = 3;

    public function getType(): int
    {
        return $this->type;

    }
}