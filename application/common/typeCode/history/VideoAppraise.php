<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/5
 * Time: 14:09
 */

namespace app\common\typeCode\history;


use app\common\typeCode\impl\HistoryImpl;

class VideoAppraise implements HistoryImpl
{
    private $type = 3;

    public function getType(): int
    {
        // TODO: Implement getType() method.

        return $this->type;
    }
}