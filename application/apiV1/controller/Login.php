<?php

namespace app\apiV1\controller;


use app\common\model\User as UserModel;
use think\Controller;
use think\Request;
use app\common\lib\RequestHttp;

class Login extends Controller
{
    public function login(Request $request)
    {
        $appid = env('WECHAT.APP_ID');			//开发平台申请
        $appsecret= env('WECHAT.APP_SECRET');	//开发平台申请

        $code = $request->post('code');	//安卓端 or ios提供用户同意登入后的code

        //认证
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code ."&grant_type=authorization_code";

        //调用微信api
        $http = new RequestHttp;
        $rs = $http -> get($url);
        if(!$rs)return json(['code'=>0,'msg'=>'获取OPENID失败']);

        $rt = json_decode($rs, 1);
        if($rt['errcode']) return json(['code'=>0,'msg'=>'授权失败:'.$rt['errmsg']]);

        // 拉取用户信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$rt['access_token']."&openid=".$rt['openid']."&lang=zh_CN ";

        $wechat_info = $http -> get($url);
        if(!$wechat_info) return json(['code'=>0,'msg'=>'获取用户资料失败:CURL '.$http -> errmsg]);

        $wechat_info = json_decode($wechat_info, 1);
        if($wechat_info['errcode']){
            return json(['code'=>0,'msg'=>"获取用户资料失败".$wechat_info['errmsg']]);
        }

        $userModel = new UserModel();
        $user = (new UserModel())->where(['openid'=>$wechat_info['openid']])->find();
        $token = $this->makeToken($user['unionid']);
        if (!$user){
            $user_info = [
                "avatar_url"=>$wechat_info['headimgurl'],	//头像
                "nickname"=>$wechat_info['nickname'],	        //昵称
                "sex"=>$wechat_info['sex'],				            //性别
                "openid"=>$wechat_info['openid'],		            //app唯一
                "unionid"=>$wechat_info['unionid'],		            //微信内部唯一，小程序， 公众号， web， 移动应用都是一致的
                "province"=>$wechat_info['province'],
                "city"=>$wechat_info['city'],
                "country"=>$wechat_info['country'],
                "create_time"=>time(),
                "token"=>$token,
            ];

            $userModel->insert($user_info);
        }

        return json(['code'=>1,'msg'=>'success','data'=>['token'=>$token]]);

    }

    public function getAccessToken()
    {

    }

    //生成token
    protected function makeToken($unionid)
    {
        $rand = mt_rand(1000000,9999999);
        $salt = env('LOGIN.SALT');
        return md5(md5($unionid) . $rand . $salt .  microtime() );
    }
}
