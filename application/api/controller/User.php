<?php

namespace app\api\controller;

use app\common\model\UserAuth as UserAuthModel;
use app\common\model\History as HistoryModel;
use think\Request;
use think\Validate;
use app\common\model\Both as BothModel;
use app\common\model\User as UserModel;
use app\common\model\Message as MessageModel;
use app\common\model\Video as VideoModel;
use app\common\typeCode\message\NewFans as NewFansModel;
use app\common\typeCode\message\PrivateMessage as PrivateMessageMessage;
use app\common\typeCode\history\VideoCollect as VideoCollectHistory;
use app\common\typeCode\history\FocusUser as FocusUserHistory;
use app\common\typeCode\history\VideoLike as VideoLikeHistory;

class User extends Base
{
    //用户关注的列表
    public function focusList(Request $request)
    {
        $get = $request->get();

        $rules = [
            'page' => 'require',
            'length' => 'require',
            'user_id' => 'require',
        ];

        $message = [
            'page.require' => 'token不合法',
            'length.require' => 'length不合法',
            'user_id.require' => 'user_id不合法'
        ];

        $validate = new Validate($rules, $message);

        if (!$validate->check($get)) return json(['code' => 0, 'msg' => $validate->getError()]);

        if (isset($get['token'])) {
            $loginUserId = $this->userInfo->id;
        } else {
            $loginUserId = 0;
        }

        $userModel = new UserModel();
        $bothModel = new BothModel();


        $user = $userModel->receptionShowData()->find($get['user_id']);;

        if (!$user) return json(['code' => 0, 'msg' => '用户不存在或已被封禁,无法查看']);

        $start = $get['page'] * $get['length'] - $get['length'];

        $bothList = $bothModel->alias('both')
            ->join('user user', 'user.id = both.to_user_id and both.from_user_id = ' . $user->id)
            ->leftJoin('both bothUser', 'bothUser.from_user_id =' . $loginUserId . ' and bothUser.to_user_id = both.to_user_id')
            ->field('bothUser.create_time focus')
            ->field('user.nickname,user.avatar_url,user.id user_id,user.sex,both.create_time')
            ->limit($start, $get['length'])->select();

        return json(['code' => 1, 'msg' => 'success', 'data' => $bothList]);
    }

    //关注和取消关注接口
    public function focusSave(Request $request)
    {
        $user = $this->userInfo;

        $post = $request->post();

        $rules = [
            'token' => 'require',
            'user_id' => 'require',
        ];

        $message = [
            'token.require' => 'token不合法',
            'user_id.require' => 'user_id不合法',
        ];

        $validate = new Validate($rules, $message);

        if (!$validate->check($post)) return json(['code' => 0, 'msg' => $validate->getError()]);

        $bothModel = new BothModel();
        $userModel = new UserModel();
        $messageModel = new MessageModel();
        $newFansModel = new NewFansModel();

        $historyModel = new HistoryModel();
        $focusUserHistory = new FocusUserHistory();

        if (!$to_user = $userModel->existsUser($post['user_id']))
            return json(['code' => 0, 'msg' => '操作失误,请稍后尝试']);

        if ($post['user_id'] == $user->id)
            return json(['code' => 0, 'msg' => '自己不能关注自己哦']);


        $bothModel->startTrans();
        try {
            //判断是否已经关注
            if ($both = $bothModel->existsBoth($user->id, $post['user_id'])) {
                //取消关注 2
                $code = 2;
                //去掉关注状态
                $both->delete();

                //变更两人的数据
                $userModel->where(['id' => $user->id])->setDec('focus_sum');
                $userModel->where(['id' => $post['user_id']])->setDec('fans_sum');
            } else {
                $code = 1;  //关注 2

                //关注状态增加
                $bothModel->insert([
                    'from_user_id' => $user->id,
                    'to_user_id' => $post['user_id'],
                    'create_time' => time(),
                ]);
                //变更两人的数据
                $userModel->where(['id' => $user->id])->setInc('focus_sum');
                $userModel->where(['id' => $post['user_id']])->setInc('fans_sum');
                //判断是否是第一次关注
                if (!$historyModel->existsHistory($focusUserHistory, $user->id)) {
                    $historyModel->add($focusUserHistory, $user->id, $post['user_id']);
                    $userModel->incScore($user->id, $this->getConfig("assignment_score.first_focus"));
                }

                //发送消息
                $messageModel->send($newFansModel, $post['user_id']);
            }
            $bothModel->commit();
        } catch (\Exception $e) {
            $bothModel->rollback();
            return json(['code' => 0, 'msg' => $e->getMessage() . $e->getLine()]);
            return json(['code' => 0, 'msg' => '操作失误,请稍后再试']);
        }

        return json(['code' => $code, 'msg' => 'success']);
    }

