<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class Category extends Base
{
    public function getVideo()
    {
        $videoType = new \app\common\typeCode\cate\Video();

        $list = (new \app\common\model\Category())->getList($videoType);

        $return = [];
        foreach ($list as $item => $value) {
            $temp = $value;
            unset($temp['child']);
            $return[] = $temp;
        }

        return json(['code'=>1,'msg'=>'success','data'=>$return]);
    }
}
