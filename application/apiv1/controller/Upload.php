<?php

namespace app\apiV1\controller;


use function Composer\Autoload\includeFile;
use think\Controller;
use think\Request;

class Upload extends Base
{
    protected $user;

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub

        $this->user = $this->userInfo;
    }

    public function img()
    {
        return (new \app\common\lib\Upload())->uploadOnePic('/','file');
    }

    public function base64Img(Request $request)
    {
        $base64 = $request->post('file');
        if(!$base64) return json(['code'=>0,'msg'=>'error']);
        return json((new \app\common\lib\Upload())->uploadBase64Pic($base64,'/'));

    }
}