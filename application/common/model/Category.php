<?php

namespace app\common\model;

use think\Cache;
use think\Model;

class Category extends Model
{
    public function getList(\app\common\typeCode\impl\CateImpl $obj)
    {
        $cateType = $obj->getCateType();

        $cacheName = $obj->getCateCacheName();

        $cache = new Cache(['type'=>config('cache.type')]);

        if($cache->has($cacheName)){
            return $cache->get($cacheName);
        }

        $data = $this->where(['type'=>$cateType])->order('order_num','desc')
            ->select()->toArray();

        $cache->set($cacheName,$data);

        return $data;

    }

    public function clear(\app\common\typeCode\impl\CateImpl $obj)
    {
        $cacheName = $obj->getCateCacheName();

        $cache = new Cache(['type'=>config('cache.type')]);

        $cache->rm($cacheName);

    }
}
