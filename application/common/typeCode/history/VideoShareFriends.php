<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/4
 * Time: 13:10
 */

namespace app\common\typeCode\history;


use app\common\typeCode\impl\HistoryImpl;

class VideoShareFriends implements HistoryImpl
{
    private $type = 5;

    public function getType(): int
    {
        return $this->type;
    }
}