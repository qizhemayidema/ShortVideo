<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/4
 * Time: 14:06
 */

namespace app\common\typeCode\history;


use app\common\typeCode\impl\HistoryImpl;

class FocusUser implements HistoryImpl
{
    private $type = 8;

    public function getType(): int
    {
        // TODO: Implement getType() method.

        return $this->type;
    }

}