<?php

namespace app\api\controller;

use think\Request;
use think\Validate;
use app\common\model\History as HistoryModel;
use app\common\typeCode\history\VideoLike as VideoLikeHistory;
use app\common\typeCode\message\VideoLike as VideoLikeMessage;
use app\common\model\Video as VideoModel;
use app\common\model\Message as MessageModel;
use app\common\model\User as UserModel;

class Video extends Base
{
    //视频点赞
    public function likeSave(Request $request)
    {
        $user = $this->userInfo;

        $post = $request->post();

        $rules = [
            'token'     => 'require',
            'video_id'  => 'require',
        ];

        $messages = [
            'token.require' => 'token不合法',
            'video_id.require' => 'video_id不合法',
        ];

        $validate = new Validate($rules,$messages);

        if(!$validate->check($post)) return json(['code'=>0,'msg'=>$validate->getError()]);

        $videoModel = new VideoModel();

        $historyModel = new HistoryModel();
        $videoLikeHistory = new VideoLikeHistory();

        $messageModel = new MessageModel();
        $videoLikeMessage = new VideoLikeMessage();

        //检查video合法性
        $video = $videoModel->checkShowStatus($post['video_id']);

        if (!$video) return json(['code'=>0,'msg'=>'该视频无法点赞']);

        //用户是否点赞
        $exists = $historyModel->existsHistory($videoLikeHistory,$user->id,$post['video_id']);

        if ($exists) return json(['code'=>0,'msg'=>'您已经点过赞了哦,请不要重复点赞']);

        $videoModel->startTrans();
        try{
            //给video加上一个赞
            $videoModel->where(['id'=>$post['video_id']])->setInc('like_sum');

            //记录历史记录
            $historyModel->add($videoLikeHistory,$user->id,$post['video_id']);

            //发送消息给作者本人
            $messageModel->send($videoLikeMessage,$video->user_id);

            //给作者本人添加获赞
            (new UserModel())->where(['id'=>$video->user_id])->setInc('get_like_sum');

            $videoModel->commit();
        }catch (\Exception $e){
            $videoModel->rollback();
            return json(['code'=>0,'msg'=>'点赞失败']);
        }

        return json(['code'=>1,'msg'=>'success']);
    }


}