    //粉丝列表
    public function fansList(Request $request)
    {
        $get = $request->get();

        $rules = [
            'page' => 'require',
            'length' => 'require',
            'user_id' => 'require',
        ];

        $message = [
            'page.require' => 'token不合法',
            'length.require' => 'length不合法',
            'user_id.require' => 'user_id不合法'
        ];

        $validate = new Validate($rules, $message);

        if (!$validate->check($get)) return json(['code' => 0, 'msg' => $validate->getError()]);

        if (isset($get['token'])) {
            $loginUserId = $this->userInfo->id;
        } else {
            $loginUserId = 0;
        }

        $userModel = new UserModel();
        $bothModel = new BothModel();


        $user = $userModel->receptionShowData()->find($get['user_id']);;

        if (!$user) return json(['code' => 0, 'msg' => '用户不存在或已被封禁,无法查看']);

        $start = $get['page'] * $get['length'] - $get['length'];

        $bothList = $bothModel->alias('both')
            ->join('user user', 'user.id = both.from_user_id and both.to_user_id = ' . $user->id)
            ->leftJoin('both bothUser', 'bothUser.from_user_id =' . $loginUserId . ' and bothUser.to_user_id = both.from_user_id')
            ->field('bothUser.create_time focus')
            ->field('user.nickname,user.avatar_url,user.id user_id,user.sex,both.create_time')
            ->limit($start, $get['length'])->select();

        return json(['code' => 1, 'msg' => 'success', 'data' => $bothList]);
    }

    //收藏列表
    public function collectList(Request $request)
    {
//        $user = $this->userInfo;

        $get = $request->get();

        $rules = [
            'user_id' => 'require',
            'page' => 'require|number',
            'length' => 'require|number',
        ];

        $message = [
            'page.require' => 'err1',
            'page.number' => 'err2',
            'length.require' => 'err3',
            'length.number' => 'err4',
        ];

        $validate = new Validate($rules, $message);
        if (!$validate->check($get)) return json(['code' => 0, 'msg' => $validate->getError()]);

        $historyModel = new HistoryModel();

        $start = $get['page'] * $get['length'] - $get['length'];

        $res = $historyModel->getList(new VideoCollectHistory(), $get['user_id'], $start, $get['length']);

        $return = [];

        foreach ($res as $key => $value) {
            $return[] = [
                'id' => $value['id'],
                'video_pic' => $value['video_pic'],
                'title' => $value['title'],
                'see_sum' => $value['see_sum'],
            ];
        }

        return json(['code' => 1, 'msg' => 'success', 'data' => $return]);

    }

