<?php

namespace app\admin\controller;

use app\common\model\Manager;
use think\Controller;
use think\Request;
use think\Session;
use think\Validate;

class Login extends Controller
{
    public function index()
    {
        if (\think\facade\Session::get('admin')){
            return redirect('/admin/index');
        }
        return $this->fetch();
    }

    public function check(Request $request)
    {
        $data =$request->post();

        $rules = [
            'username'  => 'require',
            'password'  => 'require',
            'captcha'   => 'require|captcha',
        ];
        $messages = [
            'username.require'      => '用户名必须填写',
            'password.require'      => '密码必须填写',
            'captcha.require'       => '验证码必须填写',
            'captcha.captcha'       => '验证码不正确',
        ];
        $validate = new Validate($rules,$messages);
        $result = $validate->check($data);
        if (!$result) {
            return json(['code' => 0, 'msg'=>$validate->getError()], 256);
        }

        $res = (new Manager())->where(['user_name'=>$data['username'],'password'=>md5($data['password'])])->find();
        if (!$res){
            return json(['code' => 0, 'msg'=>'账号或密码不正确'], 256);
        }
        //登陆成功
        \think\facade\Session::set('admin',$res);

        return json(['code' => 1, 'msg'=>'success'], 256);
    }

    public function logout()
    {
        \think\facade\Session::set('admin',null);
        $this->redirect('admin/Login/index');
    }
}
