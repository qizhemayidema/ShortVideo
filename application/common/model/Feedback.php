<?php

namespace app\common\model;

use think\Model;

class Feedback extends Model
{
    public function add($user_id,$comment,$pics = '')
    {
        $this->insert([
            'user_id'   => $user_id,
            'comment'   => $comment,
            'pics'      => $pics,
            'create_time' => time(),
        ]);

        return $this->getLastInsID();
    }
}
