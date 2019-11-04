<?php

namespace app\admin\controller;

use think\Request;
use app\common\model\Category as CategoryModel;
use app\common\model\Video as VideoModel;
use app\common\typeCode\cate\Video as VideoCateType;
use think\Validate;

class VideoCate extends Base
{
    public function index()
    {

        $categoryModel = new CategoryModel();

        $cate = $categoryModel->getList((new VideoCateType()));

        $this->assign('cate',$cate);

        return $this->fetch();

    }

    public function add()
    {

        $categoryModel = new CategoryModel();

        $cate = $categoryModel->getList((new VideoCateType()));

        $this->assign('cate',$cate);

        return $this->fetch();

    }

    public function save(Request $request)
    {
        $data = $request->post();

        $rules = [
            'name'  => 'require|max:60',
            'order_num' => 'require|between:0,999',
            'p_id'      => 'require',
            '__token__'     => 'token',
        ];

        $messages = [
            'name.require'  => '名称必须填写',
            'name.max'      => '名称最大60个字符',
            'order_num.require' => '排序必须填写',
            'order_num.between' => '排序数字最小为0,最大为999',
            'p_id.require'  => '所属分类必须填写',
            '__token__.token'   => '不能重复提交'
        ];

        $validate = new Validate($rules,$messages);

        if (!$validate->check($data)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $videoType = (new VideoCateType());
        $insert = [
            'type'  => $videoType->getCateType(),
            'name'  => $data['name'],
            'p_id'  => $data['p_id'],
            'order_num' => $data['order_num'],
        ];

        $cateModel = new CategoryModel();
        $cateModel->insert($insert);
        $cateModel->clear($videoType);
        return json(['code'=>1,'msg'=>'success']);
    }

    public function edit(Request $request)
    {
        $cate_id = $request->param('cate_id');
        $cate = CategoryModel::find($cate_id);

        $categoryModel = new CategoryModel();

        $list = $categoryModel->getList((new VideoCateType()));

        $this->assign('list',$list);

        $this->assign('cate',$cate);

        return $this->fetch();
    }

    public function update(Request $request)
    {
        $data = $request->post();

        $rules = [
            'id'        => 'require',
            'name'  => 'require|max:60',
            'order_num' => 'require|between:0,999',
            'p_id'  => 'require',
            '__token__'     => 'token',
        ];

        $messages = [
            'name.require'  => '名称必须填写',
            'name.max'      => '名称最大60个字符',
            'order_num.require' => '排序必须填写',
            'order_num.between' => '排序数字最小为0,最大为999',
            'p_id.require'  => '所属分类必须填写',
            '__token__.token'   => '不能重复提交'
        ];

        $validate = new Validate($rules,$messages);
        if (!$validate->check($data)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }
        $update = [
            'name'  => $data['name'],
            'order_num' => $data['order_num'],
            'p_id'  => $data['p_id'],
        ];

        $cateModel = new CategoryModel();

        $cateModel->where(['id'=>$data['id']])->update($update);

        $cateModel->clear((new VideoCateType()));

        return json(['code'=>1,'msg'=>'success']);

    }

    public function delete(Request $request)
    {
        $cate_id = $request->post('cate_id');

        $is_exists = (new VideoModel())->where(['cate_id'=>$cate_id])->find();

        if ($is_exists) return json(['code'=>0,'msg'=>'有视频正在使用此分类']);

        $cateModel = new CategoryModel();

        $videoType = new VideoCateType();

        $cateModel->where(['id'=>$cate_id])->delete();

        $cateModel->clear($videoType);

        return json(['code'=>1,'msg'=>'success']);

    }
}
