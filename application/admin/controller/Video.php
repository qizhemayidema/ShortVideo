<?php

namespace app\admin\controller;

use app\common\lib\Upload;
use app\common\model\Category;
use think\Controller;
use think\Request;
use app\common\model\Video as VideoModel;
use app\common\model\User as UserModel;
use app\common\typeCode\cate\Video as VideoTypeCate;
use think\Route;
use think\Validate;

class Video extends Base
{
    public function index()
    {
        $videoModel = new VideoModel();
        $video = $videoModel->backgroundShowData('video')->alias('video')
            ->join('category cate','video.cate_id = cate.id')
            ->order('video.id','desc')
            ->order('video.status','desc')
            ->field('video.*,cate.name cate_name')->paginate(15);

        $this->assign('video',$video);
        return $this->fetch();
    }

    public function info(Request $request)
    {
        $id = $request->param('id');

        $videoModel = new VideoModel();
        $info = $videoModel->backgroundShowData()->find($id);

        $user = (new UserModel)->find($info->user_id);

        $cate = (new Category())->find($info->cate_id);

        $this->assign('info',$info);
        $this->assign('user',$user);
        $this->assign('cate',$cate);


        return $this->fetch();



    }

    public function delete(Request $request)
    {
        $id = $request->param('id');

        (new VideoModel())->where(['id'=>$id])
            ->update(['delete_time'=>time()]);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function changeStatus(Request $request)
    {
        $status = $request->post('status') ?? 0;
        $id = $request->post('video_id');

        (new VideoModel())->where(['id'=>$id])->update(['status'=>$status]);

        return json(['code'=>1,'msg'=>'success']);
    }

}