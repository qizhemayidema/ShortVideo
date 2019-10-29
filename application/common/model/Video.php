<?php

namespace app\common\model;

use think\Model;

class Video extends Model implements ShowImpl
{
    public function receptionShowData($alias = '')
    {
        $status = $alias ? $alias.'.status' : 'status';
        $is_delete = $alias ? $alias.'.delete_time' : 'delete_time';
        return $this->where([
            $status    => 2,
            $is_delete => 0,
        ]);
    }

    public function backgroundShowData($alias = '')
    {
        $is_delete = $alias ? $alias.'.delete_time' : 'delete_time';

        return $this->where([
            $is_delete => 0,
        ]);
    }
}