<?php

namespace app\common\model;

use app\common\typeCode\history\VideoCollect;
use app\common\typeCode\history\VideoLike;
use app\common\typeCode\impl\HistoryImpl;
use think\Model;

class History extends Model
{
    //获取列表
    public function getList(HistoryImpl $historyImpl,$user_id,$start,$length)
    {
        $type = $historyImpl->getType();
        if ($historyImpl instanceof VideoCollect){
            return $this->alias('history')
                ->join('video','history.object_id = video.id and history.user_id = '.$user_id)
                ->where(['history.type'=>$type])
                ->field('video.*,history.create_time collect_time')
                ->order('history.create_time','desc')
                ->limit($start,$length)
                ->select()->toArray();
        }elseif ($historyImpl instanceof VideoLike){
            return $this->alias('history')
                ->join('video video','history.object_id = video.id and video.user_id ='.$user_id)
                ->join('user user','user.id = history.user_id')
                ->where('history.type','=',$historyImpl->getType())
                ->field('video.id video_id,user.id user_id,video.video_pic')
                ->field('user.nickname,user.avatar_url,user.sex,history.create_time')
                ->order('history.create_time','desc')
                ->limit($start,$length)
                ->select()->toArray();
        }else{
            return $this->where(['user_id'=>$user_id,'type'=>$type])->limit($start,$length)->select()->toArray();
        }
    }
    //是否存在此条记录
    public function existsHistory(HistoryImpl $historyImpl,$user_id,$object_id = 0)
    {
        $handler = $this->where(['type'=>$historyImpl->getType(),'user_id'=>$user_id]);

        if ($object_id) $handler = $handler->where(['object_id'=>$object_id]);

         return  $handler->find();
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

    //删除一条历史记录
    public function rm(HistoryImpl $historyImpl,$user_id,$object_id)
    {
        return $this->where([
            'type'  => $historyImpl->getType(),
            'user_id' => $user_id,
            'object_id' => $object_id,
        ])->delete();

    }
}
