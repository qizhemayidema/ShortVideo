<?php

namespace app\apiV1\controller;


use app\common\typeCode\history\VideoAppraise;
use think\Exception;
use think\Request;
use think\Validate;
use app\common\typeCode\history\VideoLike as VideoLikeHistory;
use app\common\typeCode\history\Video as VideoHistory;
use app\common\typeCode\history\VideoCollect as VideoCollectHistory;
use app\common\typeCode\message\VideoLike as VideoLikeMessage;
use app\common\typeCode\comment\Video as CommentVideoTypeModel;
use app\common\typeCode\history\VideoShareFriends as ShareFriendsHistory;
use app\common\typeCode\history\VideoShareFriendsRound as ShareFriendsRoundHistory;
use app\common\model\Comment as CommentModel;
use app\common\model\Video as VideoModel;
use app\common\model\Message as MessageModel;
use app\common\model\User as UserModel;
use app\common\model\History as HistoryModel;
use app\common\model\Category as CateModel;
use app\common\typeCode\cate\Video as CateVideoType;
use app\common\model\Both as BothModel;
use app\common\typeCode\history\VideoAppraise as VideoAppraiseHistory;

class Video extends Base
{

    //获取视频评论列表
    public function commentLists(Request $request, CommentModel $comment, VideoModel $video, CommentVideoTypeModel $commentType)
    {
        $get = $request->get();

        $rules = [
            'page' => 'require|integer',
            'length' => 'require|integer',
            'video_id' => 'require',
        ];

        $messages = [
            'page.require' => "err1",
            'length.require' => 'err2',
            'video_id.require' => 'err3',
        ];

        $login_user_id = $this->existsToken() ? $this->userInfo->id : 0;


        $validate = new Validate($rules, $messages);

        if (!$validate->check($get)) return json(['code' => 0, 'msg' => $validate->getError()]);

        if (!$video->checkShowStatus($get['video_id'])) return json(['code' => 0, 'msg' => '评论不存在']);

        $start = $get['page'] * $get['length'] - $get['length'];

        $list = $comment->getList($commentType, $start, $get['length'], $get['video_id'], $login_user_id, false);

        return json(['code' => 1, 'msg' => 'success', 'data' => $list]);
    }

    //获取视频评论回复
    public function commentReplyList(Request $request, CommentModel $comment, VideoModel $video, CommentVideoTypeModel $commentType)
    {
        $get = $request->get();

        $rules = [
            'page' => 'require|integer',
            'length' => 'require|integer',
            'comment_id' => 'require',
        ];

        $messages = [
            'page.require' => "err1",
            'length.require' => 'err2',
            'comment_id.require' => 'err3',
        ];

        $validate = new Validate($rules, $messages);

        if (!$validate->check($get)) return json(['code' => 0, 'msg' => $validate->getError()]);

        $start = $get['page'] * $get['length'] - $get['length'];

        $list = $comment->getReplyList($commentType, $start, $get['length'], $get['comment_id']);

        return json(['code' => 1, 'msg' => 'success', 'data' => $list]);
    }

    //视频点赞
    public function likeSave(Request $request)
    {
        $user = $this->userInfo;

        $post = $request->post();

        $rules = [
            'token' => 'require',
            'video_id' => 'require',
        ];

        $messages = [
            'token.require' => 'token不合法',
            'video_id.require' => 'video_id不合法',
        ];

        $validate = new Validate($rules, $messages);

        if (!$validate->check($post)) return json(['code' => 0, 'msg' => $validate->getError()]);

        $videoModel = new VideoModel();

        $historyModel = new HistoryModel();
        $videoLikeHistory = new VideoLikeHistory();

        $messageModel = new MessageModel();
        $videoLikeMessage = new VideoLikeMessage();

        //检查video合法性
        $video = $videoModel->checkShowStatus($post['video_id']);

        if (!$video) return json(['code' => 0, 'msg' => '该视频无法点赞']);

        //用户是否点赞
        $exists = $historyModel->existsHistory($videoLikeHistory, $user->id, $post['video_id']);

        if ($exists) return json(['code' => 0, 'msg' => '您已经点过赞了哦,请不要重复点赞']);

        $videoModel->startTrans();
        try {
            //给video加上一个赞
            $videoModel->where(['id' => $post['video_id']])->setInc('like_sum');

            //记录历史记录
            $historyModel->add($videoLikeHistory, $user->id, $post['video_id']);

            //发送消息给作者本人
            $messageModel->send($videoLikeMessage, $video->user_id);

            //给作者本人添加获赞
            (new UserModel())->where(['id' => $video->user_id])->setInc('get_like_sum');

            $videoModel->commit();
        } catch (\Exception $e) {
            $videoModel->rollback();
            return json(['code' => 0, 'msg' => '点赞失败']);
        }

        return json(['code' => 1, 'msg' => 'success']);
    }

