<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class Config extends Base
{
    //获取任务相关配置
    public function assignmentScore()
    {
        return json(['code'=>1,'msg'=>'success','data'=>$this->getConfig('assignment_score')]);

    }
}
