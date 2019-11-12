<?php

namespace app\common\model;

use app\common\typeCode\impl\MessageImpl;
use think\Model;

class Message extends Model
{
    public function seen(MessageImpl $messageImpl,$user_id)
    {
        $this->where(['user_id'=>$user_id,'type'=>$messageImpl->getType()])
            ->where(['status'=>0])->update(['status'=>1]);
    }

    public function send(MessageImpl $messageImpl,$user_id,$comment = '')
    {
        $this->insert([
            'user_id'   => $user_id,
            'type'      => $messageImpl->getType(),
            'content'   => $comment,
            'status'    => 0,
            'create_time' => time(),
        ]);

        return true;
    }
}
