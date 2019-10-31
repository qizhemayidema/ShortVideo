<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/31
 * Time: 10:28
 */

namespace app\common\typeCode\history;

use app\common\typeCode\impl\HistoryImpl;

class VideoLike implements HistoryImpl
{
    private $type = 1;

    public function getType(): int
    {
       return $this->type;
    }
}