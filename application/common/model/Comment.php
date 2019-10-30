<?php

namespace app\common\model;

use app\common\model\impl\ShowImpl;
use app\common\typeCode\impl\CommentImpl;
use think\Model;

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
    public function getList(CommentImpl $type,$start,$length,$object_id = null,$all = true)
    {
        $tableName = $this->getTable();

        if ($all){
            $handler = $this->backgroundShowData();
            $childWhere =  '';

        }else{
            $handler = $this->receptionShowData();
            $childWhere = 'and f.is_show = 1';
        }

        $handler = $object_id ? $handler->where(['public_id'=>$object_id]) : $handler;

        $data = $handler->where(['type'=>$type->getCommentType(),'top_id'=>0])->limit($start,$length)
            ->select()->toArray();

        $ids = array_column($data,'id');


        $sql = "select *
                from {$tableName}
                where (
                   select count(*) from {$tableName} as f
                   where f.top_id = base_comment.top_id and f.create_time <= base_comment.create_time
                ) <= 1 and top_id in(".implode(',',$ids).") {$childWhere};";

        $child = $this->query($sql);

        foreach ($data as $key => $value){
            foreach ($child as $key2 => $value2){
                if ($value2['top_id'] == $value['id'])
                $data[$key]['child'][] = $value2;
            }
            if (!isset($data[$key]['child'])) $data[$key]['child'] = [];
        }
        return $data;
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