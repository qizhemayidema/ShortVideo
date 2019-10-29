<?php

namespace app\common\model;

use think\Model;

class User extends Model
{
    public function UpdateStatus($user_id)
    {
        $user = $this->where(['id'=>$user_id])->find();

        $new_status = $user->status ? 0 : 1;

        $this->where(['id'=>$user_id])->update(['status'=>$new_status]);
    }
}
