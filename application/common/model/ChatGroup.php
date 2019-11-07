<?php

namespace app\common\model;

use think\Model;

class ChatGroup extends Model
{

    public function getList($userId,$start,$length)
    {
        return $this->alias('group')
            ->where(['group.user_id'=>$userId])
            ->join('chat_message msg1','group.user_mark = msg1.user_mark')
            ->join('user user','user.id = group.peer_user_id')
            ->leftJoin('chat_message msg2','msg2.user_mark = msg1.user_mark and msg1.id < msg2.id')
            ->where('msg2.id IS NULL')
            ->field('user.nickname peer_nickname,user.id peer_user_id,user.avatar_url peer_avatar_url,msg1.message message')
            ->order('msg1.create_time','desc')
            ->limit($start,$length)
            ->select()->toArray();
    }

    //新建一个组
    public function add($userId,$peerUserId)
    {
        $userIds = [$userId,$peerUserId];
        sort($userIds);
        if (!$this->where(['user_id'=>$userId,'peer_user_id'=>$peerUserId])->value('id')){
            $this->insert([
                'user_id' => $userId,
                'peer_user_id' => $peerUserId,
                'user_mark' => implode('-',$userIds),
                'create_time' => time(),
            ]);
        }

        if (!$this->where(['user_id'=>$peerUserId,'peer_user_id'=>$userId])->value('id')){
            $this->insert([
                'user_id' => $peerUserId,
                'peer_user_id' => $userId,
                'user_mark' => implode('-',$userIds),
                'create_time' => time(),
            ]);
        }

    }
}