    //作品列表
    public function videoList(Request $request)
    {

        $get = $request->get();

        $rules = [
            'user_id' => 'require',
            'page' => 'require|number',
            'length' => 'require|number',
        ];

        $message = [
            'page.require' => 'err1',
            'page.number' => 'err2',
            'length.require' => 'err3',
            'length.number' => 'err4',
        ];

        $validate = new Validate($rules, $message);
        if (!$validate->check($get)) return json(['code' => 0, 'msg' => $validate->getError()]);

        $videoModel = new VideoModel();

        $start = $get['page'] * $get['length'] - $get['length'];

        $return = $videoModel->receptionShowData()->where(['user_id' => $get['user_id']])
            ->field('video_pic,id,see_sum')
            ->order('create_time', 'desc')
            ->limit($start, $get['length'])->select()->toArray();
        return json(['code' => 1, 'msg' => 'success', 'data' => $return]);

    }

    //获得的赞
    public function likeList(Request $request)
    {
        $get = $request->get();

        $rules = [
            'user_id' => 'require',
            'page' => 'require|number',
            'length' => 'require|number',
        ];

        $message = [
            'page.require' => 'err1',
            'page.number' => 'err2',
            'length.require' => 'err3',
            'length.number' => 'err4',
        ];

        $validate = new Validate($rules, $message);
        if (!$validate->check($get)) return json(['code' => 0, 'msg' => $validate->getError()]);

        $historyModel = new HistoryModel();
        $videoLikeHistory = new VideoLikeHistory();

        $start = $get['page'] * $get['length'] - $get['length'];


        $data = $historyModel->getList($videoLikeHistory, $get['user_id'], $start, $get['length']);

        return json(['code' => 1, 'msg' => 'success', 'data' => $data]);
    }

    //推荐用户
    public function otherUser(Request $request)
    {
        $user = $this->userInfo;

        $userModel = new UserModel();

        $count = $userModel->count();
        if ($count < 10) {
            $start = 1;
            $end = $count;
        } else {
            $start = mt_rand(1, $count - 10);
            $end = $start + 10;
        }

        $data = $userModel->receptionShowData('user')
            ->alias('user')
            ->leftjoin('both both', 'both.to_user_id = user.id and both.from_user_id = ' . $user->id)
            ->whereBetween('user.id', $start . ',' . $end)
            ->where('user.id', '<>', $user->id)
            ->field('user.id,user.nickname,user.fans_sum,user.sex,user.avatar_url,both.create_time is_focus')
            ->limit(10)
            ->select()->toArray();

        return json(['code' => 1, 'msg' => 'success', 'data' => $data]);
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
        $authModel = new UserAuthModel();

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
            'card_pic' => 'require|max:128',
            'honor_pic' => 'max:128',
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
            'card_pic.max' => '非法',
            'honor_pic.max' => '非法2',
        ];

