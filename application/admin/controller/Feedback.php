<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Feedback extends Base
{
    //展示反馈列表
    public function index()
    {
        $list = (new \app\common\model\Feedback())->alias('feedback')
            ->join('user user','feedback.user_id = user.id')
            ->field('user.nickname,feedback.*')
            ->order('feedback.id','desc')
            ->paginate(15);

        $this->assign('list',$list);

        return $this->fetch();
    }
}
