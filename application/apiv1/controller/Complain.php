<?php

namespace app\apiV1\controller;


use think\Controller;
use think\Request;
use app\common\model\Category as CateModel;
use app\common\model\Video as VideoModel;
use app\common\model\Complain as ComplainModel;
use think\Validate;

class Complain extends Base
{
    public function getVideoCate()
    {
        $cateType = new \app\common\typeCode\cate\complain\Video();

        $cate = (new CateModel())->getList($cateType);

        $arr = [];
        foreach ($cate as $key => $value){
            $arr[] = [
                'id'    => $value['id'],
                'name'  => $value['name'],
            ];
        }

        return json(['code'=>1,'msg'=>'success','data'=>$arr]);
    }

    public function VideoSave(Request $request)
    {
        $post = $request->post();

        $user = $this->userInfo;

        $rules = [
            'cate_id'   => 'require',
            'content'   => 'require|max:127',
            'video_id'  => 'require',
            'files_path' => 'max:1270',
        ];

        $messages = [
            'cate_id.require'   => '必须选择分类',
            'content.require'   => '原因不能为空',
            'content.max'       => '请限制在128字以内',
            'video_id.require'  => '请求错误',
            'files_path'        => '附件过多',
        ];

        $validate = new Validate($rules,$messages);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $cateModel = new CateModel();
        $videoModel = new VideoModel();
        $complainModel = new ComplainModel();
        $videoType = new \app\common\typeCode\cate\complain\Video();

        //验证分类
        $cateExists = $cateModel->where(['type'=>$videoType->getCateType()])->find($post['cate_id']);
        if (!$cateExists) return json(['code'=>0,'msg'=>'请求错误']);

        //验证视频
        if (!$videoModel->field('id')->find($post['video_id'])){
            return json(['code'=>0,'msg'=>'请求错误']);
        }

        //组装数据
        $insert = [
            'user_id'   => $user->id,
            'object_id' => $post['video_id'],
            'type'      => $videoType->getType(),
            'cate_id'   => $post['cate_id'],
            'cate_name' => $cateExists['name'],
            'content'   => $post['content'],
            'files_path' => $post['files_path'],
            'create_time' => time(),
        ];
        $complainModel->insert($insert);

        return json(['code'=>1,'msg'=>'success']);

    }
}
