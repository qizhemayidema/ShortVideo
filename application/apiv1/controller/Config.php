<?php

namespace app\apiv1\controller;


use think\Controller;
use think\Request;

class Config extends Base
{
    //获取任务相关配置
    public function assignmentScore()
    {
        return json(['code'=>1,'msg'=>'success','data'=>$this->getConfig('assignment_score')]);

    }

    //获取导航页
    public function startPage()
    {
        return json(['code'=>1,'msg'=>'success','data'=>$this->getConfig('start_page')]);
    }

    //获取文章
    public function article(Request $request)
    {
        $arr = [
            'policy' => "privacy_policy",   //协议
            'clause' => "use_clause",   //条款
            'about'  => "about_us"     //关于
        ];

         $type = $request->get('type');

         $temp = isset($arr[$type]) ? $type : 'policy';

         return json(['code'=>1,'msg'=>'success','data'=>$this->getConfig($arr[$temp])]);

    }

    //获取拍摄最大秒数
    public function videoMaxSec()
    {
        return json(['code'=>1,'msg'=>'success','data'=>$this->getConfig('max_length_of_video_sec')]);
    }
}
