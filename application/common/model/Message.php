<?php

namespace app\common\model;

use think\Model;

class Message extends Model
{
    public function send($user_id,$type,$comment = '')
    {
        $this->insert([
            'user_id'   => $user_id,
            'type'      => $type,
            'content'   => $comment,
            'status'    => 0,
            'create_time' => time(),
        ]);

        return true;
    }
}
