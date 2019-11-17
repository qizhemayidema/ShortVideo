<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class User extends Base
{
    public function index(Request $request)
    {
        $search = $request->get('search') ?? '';

        $user = (new \app\common\model\User());

        if ($search) $user = $user->whereLike('phone', $search)->whereOr('nickname', 'like', '%' . $search . '%');
        $user = $user->order('id', 'desc')
            ->field('auth_id,id,status,create_time,nickname,avatar_url')->paginate(15, false, ['query' => request()->param()]);


        $this->assign('user', $user);
        $this->assign('search', $search);

        return $this->fetch();
    }

    public function changeStatus(Request $request)
    {
        $user_id = $request->param('user_id');
        (new \app\common\model\User())->UpdateStatus($user_id);
        return json(['code' => 1, 'msg' => 'success']);
    }

    public function info(Request $request)
    {
        $user = (new \app\common\model\User())->find($request->param('user_id'));

        $this->assign('user', $user);

        return $this->fetch();
    }
}
