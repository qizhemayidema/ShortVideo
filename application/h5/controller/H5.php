<?php

namespace app\h5\controller;

use app\apiv1\controller\Feedback;
use app\common\lib\Upload;
use app\common\model\UserAuth;
use think\Controller;
use think\Request;
use think\Validate;
use app\common\model\Category as CateModel;
use app\common\model\Video as VideoModel;
use app\common\model\Complain as ComplainModel;

class H5 extends Base
{

    public function complain(Request $request)
    {

        if (!$this->existsToken()){
            echo '请先登录账号';die;
        }

        $user = $this->userInfo;

        try{
            $videoId = $request->get('video_id');

            $video = (new \app\common\model\Video())->find($videoId);

            $authorName = (new \app\common\model\User())->where(['id'=>$video['user_id']])->value('nickname');

            //分类
            $cateType = new \app\common\typeCode\cate\complain\Video();

            $cate = (new \app\common\model\Category())->getList($cateType);


            $this->assign('video',$video);

            $this->assign('author',$authorName);

            $this->assign('cate',$cate);

            $this->assign('token',$this->existsToken());

            return $this->fetch();
        }catch (\Exception $e){
            echo $e->getMessage();die;
        }

    }

    public function complainSave(Request $request)
    {
        $post = $request->post();

        $user = $this->userInfo;

        $rules = [
            "token"     => 'require',
            'cate_id'   => 'require',
            'content'   => 'require|max:127',
            'video_id'  => 'require',
        ];

        $messages = [
            "token.require"     => 'token非法',
            'cate_id.require'   => '必须选择分类',
            'content.require'   => '原因不能为空',
            'content.max'       => '请限制在128字以内',
            'video_id.require'  => '请求错误',
        ];

        $validate = new Validate($rules,$messages);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        $cateModel = new CateModel();
        $videoModel = new VideoModel();
        $complainModel = new ComplainModel();
        $videoType = new \app\common\typeCode\cate\complain\Video();

        //验证分类
        $cateExists = $cateModel->where(['type'=>$videoType->getCateType()])->find($post['cate_id']);
        if (!$cateExists) return json(['code'=>0,'msg'=>'请求错误']);

        //验证视频
        if (!$videoModel->field('id')->find($post['video_id'])){
            return json(['code'=>0,'msg'=>'请求错误']);
        }

        //组装数据
        $insert = [
            'user_id'   => $user->id,
            'object_id' => $post['video_id'],
            'type'      => $videoType->getType(),
            'cate_id'   => $post['cate_id'],
            'cate_name' => $cateExists['name'],
            'content'   => $post['content'],
            'create_time' => time(),
        ];
        $complainModel->insert($insert);

        return json(['code'=>1,'msg'=>'success']);

    }

    public function auth(Request $request)
    {
         $user = $this->userInfo;


        //判断用户是否已经认证过
        if ($user->auth_id != 0){
            return '您已经认证过,无法再次认证';
        }


        //判断用户是否可以认证 分数
        $authScore = $this->getConfig('user_auth_score');
        if ($user->score < $authScore){
            return '学分不够,无法认证';
        }

        $this->assign('token',$this->existsToken());

        return $this->fetch();
    }

    //用户认证 个人
    public function authPersonal(Request $request)
    {
        $user = $this->userInfo;

        $post = $request->post();

        //判断用户是否已经认证过
        if ($user->auth_id != 0){
            return json(['code'=>0,'msg'=>'您已经认证过,无法再次认证']);
        }

        //判断用户是否可以认证 分数
        $authScore = $this->getConfig('user_auth_score');
        if ($user->score < $authScore){
            return json(['code'=>0,'msg'=>'学分不够,无法认证']);
        }

        $type = 1;
        $authModel = new UserAuth();

        //判断用户是否已经申请过
        if($authModel->where(['user_id'=>$user->id])->find()){
            return json(['code'=>0,'msg'=>'您已经提交过,请耐心等待审核']);
        }

        $rules = [
            'name' => 'require|max:18',
            'card_number' => 'require|max:18',
            'teacher_number' => 'require|max:40',
            'good_at_cate' => 'require|max:20',
            'desc' => 'require|max:30',
            'card_pic' => 'require',
            'honor_pic' => 'require',
        ];

        $messages = [
            'name.require' => '姓名必须填写',
            'name.max' => '姓名最大长度为18',
            'card_number.require' => '身份证号必须填写',
            'card_number.max' => '身份证号最大长度为18',
            'teacher_number.require' => '教师编号必须填写',
            'teacher_number.max' => '教师编号最大长度为18',
            'good_at_cate.require' => '擅长分类必须选择',
            'good_at_cate.max' => '擅长分类最大长度为18',
            'desc.require' => '自我介绍必须填写',
            'desc.max' => '自我介绍最大长度为18',
            'card_pic.require' => '身份证照片必须上传',
            'honor_pic.require' => '荣誉照片必须上传',
            'card_pic.max' => '非法',
            'honor_pic.max' => '非法2',
        ];

        $validate = new Validate($rules,$messages);
        if (!$validate->check($post)){
            return json(['code'=>1,'msg'=>$validate->getError()]);
        }
        $upload = new Upload();
        $card_pic = $upload->uploadBase64Pic($post['card_pic'],'userAuth/');
        if ($card_pic['code'] == 0) return json($card_pic);
        $honor_pic = $upload->uploadBase64Pic($post['honor_pic'],'userAuth/');
        if ($honor_pic['code'] == 0) return json($honor_pic);
        $insert = [
            'user_id' => $user->id,
            'type'  => $type,
            'data' => json_encode([
                'name'  => $post['name'],
                'card_number'  => $post['card_number'],
                'teacher_number'  => $post['teacher_number'],
                'good_at_cate'  => $post['good_at_cate'],
                'desc'  => $post['desc'],
                'card_pic'  => $card_pic['msg'],
                'honor_pic'  => $honor_pic['msg'],
            ],256)
        ];

        $authModel->insert($insert);

        return json(['code'=>1,'msg'=>'申请成功,请耐心等待']);
    }

