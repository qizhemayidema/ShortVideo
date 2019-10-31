<?php

namespace app\common\model;

use app\common\model\impl\ShowImpl;
use think\Model;

class User extends Model implements ShowImpl
{
    public function UpdateStatus($user_id)
    {
        $user = $this->where(['id'=>$user_id])->find();

        $new_status = $user->status ? 0 : 1;

        $this->where(['id'=>$user_id])->update(['status'=>$new_status]);
    }

    public function backgroundShowData(string $alias = '')
    {
        return $this;
    }

    public function receptionShowData(string $alias = '')
    {
        // TODO: Implement receptionShowData() method.
        $status = $alias ? $alias.'.status' : 'status';
        return $this->where([
            $status => 0,
        ]);
    }
}
