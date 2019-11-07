<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/6
 * Time: 17:05
 */

namespace app\common\lib;


class UmengPush
{
    protected $host; //发送地址

    protected $app_key; //appkey

    protected $appMasterSecret; //app secret

    protected $description; //app secret



    //

    public function __construct($options = null)

    {

        if (is_array($options)){



            $this->host = 'https://msgapi.umeng.com/api/send';

            $this->app_key = $options['app_key'];

            $this->appMasterSecret = $options['appMasterSecret'];



            $this->description = "友盟接口推送";



        }else{

            return false;

        }



    }





    /**

     * @param $info

     * @param $device_token

     * @return mixed|string

     * 用户单播 和 列播

     */

    public function Android_Device_Push($info, $device_token)

    {

        $data['appkey'] = $this->app_key;

        $data['timestamp'] = time(); //时间戳



        if(is_array($device_token)){



            //批量用户列播

            $data['type'] = 'listcast';

            $data['device_tokens'] =  implode(',',$device_token); //数组转字符串



        }else{



            //一个用户单播

            $data['type'] = 'unicast';

            $data['device_tokens'] =  $device_token;

        }



        //payload内容

        $data['payload']['display_type'] = 'notification'; //通知消息



        //payload body内容

        $data['payload']['body']['after_open'] = "go_custom"; //后续操作打开app



        $data['payload']['body']['ticker'] = $info['ticker'];

        $data['payload']['body']['title'] = $info['title'];

        $data['payload']['body']['text'] = $info['title']; //广播通知不能为空补填



        //这里可以写附加字段

        $data['payload']['extra']['type'] = $info['type'];  //附加字段类型





        $data['production_mode'] = $info['production_mode'];



        $data['description'] = $this->description;



        return $this->send($data, $this->host, $this->appMasterSecret);

    }



    /**

     * @param $info

     * @return mixed|string

     * 广播

     */

    public function Android_Broadcast($info)

    {

        $data['appkey'] = $this->app_key;

        $data['timestamp'] = time(); //时间戳



        //广播消息

        $data['type'] = 'broadcast';



        //payload内容

        $data['payload']['display_type'] = 'notification'; //通知消息



        //payload body内容

        $data['payload']['body']['after_open'] = "go_custom"; //后续操作打开app



        $data['payload']['body']['ticker'] = $info['ticker'];

        $data['payload']['body']['title'] = $info['title'];

        $data['payload']['body']['text'] = $info['title']; //广播通知不能为空补填



        $data['payload']['extra']['type'] = $info['type'];  //附加字段类型1 跳转消息详情

        $data['payload']['extra']['prod_id'] = $info['prod_id'];  //附加字段消息详情id

        $data['payload']['extra']['text'] = $info['text']; //



        $data['production_mode'] = $info['production_mode'];



        $data['description'] = $this->description;



        return $this->send($data, $this->host, $this->appMasterSecret);

    }



    /**

     * @param $info

     * @param $device_token

     * @return mixed|string

     * 单播 和 列播

     */

    public function Ios_Device_Push($info, $device_token)

    {

        $data = array();



        $data['appkey'] = $this->app_key;

        $data['timestamp'] = time(); //时间戳



        if(is_array($device_token)){



            //批量用户列播

            $data['type'] = 'listcast';

            $data['device_tokens'] =  implode(',',$device_token); //数组转字符串



        }else{



            //一个用户单播

            $data['type'] = 'unicast';

            $data['device_tokens'] =  $device_token;

        }



        //payload内容

        $data['payload']['aps']['alert'] = $info['text']; //消息主体

        $data['payload']['aps']['sound'] = 'default'; //声音



        $data['payload']['type'] = $info['type']; //消息类型 0打开消息详情



        $data['payload']['prod_id'] = $info['prod_id']; //消息id

        $data['payload']['title'] = $info['title'];

        $data['payload']['text'] = $info['text']; //



        $data['production_mode'] = $info['production_mode'];



        $data['description'] = $this->description;



        return $this->send($data, $this->host, $this->appMasterSecret);

    }





    public function Ios_Broadcast($info)

    {

        $data = array();



        $data['appkey'] = $this->app_key;

        $data['timestamp'] = time(); //时间戳



        //广播消息

        $data['type'] = 'broadcast';



        //payload内容

        $data['payload']['aps']['alert'] = $info['title']; //消息主体

        $data['payload']['aps']['sound'] = 'chime'; //声音

        $data['payload']['aps']['badge'] = 1; //显示角标



        $data['payload']['type'] = $info['type']; //消息类型 0打开消息详情



        $data['payload']['prod_id'] = $info['prod_id']; //消息id

        $data['payload']['title'] = $info['title'];

        $data['payload']['text'] = $info['ticker']; //



        $data['production_mode'] = $info['production_mode'];



        $data['description'] = $this->description;



        return $this->send($data, $this->host, $this->appMasterSecret);

    }



    /**

     * @param $data

     * @param $url_s

     * @param $appMasterSecret

     * @return mixed|string

     * curl 请求

     */

    private function send($data, $url_s, $appMasterSecret)

    {

        $postBody = json_encode($data);



        //加密

        $sign = md5("POST" . $url_s . $postBody . $appMasterSecret);

        $url = $url_s . "?sign=" . $sign;



        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody );

        $result = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $curlErrNo = curl_errno($ch);

        $curlErr = curl_error($ch);

        curl_close($ch);



        // print_r($result);

        // exit;



        if ($httpCode == "0") {

            // Time out

            return ("Curl error number:" . $curlErrNo . " , Curl error details:" . $curlErr . "\r\n");

        } else if ($httpCode != "200") {

            return ("Http code:" . $httpCode .  " details:" . $result . "\r\n");

        } else {

            return $result;

        }

    }
}