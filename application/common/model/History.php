<?php

namespace app\common\model;

use app\common\typeCode\impl\HistoryImpl;
use think\Model;

class History extends Model
{
    //是否存在此条记录
    public function existsHistory(HistoryImpl $historyImpl,$user_id,$object_id)
    {
        return $this->where(['type'=>$historyImpl->getType(),'user_id'=>$user_id])
            ->where(['object_id'=>$object_id])->find();
    }

    //添加一条历史记录
    public function add(HistoryImpl $historyImpl,$user_id,$object_id)
    {
        $this->insert([
            'type'  => $historyImpl->getType(),
            'user_id' => $user_id,
            'object_id' => $object_id,
            'create_time' => time(),
        ]);

        return $this->getLastInsID();
    }
}
