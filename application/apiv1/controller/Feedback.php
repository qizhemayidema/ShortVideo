<?php

namespace app\apiv1\controller;


use think\Controller;
use think\Request;
use think\Validate;
use app\common\model\Feedback as FeedbackModel;

class Feedback extends Base
{
    public function save(Request $request)
    {
        $post = $request->post();

        $user = $this->userInfo;

        $rules = [
            'comment'   => 'require',
        ];

        $messages = [
            'comment.require' => '评论必须填写',
        ];

        $validate = new Validate($rules,$messages);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        (new FeedbackModel())->add($user->id,$post['comment']);

        return json(['code'=>1,'msg'=>'success']);
    }
}
