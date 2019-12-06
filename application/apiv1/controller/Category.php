<?php

namespace app\apiv1\controller;


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

        array_unshift($return,[
            "id"=> 0,
            "p_id" => 0,
            "type" => 1,
            "name" => "推荐",
            "data_sum"=> 0,
            "order_num"=> 0
        ]);

        return json(['code'=>1,'msg'=>'success','data'=>$return]);
    }
}
