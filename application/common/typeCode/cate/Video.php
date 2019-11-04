<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/29
 * Time: 13:23
 */
namespace app\common\typeCode\cate;

use app\common\typeCode\impl\CateImpl;


class Video implements CateImpl
{
    private $cateType = 1;

    private $cateCacheName = "video_cate";

    private $levelType = 'more';

    public function getCateType() : int
    {
        // TODO: Implement getCateType() method.

        return $this->cateType;
    }

    public function getCateCacheName(): string
    {
        // TODO: Implement getCateCacheName() method.

        return $this->cateCacheName;
    }

    public function getLevelType(): string
    {
        // TODO: Implement getLevelType() method.

        return $this->levelType;
    }
}