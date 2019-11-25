<?php

namespace app\apiv1\controller;

use TencentCloud\Common\Credential;
use think\Controller;
use think\Request;
// 导入对应产品模块的client
use TencentCloud\Cvm\V20170312\CvmClient;
// 导入要请求接口对应的Request类
use TencentCloud\Cvm\V20170312\Models\DescribeZonesRequest;
use TencentCloud\Common\Exception\TencentCloudSDKException;

class Sign extends Base
{
    public function sign()
    {
        $secret_id = env('tx.SECRET_ID');
        $secret_key = env('tx.SECRET_KEY');

// 确定签名的当前时间和失效时间
        $current = time();
        $expired = $current + 86400;  // 签名有效期：1天

// 向参数列表填入参数
        $arg_list = array(
            "secretId" => $secret_id,
            "currentTimeStamp" => $current,
            "expireTime" => $expired,
            "random" => rand());

        // 计算签名
        $orignal = http_build_query($arg_list);
        $signature = base64_encode(hash_hmac('SHA1', $orignal, $secret_key, true).$orignal);

        return json(['code'=>1,'msg'=>'success','data'=>$signature]);

    }
}
