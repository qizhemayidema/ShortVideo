<?php

namespace app\api\controller;

use app\common\model\History as HistoryModel;
use think\Controller;
use think\Request;
use think\Validate;
use app\common\model\Both as BothModel;
use app\common\model\User as UserModel;
use app\common\model\Message as MessageModel;
use app\common\typeCode\message\NewFans as NewFansModel;
use app\common\typeCode\message\PrivateMessage as PrivateMessageMessage;
use app\common\typeCode\history\VideoCollect as VideoCollectHistory;
use app\common\typeCode\history\FocusUser as FocusUserHistory;

class User extends Base
{
    //用户关注的列表
    public function focusList(Request $request)
    {
        $get = $request->get();

        $rules = [
            'page'  => 'require',
            'length' => 'require',
            'user_id' => 'require',
        ];

        $message = [
            'page.require' => 'token不合法',
            'length.require' => 'length不合法',
            'user_id.require' => 'user_id不合法'
        ];

        $validate = new Validate($rules,$message);

        if (!$validate->check($get)) return json(['code'=>0,'msg'=>$validate->getError()]);

        if (isset($get['token'])){
            $loginUserId = $this->userInfo->id;
        }else{
            $loginUserId = 0;
        }

        $userModel = new UserModel();
        $bothModel = new BothModel();


        $user = $userModel->receptionShowData()->find($get['user_id']);;

        if (!$user) return json(['code'=>0,'msg'=>'用户不存在或已被封禁,无法查看']);

        $start = $get['page'] * $get['length'] - $get['length'];

        $bothList = $bothModel->alias('both')
            ->join('user user','user.id = both.to_user_id and both.from_user_id = '.$user->id)
            ->leftJoin('both bothUser','bothUser.from_user_id ='.$loginUserId.' and bothUser.to_user_id = both.to_user_id')
            ->field('bothUser.create_time focus')
            ->field('user.nickname,user.avatar_url,user.id user_id,user.sex,both.create_time')
            ->limit($start,$get['length'])->select();

        return json(['code'=>1,'msg'=>'success','data'=>$bothList]);
    }

    //关注和取消关注接口
    public function focusSave(Request $request)
    {
        $user = $this->userInfo;

        $post = $request->post();

        $rules = [
            'token' => 'require',
            'user_id' => 'require',
        ];

        $message = [
            'token.require' => 'token不合法',
            'user_id.require' => 'user_id不合法',
        ];

        $validate = new Validate($rules,$message);

        if (!$validate->check($post)) return json(['code'=>0,'msg'=>$validate->getError()]);

        $bothModel = new BothModel();
        $userModel = new UserModel();
        $messageModel = new MessageModel();
        $newFansModel = new NewFansModel();

        $historyModel = new HistoryModel();
        $focusUserHistory = new FocusUserHistory();

        if (!$to_user = $userModel->existsUser($post['user_id']))
            return json(['code'=>0,'msg'=>'操作失误,请稍后尝试']);

        if ($post['user_id'] == $user->id)
            return json(['code'=>0,'msg'=>'自己不能关注自己哦']);



        $bothModel->startTrans();
        try{
            //判断是否已经关注
            if($both = $bothModel->existsBoth($user->id,$post['user_id'])){
                //取消关注 2
                $code = 2;
                //去掉关注状态
                $both->delete();

                //变更两人的数据
                $userModel->where(['id'=>$user->id])->setDec('focus_sum');
                $userModel->where(['id'=>$post['user_id']])->setDec('fans_sum');
            }else{
                $code = 1;  //关注 2

                //关注状态增加
                $bothModel->insert([
                    'from_user_id'  => $user->id,
                    'to_user_id'    => $post['user_id'],
                    'create_time'   => time(),
                ]);
                //变更两人的数据
                $userModel->where(['id'=>$user->id])->setInc('focus_sum');
                $userModel->where(['id'=>$post['user_id']])->setInc('fans_sum');
                //判断是否是第一次关注
                if (!$historyModel->existsHistory($focusUserHistory,$user->id)){
                    $historyModel->add($focusUserHistory,$user->id,$post['user_id']);
                    $userModel->incScore($user->id,$this->getConfig("assignment_score.first_focus"));
                }

                //发送消息
                $messageModel->send($newFansModel,$post['user_id']);
            }
            $bothModel->commit();
        }catch (\Exception $e){
            $bothModel->rollback();
            return json(['code'=>0,'msg'=>$e->getMessage().$e->getLine()]);
            return json(['code'=>0,'msg'=>'操作失误,请稍后再试']);
        }

        return json(['code'=>$code,'msg'=>'success']);
    }

