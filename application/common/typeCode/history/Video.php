<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/4
 * Time: 15:07
 */

namespace app\common\typeCode\history;


use app\common\typeCode\impl\HistoryImpl;

class Video implements HistoryImpl
{
    private $type = 9;

    public function getType(): int
    {
        // TODO: Implement getType() method.

        return $this->type;
    }

}