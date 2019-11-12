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
        $list = (new ComplainModel())->alias('complain')
            ->join('user user','user.id = complain.user_id')
            ->join('video video','video.id = complain.object_id')
            ->field('complain.*,video.status status,user.nickname nickname,video.id video_id,video.title video_title')
            ->paginate(15);

        $this->assign('list',$list);

        return $this->fetch();

    }
}
