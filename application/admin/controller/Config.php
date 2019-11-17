<?php

namespace app\admin\controller;

use app\common\lib\Upload;
use think\Controller;
use think\Request;

class Config extends Base
{
    public function index()
    {
        $config = $this->getConfig('*');

        $this->assign('config',$config);
        return $this->fetch();
    }

    public function save(Request $request)
    {
        $post = $request->post();

        $data = [
            'assignment_score' => [
                'first_share_friends'   => $post['first_share_friends'],
                'first_share_friends_round'   => $post['first_share_friends_round'],
                'first_comment'   => $post['first_comment'],
                'first_focus'   => $post['first_focus'],
                'everyday_take_video'   => $post['everyday_take_video'],
            ],
            'user_auth_score' => $post['user_auth_score'],
            'take_video_auth' => $post['take_video_auth'],
            'max_length_of_video_sec' => $post['max_length_of_video_sec'],
            'start_page' => $post['start_page'],
            'privacy_policy' => $post['privacy_policy'],
            'use_clause' => $post['use_clause'],
            'about_us' => $post['about_us'],
            'register_policy' => $post['register_policy'],
        ];


        file_put_contents(self::WEBSITE_CONFIG_PATH,json_encode($data));

        return json(['code'=>1,'msg'=>'success']);
    }

    public function uploadConfigStartPage()
    {
        return (new Upload())->uploadOnePic('config/start_page/','file');
    }
}
