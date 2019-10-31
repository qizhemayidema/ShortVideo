<?php

namespace app\common\model;

use think\Model;

class Both extends Model
{
    //某人对某人是否存在关系
    public function existsBoth($from_user_id,$to_user_id)
    {
        return $this->where([
            'from_user_id' => $from_user_id,
            'to_user_id'   => $to_user_id
        ])->find();

    }
}