    //粉丝列表
    public function fansList(Request $request)
    {
        $get = $request->get();

        $rules = [
            'page'  => 'require',
            'length' => 'require',
            'user_id' => 'require',
        ];

        $message = [
            'page.require' => 'token不合法',
            'length.require' => 'length不合法',
            'user_id.require' => 'user_id不合法'
        ];

        $validate = new Validate($rules,$message);

        if (!$validate->check($get)) return json(['code'=>0,'msg'=>$validate->getError()]);

        if (isset($get['token'])){
            $loginUserId = $this->userInfo->id;
        }else{
            $loginUserId = 0;
        }

        $userModel = new UserModel();
        $bothModel = new BothModel();


        $user = $userModel->receptionShowData()->find($get['user_id']);;

        if (!$user) return json(['code'=>0,'msg'=>'用户不存在或已被封禁,无法查看']);

        $start = $get['page'] * $get['length'] - $get['length'];

        $bothList = $bothModel->alias('both')
            ->join('user user','user.id = both.from_user_id and both.to_user_id = '.$user->id)
            ->leftJoin('both bothUser','bothUser.from_user_id ='.$loginUserId.' and bothUser.to_user_id = both.from_user_id')
            ->field('bothUser.create_time focus')
            ->field('user.nickname,user.avatar_url,user.id user_id,user.sex,both.create_time')
            ->limit($start,$get['length'])->select();

        return json(['code'=>1,'msg'=>'success','data'=>$bothList]);
    }

    //收藏列表
    public function collectList(Request $request)
    {
        $user = $this->userInfo;

        $get = $request->get();

        $rules = [
            'page'  => 'require|number',
            'length' => 'require|number',
        ];

        $message = [
            'page.require'  => 'err1',
            'page.number'   => 'err2',
            'length.require' => 'err3',
            'length.number'  => 'err4',
        ];

        $validate = new Validate($rules,$message);
        if (!$validate->check($get)) return json(['code'=>0,'msg'=>$validate->getError()]);

        $historyModel = new HistoryModel();

        $start = $get['page'] * $get['length'] - $get['length'];

        $res = $historyModel->getList(new VideoCollectHistory(),$user->id,$start,$get['length']);

        $return = [];

        foreach ($res as $key => $value){
            $return[] = [
                'id'    => $value['id'],
                'video_pic' => $value['video_pic'],
                'title' => $value['title'],
                'see_sum' => $value['see_sum'],
            ];
        }

        return json(['code'=>1,'msg'=>'success','data'=>$return]);

    }

    //给某个用户发送一条私信
    public function PrivateMessageSave(Request $request)
    {
        $post = $request->post();

        $user = $this->userInfo;

        $rules = [
            'token'     => 'require',
            'message'   => 'require|max:127',
            'user_id'   => 'require',
        ];

        $messages = [
            'token.require'     => 'token不合法',
            'message.require'   => '信息必须填写',
            'message.max'       => '信息最长不能超过127个字符',
            'user_id.require'   => 'user_id不合法',
        ];

        $validate = new Validate($rules,$messages);

        if (!$validate->check($post)) return json(['code'=>0,'msg'=>$validate->getError()]);

        $userModel = new UserModel();
        $messageModel = new MessageModel();
        $privateMessageMessage = new PrivateMessageMessage();


        //检查user_id合法性
        if (!$userModel->existsUser($post['user_id'])) return json(['code'=>0,'msg'=>'接收方账号已被冻结']);

        //发送提醒消息 你收到N条私信
        $messageModel->send($privateMessageMessage,$post['user_id'],$post['message']);


        return json(['code'=>1,'msg'=>'success']);
    }

    //私信列表
    public function privateMessageList(Request $request)
    {
        $get = $request->get();
        $rules = [
            'token'     => 'require',
        ];

        $messages = [
            'token.require' => 'token不合法'
        ];

        $validate = new Validate($rules,$messages);

        if (!$validate->check($get)){
            return json(['code'=>0,'msg'=>'success']);
        }
    }

    //
    public function privateMessageInfo()
    {

    }
}
