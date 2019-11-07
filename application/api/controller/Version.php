<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use app\common\model\Version as VersionModel;

class Version extends Base
{
    //获取最新版本
    public function getNewest()
    {
        $data = (new VersionModel())->order('id','desc')
            ->field('id,version,update_desc,android_source,is_compel,create_time')
            ->limit(1)
            ->select();

        return json(['code'=>1,'msg'=>'success','data'=>$data]);
    }
}
