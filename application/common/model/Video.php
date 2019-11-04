<?php

namespace app\common\model;

use app\common\model\impl\ShowImpl;
use think\Model;

class Video extends Model implements ShowImpl
{
    //检查前台是否可以看到此条数据
    public function checkShowStatus($id)
    {
       $data = $this->receptionShowData()->where(['id'=>$id])->find();

       return $data ?? false;
    }

    //入库
    public function add($user_id,$cate_id,$title,$pic,$source_url)
    {
        $insert = [
            'cate_id'   => $cate_id,
            'user_id'   => $user_id,
            'title'     => $title,
            'video_pic' => $pic,
            'source_url'=> $source_url,
            'status'    => 1,
            'create_time' => time(),
        ];

        $this->insert($insert);

        $id = $this->getLastInsID();

        (new User())->where(['id'=>$user_id])->setInc('works_sum');

        return $id;
    }

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