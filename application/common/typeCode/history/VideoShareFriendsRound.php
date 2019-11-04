<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/4
 * Time: 13:12
 */

namespace app\common\typeCode\history;


use app\common\typeCode\impl\HistoryImpl;

class VideoShareFriendsRound implements HistoryImpl
{
    private $type = 6;

    public function getType(): int
    {
        return $this->type;
    }
}