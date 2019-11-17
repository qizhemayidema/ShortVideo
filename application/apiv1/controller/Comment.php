<?php

namespace app\apiv1\controller;


use app\common\model\User as UserModel;
use app\common\typeCode\message\VideoComment as VideoCommentMessage;
use think\Request;
use think\Validate;
use app\common\model\Video as VideoModel;
use app\common\model\Message as MessageModel;
use app\common\model\History as HistoryModel;
use app\common\model\Comment as CommentModel;
use app\common\typeCode\history\CommentLike as CommentLikeHistory;
use app\common\typeCode\history\VideoComment as VideoCommentHistory;


class Comment extends Base
{
    public $user_id = 1;

    //评论一个视频
    public function videoSave(Request $request)
    {
        $post = $request->post();

        //step1 : 验证接口传参是否正确
        $rules = [
            'video_id'  => 'require|number',
            'content'   => 'require|max:511',
            'comment_id' => 'number',
            'reply_user_id'   => 'number',
        ];
        $messages = [
            'content.require'   => '必须填写评论',
            'content.max'       => '评论字符最大长度为512'
        ];
        $validate = new Validate($rules,$messages);
        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        //step2 : 验证视频合法性
        $videoModel = new VideoModel();
        $videoData = $videoModel->checkShowStatus($post['video_id']);
        if (!$videoData){
            return json(['code'=>0,'msg'=>'此视频已无法评论']);
        }

        $commentModel = new CommentModel();
        $userModel = new UserModel();
        $historyModel = new HistoryModel();
        $videoCommentHistory = new VideoCommentHistory();

        $commentModel->startTrans();
        try{
            //step3 : 准备数据 入库
            $user = $this->userInfo;
            $insert = [
                'type'  => 1,
                'public_id' => $post['video_id'],
                'user_id'   => $user->id,
                'nickname'  => $user->nickname,
                'avatar_url'=> $user->avatar_url,
                'comment'   => $post['content'],
                'create_time' => time(),
                'is_show'   => 1,
            ];

            //如果不是顶级评论节点则增加顶级节点的被评论数
            if (isset($post['comment_id'])){
                $comment = $commentModel->receptionShowData()->find($post['comment_id']);

                if (!$comment) return json(['code'=>0,'msg'=>'操作太快了,休息一下吧']);

                $insert['top_id'] = $post['comment_id'];

                $comment->setInc('comment_sum');
            }else{
                //发送消息到本人
                (new MessageModel())->send((new VideoCommentMessage()),$videoData['user_id']);

            }

            //如果有回复到用户,则记录,如果没有,则表明直接回复的顶级节点
            if (isset($post['reply_user_id'])){
                $replyUser = $userModel->find($post['reply_user_id']);
                $insert['reply_nickname'] = $replyUser->nickname;
                $insert['reply_user_id'] = $replyUser->id;
            }
            $commentModel->insert($insert);
            //更改视频被评论数
            $videoModel->where(['id'=>$post['video_id']])->setInc('comment_sum');

            //判断用户是不是 首次评论
            if(!$historyModel->existsHistory($videoCommentHistory,$user->id)){
                $historyModel->add($videoCommentHistory,$user->id,$commentModel->getLastInsID());
                $userModel->incScore($user->id,$this->getConfig('assignment_score.first_comment'));
            }
             $commentModel->commit();
        }catch (\Exception $e){
            $commentModel->rollback();
            return json(['code'=>0,'msg'=>'系统错误,请稍后再试']);
        }

        return json(['code'=>1,'msg'=>'success']);
    }

    //点赞一个评论
    public function likeSave(Request $request)
    {
        $post = $request->post();

        $user = $this->userInfo;

        $rules = [
            'comment_id' => 'require',
        ];

        $message = [
            'comment_id.require' => 'comment_id不合法',
        ];

        $validate = new Validate($rules,$message);

        if (!$validate->check($post)) return json(['code'=>0,'msg'=>$validate->getError()]);

        $historyModel = new HistoryModel();
        $commentLikeHistory = new CommentLikeHistory();
        $commentModel = new CommentModel();

        //检查是否点过赞了
        $history = $historyModel->existsHistory($commentLikeHistory,$user->id,$post['comment_id']);

        if ($history) return json(['code'=>0,'msg'=>'您已经点过赞了,不能重复点赞哦']);

        $commentModel->startTrans();
        try {

            //评论加赞
            (new CommentModel())->where(['id'=>$post['comment_id']])->setInc('like_sum');

            //记录历史
            $historyModel->add($commentLikeHistory,$user->id,$post['comment_id']);

            //不通知用户

            $commentModel->commit();
        }catch(\Exception $e){
            $commentModel->rollback();
            return json(['code'=>0,'msg'=>'点赞失败']);
        }

        return json(['code'=>1,'msg'=>'success']);
    }
}
