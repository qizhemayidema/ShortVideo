<?php

namespace app\common\model;

use think\Model;

class Feedback extends Model
{
    public function add($user_id,$comment)
    {
        $this->insert([
            'user_id'   => $user_id,
            'comment'   => $comment,
            'create_time' => time(),
        ]);

        return $this->getLastInsID();
    }
}
