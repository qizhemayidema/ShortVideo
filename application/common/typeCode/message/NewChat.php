<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/12
 * Time: 14:02
 */

namespace app\common\typeCode\message;


use app\common\typeCode\impl\MessageImpl;

class NewChat implements MessageImpl
{
    private $type = 3;

    public function getType(): int
    {
        // TODO: Implement getType() method.
        return $this->type;
    }
}