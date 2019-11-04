<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/4
 * Time: 13:58
 */

namespace app\common\typeCode\history;


use app\common\typeCode\impl\HistoryImpl;

class VideoComment implements HistoryImpl
{
    private $type = 7;

    public function getType(): int
    {
        return $this->type;
    }
}