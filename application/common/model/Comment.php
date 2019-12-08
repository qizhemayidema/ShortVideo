<?php

namespace app\common\model;

use app\common\model\impl\ShowImpl;
use app\common\typeCode\history\CommentLike;
use app\common\typeCode\impl\CommentImpl;
use think\Model;
use think\Request;

class Comment extends Model implements ShowImpl
{
    /**
     * 获取列表
     * @param CommentImpl $type 类型
     * @param $start int 从哪开始
     * @param $length int 长度多少
     * @param null $object_id 查询对象的id 例如 某视频的评论
     * @param bool $all 是否查询所有 如果false则筛掉冻结的评论
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(CommentImpl $type,$start,$length,$object_id = null,$login_user_id = 0,$all = true)
    {
        if ($all){
            $handler = $this->backgroundShowData();
            $handler2 =  $this->newInstance()->backgroundShowData();
            $childHandler = $this->backgroundShowData('comment1');
        }else{
            $handler = $this->receptionShowData();
            $handler2 = $this->newInstance()->receptionShowData();
            $childHandler = $this->receptionShowData('comment1');
        }

        $historyType = new CommentLike();

        $handler = $object_id ? $handler->where(['public_id'=>$object_id]) : $handler;
        $handler2 = $object_id ? $handler2->where(['public_id'=>$object_id]) : $handler2;

        //根据逻辑 查出5条评论前五的
        $topNum = 5;

        //热点评论
        $hot = $handler->alias('comment')
            ->leftJoin('history','history.user_id = '.$login_user_id.' and history.object_id = comment.id and history.type = '.$historyType->getType())
            ->where(['comment.type'=>$type->getCommentType(),'comment.top_id'=>0])
            ->order('comment.like_sum','desc')
            ->field('comment.create_time,comment.avatar_url,comment.id,comment.user_id,comment.nickname,comment.comment,comment.like_sum,comment.is_show,comment.comment_sum,history.create_time is_like')
            ->limit(0,$topNum)
            ->select()->toArray();

        $hotIds = array_column($hot,'id');
        if($start == 0){

            $data = $handler2->alias('comment')
                ->leftJoin('history','history.user_id = '.$login_user_id.' and history.object_id = comment.id and history.type = '.$historyType->getType())
                ->where(['comment.type'=>$type->getCommentType(),'comment.top_id'=>0])
                ->whereNotIn('comment.id',$hotIds)
                ->field('comment.create_time,comment.avatar_url,comment.id,comment.user_id,comment.nickname,comment.comment,comment.like_sum,comment.is_show,comment.comment_sum,history.create_time is_like')
                ->limit(0,$length - $topNum)
                ->select()->toArray();

            $data = array_merge($hot,$data);
            $ids = array_column($data,'id');

            $child = $childHandler->alias('comment1')
                ->leftJoin('comment comment2','comment1.top_id = comment2.top_id and comment1.id < comment2.id')
                ->field('comment1.id,comment1.top_id,comment1.user_id,comment1.nickname,comment1.comment,comment1.like_sum,comment1.is_show,comment1.reply_nickname,comment1.reply_user_id,comment1.like_sum,comment1.create_time')
                ->whereIn('comment1.top_id',$ids)
                ->where('comment2.id IS NULL')
                ->order('comment1.id','desc')
                ->select()->toArray();
        }else{

            $data = $handler2->alias('comment')
                ->leftJoin('history','history.user_id = '.$login_user_id.' and history.object_id = comment.id and history.type = '.$historyType->getType())
                ->whereNotIn('comment.id',$hotIds)
                ->where(['comment.type'=>$type->getCommentType(),'comment.top_id'=>0])
                ->field('comment.create_time,comment.avatar_url,comment.id,comment.user_id,comment.nickname,comment.comment,comment.like_sum,comment.is_show,comment.comment_sum,history.create_time is_like')
                ->limit($start,$length)
//                ->buildSql(true);
                ->select()->toArray();

            $ids = array_column($data,'id');

            $child = $childHandler->alias('comment1')
                ->leftJoin('comment comment2','comment1.top_id = comment2.top_id and comment1.id < comment2.id')
                ->field('comment1.id,comment1.top_id,comment1.user_id,comment1.nickname,comment1.comment,comment1.like_sum,comment1.is_show,comment1.reply_nickname,comment1.reply_user_id,comment1.like_sum,comment1.create_time')
                ->whereIn('comment1.top_id',$ids)
                ->where('comment2.id IS NULL')
                ->order('comment1.id','desc')
                ->select()->toArray();
        }



//
//        $sql = "select
//                from {$tableName}
//                where (
//                   select count(*) from {$tableName} as f
//                   where f.top_id = base_comment.top_id and f.create_time <= base_comment.create_time
//                ) <= 1 and top_id in(".implode(',',$ids).") {$childWhere};";
//
//        $child = $this->query($sql);

        foreach ($data as $key => $value){
            foreach ($child as $key2 => $value2){
                if ($value2['top_id'] == $value['id'])
                $data[$key]['child'][] = $value2;
            }
            if (!isset($data[$key]['child'])) $data[$key]['child'] = [];
        }
        return $data;
    }

    public function getReplyList(CommentImpl $type,$start,$length,$comment_id)
    {
        $list = $this->receptionShowData()
            ->where(['top_id'=>$comment_id,'type'=>$type->getCommentType()])
            ->field('id,user_id,nickname,avatar_url,comment,reply_nickname,reply_user_id,create_time')
            ->limit($start,$length)
            ->select();

        return $list->toArray();

    }

    public function UpdateStatus($id)
    {
        $data = $this->find($id);

        $new_status = $data->is_show ? 0 : 1;

        $this->where(['id'=>$id])->update(['is_show'=>$new_status]);
    }

    public function backgroundShowData(string $alias = '')
    {
        return $this;
    }

    public function receptionShowData(string $alias = '')
    {
        $is_show = $alias ? $alias.'.is_show' : 'is_show';
        return $this->where([
            $is_show => 1,
        ]);
    }
}