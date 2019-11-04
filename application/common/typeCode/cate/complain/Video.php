<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/30
 * Time: 15:11
 */

namespace app\common\typeCode\cate\complain;

use app\common\typeCode\cate\complain\impl\TypeImpl;

use app\common\typeCode\impl\CateImpl;

class Video implements CateImpl,TypeImpl
{
    private $type = 1;  //举报本身的类型

    private $cateType = 2;

    private $levelType = 'one';

    private $cateCacheName = "complain_video_type";


    public function getType(): int
    {
        return $this->type;
    }


    public function getCateType() : int
    {

        return $this->cateType;
    }

    public function getCateCacheName(): string
    {

        return $this->cateCacheName;
    }

    public function getLevelType(): string
    {
        // TODO: Implement getLevelType() method.

        return $this->levelType;
    }
}