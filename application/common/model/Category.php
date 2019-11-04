<?php

namespace app\common\model;

use app\common\typeCode\impl\CateImpl;
use think\Cache;
use think\Model;

class Category extends Model
{
    public function existsCate(CateImpl $cateImpl,$cate_id)
    {
        $result = $this->where(['type'=>$cateImpl->getCateType()])
            ->where(['id'=>$cate_id])->find();

        return $result ?? false;
    }

    public function getList(\app\common\typeCode\impl\CateImpl $obj)
    {
        $cateType = $obj->getCateType();

        $cacheName = $obj->getCateCacheName();

        $levelType = $obj->getLevelType();

        $cache = new Cache(['type'=>config('cache.type')]);

        if($cache->has($cacheName)){
            return $cache->get($cacheName);
        }

        $data = $this->where(['type'=>$cateType])->order('order_num','desc')
            ->select()->toArray();

        if($levelType == 'more'){
            $data = $this->getMoreList($data);
        }

        $cache->set($cacheName,$data);

        return $data;

    }

    public function clear(\app\common\typeCode\impl\CateImpl $obj)
    {
        $cacheName = $obj->getCateCacheName();

        $cache = new Cache(['type'=>config('cache.type')]);

        $cache->rm($cacheName);
    }

    private function getMoreList($categorys,$pId = 0,$l = 0)
    {
        $list =array();

        foreach ($categorys as $k=>$v){

            if ($v['p_id'] == $pId){
                unset($categorys[$k]);
                if ($l < 2){
                    //小于三级
                    $v['child'] = $this->getMoreList($categorys,$v['id'],$l+1);
                }
                $list[] = $v;
            }
        }
        return $list;
    }
}