    //视频收藏
    public function collectSave(Request $request)
    {
        $user = $this->userInfo;

        $post = $request->post();

        $rules = [
            'token' => 'require',
            'video_id' => 'require',
        ];

        $messages = [
            'token.require' => 'token不合法',
            'video_id.require' => 'video_id不合法',
        ];

        $validate = new Validate($rules, $messages);

        if (!$validate->check($post)) return json(['code' => 0, 'msg' => $validate->getError()]);

        $videoModel = new VideoModel();

        $historyModel = new HistoryModel();
        $collectType = new VideoCollectHistory();

        $userModel = new UserModel();


        //检查video合法性
        $video = $videoModel->checkShowStatus($post['video_id']);

        if (!$video) return json(['code' => 0, 'msg' => '该视频无法收藏']);

        //用户是否收藏
        $exists = $historyModel->existsHistory($collectType, $user->id, $post['video_id']);

        if ($exists) {
            $returnCode = 2;    //取消收藏
        } else {
            $returnCode = 1;    //收藏
        }

        $videoModel->startTrans();
        try {
            if ($returnCode == 1) {
                //给video加上一个收藏
                $videoModel->where(['id' => $post['video_id']])->setInc('collect_sum');

                //更改用户
                $userModel->where(['id' => $user->id])->setInc('collect_sum');

                //记录历史记录
                $historyModel->add($collectType, $user->id, $post['video_id']);

            } elseif ($returnCode == 2) {
                //给video取消一个收藏
                $videoModel->where(['id' => $post['video_id']])->setDec('collect_sum');

                //更改用户
                $userModel->where(['id' => $user->id])->setDec('collect_sum');

                //删除历史记录
                $historyModel->rm($collectType, $user->id, $post['video_id']);
            }


            $videoModel->commit();
        } catch (\Exception $e) {
            $videoModel->rollback();
            return json(['code' => 0, 'msg' => '收藏失败']);
        }

        return json(['code' => $returnCode, 'msg' => 'success']);

    }

    //视频分享到好友
    public function shareFriends(Request $request)
    {
        $user = $this->userInfo;

        $videoId = $request->post('video_id');

        if (!$videoId) return json(['code' => 0, 'msg' => 'err1']);

        $videoModel = new VideoModel();

        $historyModel = new HistoryModel();

        $userModel = new UserModel();

        $history = new ShareFriendsHistory();

        if (!($videoModel->checkShowStatus($videoId))) return json(['code' => 0, 'msg' => '该视频无法分享']);

        //判断用户是否是第一次分享
        $is_exists = $historyModel->existsHistory($history, $user->id);

        $userModel->startTrans();

        try {
            if (!$is_exists) $userModel->incScore($user->id, $this->getConfig('assignment_score.first_share_friends'));

            $historyModel->add($history, $user->id, $videoId);

            $userModel->commit();
        } catch (\Exception $e) {

            $userModel->rollback();

            return json(['code' => 0, 'msg' => '操作失败,请稍后再试']);
        }

        return json(['code' => 1, 'msg' => 'success']);

    }

    //视频分享到朋友圈
    public function shareFriendsRound(Request $request)
    {
        $user = $this->userInfo;

        $videoId = $request->post('video_id');

        if (!$videoId) return json(['code' => 0, 'msg' => 'err1']);

        $videoModel = new VideoModel();

        $historyModel = new HistoryModel();

        $userModel = new UserModel();

        $history = new ShareFriendsRoundHistory();

        if (!($videoModel->checkShowStatus($videoId))) return json(['code' => 0, 'msg' => '该视频无法分享']);

        //判断用户是否是第一次分享
        $is_exists = $historyModel->existsHistory($history, $user->id);

        $userModel->startTrans();

        try {
            if (!$is_exists) $userModel->incScore($user->id, $this->getConfig('assignment_score.first_share_friends_round'));

            $historyModel->add($history, $user->id, $videoId);

            $userModel->commit();
        } catch (\Exception $e) {

            $userModel->rollback();

            return json(['code' => 0, 'msg' => '操作失败,请稍后再试']);
        }

        return json(['code' => 1, 'msg' => 'success']);

    }

