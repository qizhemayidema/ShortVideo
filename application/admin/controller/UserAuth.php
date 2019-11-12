<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class UserAuth extends Base
{
    public function index()
    {
        $list = (new \app\common\model\UserAuth())->alias('auth')
            ->join('user user','user.id = auth.user_id')
            ->where(['auth.status'=>0])
            ->field('auth.*,user.nickname,user.auth_type')
            ->order('auth.id','desc')
            ->paginate(15);

        $this->assign('list',$list);

        return $this->fetch();

    }

    public function info(Request $request)
    {
        $id = $request->param('id');

        $data = (new \app\common\model\UserAuth())->where(['id'=>$id])->find();

        $info = json_decode($data['data'],256);

        $this->assign('info',$info);

        if ($data['type'] == 1){
            return $this->fetch('personalInfo');
        }else{
            return $this->fetch('companyInfo');
        }
    }

    public function auth(Request $request)
    {
        $status = $request->post('status'); // 1 拒绝 2 通过

        $id = $request->post('id');

        $authModel = new \app\common\model\UserAuth();

        $auth = $authModel->find($id);

        if ($status == 1){
            $authModel->where(['id'=>$id])->delete();
        }else{
            (new \app\common\model\User())->where(['id'=>$auth->user_id])
                ->update([
                    'auth_type' => $auth->type,
                    'auth_id'   => $auth->id,
                ]);
            $authModel->where(['id'=>$id])->update(['status'=>1]);

        }

        return json(['code'=>1,'msg'=>'操作成功']);
    }
}
