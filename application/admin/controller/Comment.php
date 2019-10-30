<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use app\common\model\Comment as CommentModel;
use app\common\typeCode\comment\Video as VideoType;

class Comment extends Base
{
    public function index()
    {
        $list = (new CommentModel())->backgroundShowData()->where('top_id',0)
            ->order('create_time','desc')->paginate(15);

        $this->assign('list', $list);

        return $this->fetch();
    }

    public function changeStatus(Request $request)
    {
        $id = $request->post('comment_id');

        (new CommentModel())->UpdateStatus($id);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function replyList(Request $request)
    {
        $top_id = $request->param('id');

        $list = (new CommentModel())->backgroundShowData()->where('top_id',$top_id)
            ->order('create_time','desc')->paginate(15,false,['query' => request()->param()]);

        $this->assign('list', $list);

        return $this->fetch();

    }
}
