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
}