<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/1
 * Time: 17:09
 */

namespace app\common\typeCode\history;


use app\common\typeCode\impl\HistoryImpl;

class VideoCollect implements HistoryImpl
{
    private $type = 4;

    public function getType(): int
    {
        // TODO: Implement getType() method.

        return $this->type;
    }

}