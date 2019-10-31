<?php

namespace app\common\model;

use app\common\typeCode\impl\MessageImpl;
use think\Model;

class Message extends Model
{
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
