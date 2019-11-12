<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/10/30
 * Time: 16:38
 */

namespace app\common\lib;


class RequestHttp{
    protected $url;	//要请求的地址
    protected $method;	//请求方法
    protected $data;	//数据
    public $errmsg;		//错误信息,返回false的时候可以用来获取详细的错误代码
    public $sslkey = null; 	//sslkey,默认是pem格式
    public $sslcert = null; 	//cert证书，默认是pem格式

    /**
     *	GET方式抓取数据
     *	@param string $url 要抓取的URL
     */
    public function get($url,$https = true, $data = null){
        //设置网络请求配置
        // 创建一个新cURL资源
        $ch = curl_init();

        // 设置URL和相应的选项
        curl_setopt($ch, CURLOPT_URL, $url);  //要访问的网站
        //启用时会将头文件的信息作为数据流输出。
        curl_setopt($ch, CURLOPT_HEADER, false);
        //将curl_exec()获取的信息以字符串返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($https){
            //FALSE 禁止 cURL 验证对等证书（peer's certificate）。
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true); //验证主机
        }
        // 抓取URL并把它传递给浏览器
        $content = curl_exec($ch);

        //关闭cURL资源，并且释放系统资源
        curl_close($ch);

        return $content;
    }

    /**
     *	POST提交数据并返回内容
     *	@param string $url 要请求的地址
     *	@param mixed $data 提交的数据
     */
    public function post($url, $data = null){
        $this->url = $url;
        $this->method = "POST";
        $this->data = $data;
        return $this->excrequest();
    }

    /**
     *	执行请求并返回数据
     *	@access private
     */
    private function excrequest(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if($this->sslcert){
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $this->sslcert);
            //echo $this->sslcert;
        }
        if($this->sslkey){
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $this->sslkey);
            //echo $this->sslkey;
        }
        //die($this->data);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        $errorno = curl_errno($ch);
        if(!$errorno)return $tmpInfo;
        else{
            $this->errmsg = $errorno;
            return false;
        }
    }
}
