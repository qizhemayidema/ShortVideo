<?php

namespace app\common\model;

use think\Model;
use think\Request;

class ChatMessage extends Model
{
    public function getList($userId,$peerUserId,$start,$length)
    {
        $arr = [$userId,$peerUserId];

        sort($arr);

        return $this->alias('msg')
            ->join('user user1','msg.user_id = user1.id')
            ->where(['msg.user_mark'=>implode('-',$arr)])
            ->field('user1.id user_id,user1.nickname user_nickname,user1.avatar_url user_avatar_url')
            ->field('msg.message')
            ->field('msg.create_time')
            ->order('msg.id','desc')
            ->limit($start,$length)
            ->select()->toArray();
    }
    public function add($userId,$peerUserId,$msg)
    {
        $userIds = [$userId,$peerUserId];
        sort($userIds);
        $this->insert([
            'user_mark' => implode('-',$userIds),
            'user_id' => $userId,
            'peer_user_id' => $peerUserId,
            'message'   => $msg,
            'status'    => 0,
            'create_time' => time(),
        ]);
    }
}