    //用户认证 企业
    public function authCompany(Request $request)
    {
        $user = $this->userInfo;

        $post = $request->post();

        //判断用户是否已经认证过
        if ($user->auth_id != 0){
            return json(['code'=>0,'msg'=>'您已经认证过,无法再次认证']);
        }

        //判断用户是否可以认证 分数
        $authScore = $this->getConfig('user_auth_score');
        if ($user->score < $authScore){
            return json(['code'=>0,'msg'=>'学分不够,无法认证']);
        }

        $type = 2;
        $authModel = new UserAuth();

        //判断用户是否已经申请过
        if($authModel->where(['user_id'=>$user->id])->find()){
            return json(['code'=>0,'msg'=>'您已经提交过,请耐心等待审核']);
        }

        $rules = [
            'name' => 'require|max:18',
            'alias_name' => 'require|max:18',
            'code' => 'require|max:18',
            'licence_code' => 'require|max:32',
            'location' => 'require|max:64',
            'good_at_cate' => 'require|max:20',
            'desc' => 'require|max:30',
            'card_pic' => 'require',
            'school_pic' => 'require',
        ];

        $messages = [
            'name.require' => '机构名称必须填写',
            'name.max' => '机构名称最大长度为18',
            'alias_name.require' => '学校简称必须填写',
            'alias_name.max' => '学校建成最大长度为18',
            'code.require' => '统一编码必须填写',
            'code.max' => '统一编码最大长度为18',
            'licence_code.require' => '办学许可证必须填写',
            'licence_code.max' => '办学许可证最大长度为18',
            'location.require' => '机构位置必须填写',
            'location.max' => '机构位置最大长度为64',
            'goods_at_cate.require' => '擅长分类必须填写',
            'goods_at_cate.max' => '擅长分类最大长度为20',
            'desc.require' => '简短介绍必须填写',
            'desc.max' => '简短介绍最大长度为30',
            'card_pic.require' => '营业执照必须上传',
            'card_pic.max' => 'err1',
            'school_pic.require' => '学校照片必须上传',
            'school_pic.max' => 'err2',
        ];

        $validate = new Validate($rules,$messages);
        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }
        $upload = new Upload();
        $card_pic = $upload->uploadBase64Pic($post['card_pic'],'userAuth/');
        if ($card_pic['code'] == 0) return json($card_pic);
        $school_pic = $upload->uploadBase64Pic($post['school_pic'],'userAuth/');
        if ($school_pic['code'] == 0) return json($school_pic);
        $insert = [
            'user_id' => $user->id,
            'type'  => $type,
            'data'  => json_encode([
                'name'  => $post['name'],
                'alias_name'  => $post['alias_name'],
                'code'  => $post['code'],
                'licence_code'  => $post['licence_code'],
                'good_at_cate'  => $post['good_at_cate'],
                'desc'  => $post['desc'],
                'card_pic'  => $card_pic['msg'],
                'location'  => $post['location'],
                'school_pic'  => $school_pic['msg'],
            ],256)
        ];

        $authModel->insert($insert);

        return json(['code'=>1,'msg'=>'申请成功,请耐心等待']);
    }

    public function registerPolicy(Request $request)
    {
        $text = $this->getConfig('register_policy');

        $this->assign('text',$text);

        return $this->fetch();

    }
    public function privacyPolicy(Request $request)
    {
        $text = $this->getConfig('privacy_policy');

        $this->assign('text',$text);

        return $this->fetch();
    }

    public function feedback(Request $request)
    {
        $user = $this->UserInfo;

        $this->assign('token',$this->existsToken());

        return $this->fetch();
    }

    public function feedbackSave(Request $request)
    {
        $post = $request->post();

        $user = $this->userInfo;

        $rules = [
            'comment'   => 'require',
        ];

        $messages = [
            'comment.require' => '内容必须填写',
        ];

        $validate = new Validate($rules,$messages);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }
        if (isset($post['pics']) && count($post['pics']) > 10) return json(['code'=>0,'msg'=>'上传图片过多']);

        if (isset($post['pics'])){
            $pics = [];
            $upload = new Upload();
            foreach($post['pics'] as $key => $value){
                $temp = $upload->uploadBase64Pic($value,'feedback/');
                if ($temp['code'] == 0) return json($temp);
                $pics[] = $temp['msg'];
            }
            $picStr = implode(',',$pics);
        }else{
            $picStr = '';
        }

        (new \app\common\model\Feedback())->add($user->id,$post['comment'],$picStr);

        return json(['code'=>1,'msg'=>'success']);
    }

    public function aboutOur()
    {
        $text = $this->getConfig('about_us');

        $this->assign('text',$text);

        return $this->fetch();
    }
}
