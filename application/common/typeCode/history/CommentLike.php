<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/31
 * Time: 11:12
 */

namespace app\common\typeCode\history;


use app\common\typeCode\impl\HistoryImpl;

class CommentLike implements HistoryImpl
{
    private $type = 2;

    public function getType(): int
    {
        return $this->type;
    }
}