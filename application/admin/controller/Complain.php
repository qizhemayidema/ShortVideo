<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\common\typeCode\cate\complain\Video as ComplainVideo;
use app\common\model\Complain as ComplainModel;

class Complain extends Base
{
    public function index()
    {
        $complain = new ComplainVideo();

        $list = (new ComplainModel())->alias('complain')
            ->join('user user','user.id = complain.user_id')
            ->join('video video','video.id = complain.object_id')
            ->join('category cate','complain.cate_id = cate.id')
            ->where(['type'=>$complain->getType()])
            ->field('complain.*,user.nickname nickname,video.title video_title,cate.name cate_name')
            ->paginate(15);

        $this->assign('list',$list);

        return $this->fetch();

    }
}
