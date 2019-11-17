<?php

namespace app\apiv1\controller;

use app\common\model\User as UserModel;
use think\Controller;
use think\Request;
use app\common\lib\RequestHttp;
use think\Validate;
use EasyWeChat\Factory;

class Login extends Controller
{
    public function login(Request $request)
    {

        $appid = env('WECHAT.APP_ID');			//开发平台申请
        $appsecret= env('WECHAT.APP_SECRET');	//开发平台申请

        $code = $request->post('code');

        if (!$code) return json(['code'=>0,'msg'=>'登陆失败!']);
        //认证
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code ."&grant_type=authorization_code";

        //调用微信api
        $rs = $this->curlData($url,[]);
        if(!$rs)return json(['code'=>0,'msg'=>'获取OPENID失败']);

        $rt = json_decode($rs, 256);
        if(isset($rt['errcode']) && $rt['errcode']) return json(['code'=>0,'msg'=>'授权失败:'.$rt['errmsg']]);

        // 拉取用户信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$rt['access_token']."&openid=".$rt['openid']."&lang=zh_CN ";

        $wechat_info = $this->curlData($url,[]);
        if(!$wechat_info) return json(['code'=>0,'msg'=>'获取用户资料失败:CURL ']);

        $wechat_info = json_decode($wechat_info, 1);
        if(isset($rt['errcode']) && $wechat_info['errcode']){
            return json(['code'=>0,'msg'=>"获取用户资料失败".$wechat_info['errmsg']]);
        }

        $userModel = new UserModel();
        $user = $userModel->where(['unionid'=>$wechat_info['unionid']])->find();
        $token = $this->makeToken($user['unionid']);
        if (!$user){
            $user_info = [
                "avatar_url"=>$wechat_info['headimgurl'],	//头像
                "nickname"=>$wechat_info['nickname'],	        //昵称
                "sex"=>$wechat_info['sex'],				            //性别
                "openid"=>$wechat_info['openid'],		                //app唯一
                "unionid"=>$wechat_info['unionid'],		            //微信内部唯一，小程序， 公众号， web， 移动应用都是一致的
                "province"=>$wechat_info['province'],
                "city"=>$wechat_info['city'],
                "country"=>$wechat_info['country'],
                "create_time"=>time(),
                "token"=>$token,
            ];

            $userModel->insert($user_info);
            $userId = $userModel->getLastInsID();
        }else{
            if ($user->status == 1)
                return json(['code'=>0,'msg'=>'该账号已被冻结,如果疑问请联系官方']);
            $user->save(['token'=>$token]);
            $userId = $user->id;
        }

        $userInfo = $userModel->receptionShowData()->field('id,phone,wechat,nickname,avatar_url,sex,country,province,city,focus_sum,fans_sum,get_like_sum,works_sum,collect_sum,score,auth_type,token')->find($userId);

        return json(['code'=>1,'msg'=>'success','data'=>$userInfo]);

    }

    public function loginIos(Request $request)
    {
        $post = $request->post();

        $rules = [
            'username'  => 'require',
            'password'  => 'require',
        ];

        $message = [
            'username.require' => '账号必须填写',
            'password.require' => '密码必须填写',
        ];

        $validate = new Validate($rules,$message);

        if (!$validate->check($post)){
            return json(['code'=>0,'msg'=>$validate->getError()]);
        }

        //查找是否存在此账号

        $user = (new UserModel())->where(['username'=>$post['username'],'password'=>md5($post['password'])])->field('id,phone,wechat,nickname,avatar_url,sex,country,province,city,focus_sum,fans_sum,get_like_sum,works_sum,collect_sum,score,auth_type,token')->find();

        if ($user) return json(['code'=>1,'msg'=>'success','data'=>$user]);

        return json(['code'=>0,'msg'=>'账号或密码不正确']);
    }

    //生成token
    protected function makeToken($unionid)
    {
        $rand = mt_rand(1000000,9999999);
        $salt = env('LOGIN.SALT');
        return md5(md5($unionid) . $rand . $salt .  microtime() );
    }

    function curlData($url,$data,$method = 'GET',$type='json')
    {
        //初始化
        $ch = curl_init();
        $headers = [
            'form-data' => ['Content-Type: multipart/form-data'],
            'json'      => ['Content-Type: application/json'],
        ];
        if($method == 'GET'){
            if($data){
                $querystring = http_build_query($data);
                $url = $url.'?'.$querystring;
            }
        }
        // 请求头，可以传数组
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers[$type]);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);         // 执行后不直接打印出来
        if($method == 'POST'){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');     // 请求方式
            curl_setopt($ch, CURLOPT_POST, true);               // post提交
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);              // post的变量
        }
        if($method == 'PUT'){
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        }
        if($method == 'DELETE'){
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 不从证书中检查SSL加密算法是否存在
        $output = curl_exec($ch); //执行并获取HTML文档内容
        curl_close($ch); //释放curl句柄
        return $output;
    }

}