        $validate = new Validate($rules,$messages);
        if (!$validate->check($post)){
            return json(['code'=>1,'msg'=>$validate->getError()]);
        }
        $insert = [
            'user_id' => $user->id,
            'type'  => $type,
            'data' => json_encode([
                'name'  => $post['name'],
                'card_number'  => $post['card_number'],
                'teacher_number'  => $post['teacher_number'],
                'good_at_cate'  => $post['good_at_cate'],
                'desc'  => $post['desc'],
                'card_pic'  => $post['card_pic'],
                'honor_pic'  => $post['honor_pic']
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
        $authModel = new UserAuthModel();

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
            'card_pic' => 'require|max:128',
            'school_pic' => 'require|max:128',
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
            return json(['code'=>1,'msg'=>$validate->getError()]);
        }
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
                'card_pic'  => $post['card_pic'],
                'location'  => $post['location'],
                'school_pic'  => $post['school_pic']
            ],256)
        ];

        $authModel->insert($insert);

        return json(['code'=>1,'msg'=>'申请成功,请耐心等待']);
    }

    //给某个用户发送一条私信
    public function PrivateMessageSave(Request $request)
    {
        $post = $request->post();

        $user = $this->userInfo;

        $rules = [
            'token' => 'require',
            'message' => 'require|max:127',
            'user_id' => 'require',
        ];

        $messages = [
            'token.require' => 'token不合法',
            'message.require' => '信息必须填写',
            'message.max' => '信息最长不能超过127个字符',
            'user_id.require' => 'user_id不合法',
        ];

        $validate = new Validate($rules, $messages);

        if (!$validate->check($post)) return json(['code' => 0, 'msg' => $validate->getError()]);

        $userModel = new UserModel();
        $messageModel = new MessageModel();
        $privateMessageMessage = new PrivateMessageMessage();


        //检查user_id合法性
        if (!$userModel->existsUser($post['user_id'])) return json(['code' => 0, 'msg' => '接收方账号已被冻结']);

        //发送提醒消息 你收到N条私信
        $messageModel->send($privateMessageMessage, $post['user_id'], $post['message']);


        return json(['code' => 1, 'msg' => 'success']);
    }

    //获取用户信息
    public function info(Request $request)
    {
        $get = $request->get();

        $userModel = new UserModel();

        $user = isset($get['user_id']) ? $userModel->receptionShowData()->find($request->get('user_id')) : $this->userInfo;

        if (!$user) return json(['code'=>0,'msg'=>'err']);

        $return = [
            'user_id'       => $user['id'],
            'avatar_url'    => $user['avatar_url'],
            'nickname'      => $user['nickname'],
            'sex'           => $user['sex'],
            'country'       => $user['country'],
            'province'      => $user['province'],
            'city'          => $user['city'],
            'fans_sum'      => $user['fans_sum'],
            'focus_sum'     => $user['focus_sum'],
            'get_like_sum'  => $user['get_like_sum'],
            'works_sum'     => $user['works_sum'],
            'collect_sum'   => $user['collect_sum'],
        ];

        if ($this->existsToken() && isset($get['user_id'])){

            $bothModel = new BothModel();

            $return['is_focus'] = $bothModel->where(['from_user_id'=>$this->userInfo->id,'to_user_id'=>$return['user_id']])->find() ? true : false;

        }

        return json(['code'=>1,'msg'=>'success','data'=>$return]);
    }

    //修改我的信息
    public function update(Request $request)
    {
        $user = $this->userInfo;

        $post = $request->put();

        $rules = [
            'avatar_url' => 'require|max:128',
            'sex'        => 'require|in:1,2',
            'nickname'   => 'require|max:30',
            'phone'      => 'min:11|max:11|regex:/1[3-8]{1}[0-9]{9}/',
            'wechat'     => 'max:128',
            'province'   => 'require|max:120',
            'city'       => 'require|max:120',
        ];

        $messages = [
            'avatar_url.require'    => '头像必须上传',
            'avatar_url.max'        => 'err1',
            'sex.require'           => '性别必须选择',
            'nickname.require'      => '昵称必须填写',
            'nickname.max'          => '昵称最大长度为30',
            'phone.regex'           => '手机号格式不合法',
            'wechat.max'            => '微信号最大长度为128',
            'province.require'      => '地址填写不完全',
            'city.require'      => '地址填写不完全',
        ];

        $validate = new Validate($rules,$messages);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }
        $update = [
            'avatar_url' => $post['avatar_url'],
            'nickname' => $post['nickname'],
            'sex' => $post['sex'],
            'province' => $post['province'],
            'city' => $post['city'],
        ];

        isset($post['phone']) && $post['phone'] && $update['phone'] = $post['phone'];
        isset($post['wechat']) && $post['wechat'] && $update['wechat'] = $post['wechat'];

        (new UserModel())->where(['id'=>$user->id])->update($update);

        return json(['code'=>1,'msg'=>'success']);
    }

    //私信列表
    public function privateMessageList(Request $request)
    {
        $get = $request->get();
        $rules = [
            'token' => 'require',
        ];

        $messages = [
            'token.require' => 'token不合法'
        ];

        $validate = new Validate($rules, $messages);

        if (!$validate->check($get)) {
            return json(['code' => 0, 'msg' => 'success']);
        }
    }

    //
    public function privateMessageInfo()
    {

    }
}