    /**
     * 获取列表
     * type 1 推荐 2 关注 3 分类下的数据
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function list(Request $request)
    {
        $type = $request->get('type') ?? 1;

        $cate = $request->get('cate_id') ?? 0;

        $page = $request->get('page') ?? 1;

        $length = $request->get('length') ?? 10;

        $loginUserId = $this->existsToken() ? $this->userInfo->id : 0;

        $start = $page * $length - $length;

        $videoModel = new VideoModel();

        switch ($type) {
            case 1:
                $data = $videoModel->receptionShowData('video')
                    ->alias('video')
                    ->join('user user', 'user.id = video.user_id')
                    ->field('video.video_pic,video.id video_id,video.title,video.ok_sum,video.no_sum,video.like_sum,video.comment_sum,video.share_sum')
                    ->field('user.avatar_url,user.nickname,user.id user_id')
                    ->where('video.create_time', '>', time() - 60 * 60 * 24 * 7)
                    ->order('like_sum', 'desc')
                    ->limit($start, $length)
                    ->select()->toArray();
                break;
            case 2:

                $bothModel = new BothModel();
                $focusUserIds = $bothModel->where(['from_user_id' => $loginUserId])->column('to_user_id');
                $data = $videoModel->receptionShowData('video')
                    ->alias('video')
                    ->join('user user', 'user.id = video.user_id')
                    ->field('video.video_pic,video.id video_id,video.title,video.ok_sum,video.no_sum,video.like_sum,video.comment_sum,video.share_sum')
                    ->field('user.avatar_url,user.nickname,user.id user_id')
                    ->whereIn('video.user_id', $focusUserIds)
                    ->where('video.create_time', '>', time() - 60 * 60 * 24 * 7)
                    ->order('like_sum', 'desc')
                    ->limit($start, $length)
                    ->select()->toArray();
                break;
            case 3:
                $data = $videoModel->receptionShowData('video')
                    ->alias('video')
                    ->where(['video.cate_id' => $cate])
                    ->join('user user', 'video.user_id = user.id')
                    ->field('video.video_pic,video.id video_id,video.title,video.ok_sum,video.no_sum,video.like_sum,video.comment_sum,video.share_sum')
                    ->field('user.avatar_url,user.nickname,user.id user_id')
                    ->order('video.create_time','desc')
                    ->limit($start, $length)
                    ->select()->toArray();
        }
        return json(['code' => 1, 'msg' => 'success', 'data' => $data]);
    }

    //评价一个视频 1 完全正确 2 误人子弟
    public function appraise(Request $request)
    {
        $post = $request->post();

        $user = $this->userInfo;

        $rule = [
            'video_id'  => 'require',
            'status'    => 'require|number',
        ];

        $message = [
            'video_id.require'  => 'video_id err',
            'status.require'    => 'status require',
            'status.number'     => 'status number',
        ];

        $validate = new Validate($rule,$message);
        if (!$validate->check($post)){
            return json(['code'=>0,'mgs'=>$validate->getError()]);
        }
        $videoModel = new VideoModel();
        $historyModel = new HistoryModel();
        $videoAppraiseHistory = new VideoAppraiseHistory();

        //判断视频是否合法
        $res = $videoModel->checkShowStatus($post['video_id']);
        if (!$res) return json(['code'=>0,'msg'=>'该视频无法评价']);

        //判断用户是否评价过了
        $isExists = $historyModel->existsHistory($videoAppraiseHistory,$user->id,$post['video_id']);
        if ($isExists) return json(['code'=>0,'msg'=>'您已经评价过了,无法再次评价']);

        $incField = $post['status'] == 1 ? 'ok_sum' : 'no_sum';
        $videoModel->startTrans();
        try{
            //给视频添加数据
            $videoModel->where(['id'=>$post['video_id']])->setInc($incField);
            //记录历史记录
            $historyModel->add($videoAppraiseHistory,$user->id,$post['video_id']);

            $videoModel->commit();
        }catch (\Exception $e){
            $videoModel->rollback();
            return json(['code'=>0,'msg'=>'操作失误,请稍后再试']);
        }

        return json(['code'=>1,'msg'=>'success']);
    }

    //发布一个视频
    public function save(Request $request)
    {
        $user = $this->userInfo;

        //判断用户是否能够发布
        $authType = $this->getConfig('take_video_auth');

        if ($authType != 0 && $user->auth_id != $authType) {
            return json(['code' => 0, 'msg' => '您无权发布视频']);
        }

        $post = $request->post();

        $rules = [
            'cate_id' => 'require',
            "title" => "require|max:30",
            "video_pic" => 'require|max:128',
            "source_url" => "require|max:128",
        ];

        $message = [
            "cate_id.require" => '必须选择一个分类',
            "title.require" => '必须填写描述',
            "title.max" => '描述最大长度不能超过30',
            'video_pic.require' => '封面必须携带',
            'video_pic.max' => '封面非法',
            'source_url.require' => '资源路径非法',
            'source_url.max' => '资源路径非法'
        ];

        $validate = new Validate($rules, $message);
        if (!$validate->check($post)) {
            return json(['code' => 0, 'msg' => $validate->getError()]);
        }

        $cateModel = new CateModel();
        $cateVideoType = new CateVideoType();
        $videoModel = new VideoModel();
        $historyModel = new HistoryModel();
        $userModel = new UserModel();

        //判断分类id是否合法
        if (!$cateModel->existsCate($cateVideoType, $post['cate_id'])) {
            return json(['code' => 0, 'msg' => '操作非法']);
        }


        $videoModel->startTrans();

        try {
            //入库 修改相关统计数据
            $insId = $videoModel->add($user->id, $post['cate_id'], $post['title'], $post['video_pic'], $post['source_url']);

            //判断 是否是当天第一次发布
            $startTime = strtotime(date('Y-m-d'));
            $videoHistory = new VideoHistory();
            $tempType = $videoHistory->getType();
            if (!$historyModel->where(['user_id' => $user->id, 'type' => $tempType])->where('create_time', '>', $startTime)->find()) {
                $historyModel->add($videoHistory, $user->id, $insId);
                $userModel->incScore($user->id, $this->getConfig('assignment_score.everyday_take_video'));
            }

            $videoModel->commit();
        } catch (\Exception $e) {
            $videoModel->rollback();
            return json(['code' => 0, 'msg' => '操作失误,请稍后再试']);
        }

        return json(['code' => 1, 'msg' => 'success']);
    }

    //删除一个视频
    public function delete(Request $request)
    {
        $user = $this->userInfo;

        $post = $request->delete();

        $rules = [
            'video_id' => 'require',
        ];

        $message = [
            'video_id.require' => 'err1',
        ];

        $validate = new Validate($rules, $message);

        if (!$validate->check($post)) {
            return json(['code' => 0, 'msg' => $validate->getError()]);
        }

        $videoModel = new VideoModel();
        $userModel = new UserModel();

        //判断是否为作者本人
        $video = $videoModel->receptionShowData()->where(['id' => $post['video_id'], 'user_id' => $user->id])->find();
        if (!$video) return json(['code' => 0, 'msg' => '请求错误,请稍后再试']);

        $userModel->startTrans();
        try {
            //删除
            $video->save(['delete_time' => time()]);

            //修改用户作品数量
            $userModel->where(['id' => $user->id])->setDec('works_sum');

            $userModel->commit();
        } catch (\Exception $e) {
            $userModel->rollback();
            return json(['code' => 0, 'msg' => '请求错误,请稍后再试']);
        }

        return json(['code' => 1, 'msg' => 'success']);
    }

    //播放一个视频
    public function play(Request $request)
    {
        try{

            $video_id = $request->get('video_id');

            $loginUserId = $this->existsToken() ? $this->userInfo->id : 0;

            if (!$video_id) throw new Exception('');

            $videoModel = new VideoModel();
            $historyModel = new HistoryModel();
            $likeHistory = new VideoLikeHistory();
            $collectHistory = new VideoCollectHistory();

            $info = $videoModel->receptionShowData('video')
                ->alias('video')
                ->join('user user', 'user.id = video.user_id')
                ->field('video.source_url,video.video_pic,video.id video_id,video.title,video.ok_sum,video.no_sum,video.like_sum,video.comment_sum,video.share_sum')
                ->field('user.avatar_url,user.nickname,user.id user_id')
                ->where(['video.id'=>$video_id])
                ->find()->toArray();

            if (!$info) throw new Exception('');

            $status = [
                'is_focus' => $loginUserId && (new BothModel())->where(['from_user_id'=>$loginUserId,'to_user_id'=>$info['user_id']])->find() ? true : false,
                'is_like' => $loginUserId && $historyModel->existsHistory($likeHistory,$loginUserId,$info['video_id']) ? true : false,
                'is_collect' => $loginUserId && $historyModel->existsHistory($collectHistory,$loginUserId,$info['video_id']) ? true : false,
            ];

            $videoModel->receptionShowData()->where(['id'=>$video_id])->setInc('see_sum');

            $data = [
                'info' => $info,
                'status' => $status,
            ];
            return json(['code'=>1,'msg'=>'success','data'=>$data]);
        }catch (\Exception $e){
            return json(['code'=>0,'msg'=>'播放出错,请稍后再试']);
        }

    }

}
