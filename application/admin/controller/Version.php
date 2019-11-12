<?php

namespace app\admin\controller;

use app\common\lib\Upload;
use think\Controller;
use think\Request;
use app\common\model\Version as VersionModel;
use think\Validate;

class Version extends Base
{
    public function index()
    {
        $list = (new VersionModel())->order('id','desc')->paginate(15);

        $this->assign('list',$list);

        return $this->fetch();
        
    }

    public function add()
    {
        return $this->fetch();
    }

    public function save(Request $request)
    {
        $post = $request->post();

        $rules = [
            'version'   => 'require',
            'android_source' => 'require',
            'update_desc'   => 'require',
        ];

        $messages = [
            'version.require'   => '必须填写版本号',
            'android_source.require' => '必须上传apk资源文件',
            'update_desc.require' => '更新内容必须填写',
        ];

        $validate = new Validate($rules,$messages);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $insert = [
            'version'   => $post['version'],
            'android_source' => $post['android_source'],
            'create_time' => time(),
            'update_desc' => $post['update_desc'],
            'is_compel' => $post['is_compel'],
        ];

        (new \app\common\model\Version())->insert($insert);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function edit(Request $request)
    {
       $id = $request->param('id');

       $data = (new \app\common\model\Version())->find($id);

       $this->assign('data',$data);

       return $this->fetch();
    }

    public function update(Request $request)
    {
        $post = $request->post();

        $rules = [
            'version'   => 'require',
            'android_source' => 'require',
            'update_desc'   => 'require',
        ];

        $messages = [
            'version.require'   => '必须填写版本号',
            'android_source.require' => '必须上传apk资源文件',
            'update_desc.require' => '更新内容必须填写',
        ];

        $validate = new Validate($rules,$messages);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $update = [
            'version'   => $post['version'],
            'android_source' => $post['android_source'],
            'update_desc' => $post['update_desc'],
            'is_compel' => $post['is_compel'],
        ];

        (new \app\common\model\Version())->where(['id'=>$post['id']])->update($update);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function delete(Request $request)
    {
        $id = $request->post('id');

        (new VersionModel())->where(['id'=>$id])->delete();

        return json(['code'=>1,'msg'=>'success']);

    }

    public function upload()
    {
        return (new Upload())->uploadOneApk('apk/','file');
    }


    public function download(Request $request)
    {
        $file_dir = '.'.$request->get('source');    // 下载文件存放目录

        echo $file_dir;
        // 检查文件是否存在
        if (! file_exists($file_dir) ) {
            $this->error('文件未找到');
        }else{
            // 打开文件
            $file1 = fopen($file_dir, "r");
            // 输入文件标签
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length:".filesize($file_dir));
            Header("Content-Disposition: attachment;filename=" . $file_dir);
            ob_clean();     // 重点！！！
            flush();        // 重点！！！！可以清除文件中多余的路径名以及解决乱码的问题：
            //输出文件内容
            //读取文件内容并直接输出到浏览器
            echo fread($file1, filesize($file_dir));
            fclose($file1);
            exit();
        }
    }

}
