<?php

namespace app\apiV1\controller;


use think\Controller;
use think\Request;
use app\common\model\User as UserModel;
use app\common\model\Video as VideoModel;

class Index extends Base
{
    //1 用户 2 作品
    public function search(Request $request)
    {
        $search = $request->get('search') ?? '';

        $type = $request->get('type') ?? 1;

        $length = $request->get('length') ?? 10;

        $start = ($request->get('page') ?? 1) * $length - $length;

        $userModel = new UserModel();
        $videoModel = new VideoModel();

        if ($type == 1) {
            //当前登陆id
            $userId = $this->existsToken() ? $this->userInfo->id : 0;

            $data = $userModel->receptionShowData('user')
                ->alias('user')
                ->leftJoin('both both', 'both.from_user_id = ' . $userId . ' and both.to_user_id = user.id')
                ->where('user.nickname', 'like', '%' . $search . '%')
                ->where('user.id','<>',$userId)
                ->field('both.create_time focus,user.avatar_url,user.id,user.fans_sum,user.nickname')
                ->limit($start, $length)
                ->select()->toArray();
        } else {
            $data = $videoModel->receptionShowData('video')
                ->alias('video')
                ->join('user user', 'video.user_id = user.id')
                ->where('video.title', 'like', '%' . $search . '%')
                ->field('video.video_pic,video.title,video.id video_id,video.see_sum,user.nickname,user.avatar_url,user.id user_id')
                ->limit($start, $length)
                ->select()->toArray();
        }

        return json(['code'=>1,'msg'=>'success','data'=>$data]);
    }
}
