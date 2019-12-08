<?php

namespace app\h5\controller;


use app\common\model\Message;

use app\common\typeCode\message\NewChat;

use think\Request;
use think\Validate;
use app\common\model\User as UserModel;

use app\common\typeCode\message\NewChat as NewChatMessage;

use app\common\model\ChatMessage as ChatMessageModel;
use app\common\model\ChatGroup as ChatGroupModel;
class User extends Base
{

    //给某个用户发送一条私信
    public function privateMessageSave(Request $request)
    {
        $post = $request->param();

        $user = $this->userInfo;

        $rules = [
            'message' => 'require|max:127',
            'user_id' => 'require',
        ];

        $messages = [
            'message.require' => '信息必须填写',
            'message.max' => '信息最长不能超过127个字符',
            'user_id.require' => 'user_id不合法',
        ];

        $validate = new Validate($rules, $messages);

        if (!$validate->check($post)) return json(['code' => 0, 'msg' => $validate->getError()]);

        $userModel = new UserModel();
        $chatModel = new ChatMessageModel();
        $groupModel = new ChatGroupModel();

        //检查user_id合法性
        if (!$userModel->existsUser($post['user_id'])) return json(['code' => 0, 'msg' => '接收方账号已被冻结']);

        $chatModel->startTrans();
        try{
            //如没有group则创建
            $groupModel->add($user->id,$post['user_id']);

            //入库
            $chatModel->add($user->id, $post['user_id'], $post['message']);

            //新的消息入库
            (new Message())->send((new NewChatMessage()),$post['user_id']);

            $chatModel->commit();
        }catch (\Exception $e){
            $chatModel->rollback();
            return json(['code'=>0,'msg'=>'发送失败,请稍后再试']);
        }

        return json(['code' => 1, 'msg' => 'success']);
    }

    //私信列表
    public function privateMessageList(Request $request)
    {
        $page = $request->param('page') ?? 1;
        $length = $request->param('length') ?? 10;
        $start = $page * $length - $length;
        $user = $this->userInfo;

        (new Message())->seen((new NewChat()),$user->id);

        $data = (new ChatGroupModel())->getList($user->id,$start,$length);

        return json(['code'=>1,'msg'=>'success','data'=>$data]);

    }

    //聊天记录页
    public function privateMessageInfo(Request $request)
    {
        $user = $this->userInfo;

        $userId = $request->param('user_id') ?? 0;

        $page = $request->param('page') ?? 1;

        $length = $request->param('length') ?? 10;

        $start = $page * $length - $length;

        $otherUser = (new UserModel())->find($userId);


        $data = (new ChatMessageModel())->getList($user->id,$userId,$start,$length);

        return json([
            'code'=>1,
            'msg'=>'success',
            'data'=>array_reverse($data),
            'user'=>['avatar_url'=>$user['avatar_url']],
            'other_user' => ['nickname'=>$otherUser['nickname']]
        ]);


    }


}
