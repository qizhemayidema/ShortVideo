
<?php
/**
 * Created by PhpStorm.
 * User: 刘彪
 * Date: 2019/11/1
 * Time: 12:52
 */


class Chat
{

    const HOST = '0.0.0.0'; //允许访问的地址

    const PART = 82;        //端口号

    private $server = null;//单例存放websocket_server对象

    /**
     * @var \Swoole\Coroutine\MySQL
     */
    private $dbCollect = null; //单例存放 异步数据库连接资源

    public $userList = []; //用户池  user_id => fd




    public function __construct()
    {
        //实例化
        $this->server = new swoole_websocket_server(self::HOST, self::PART);
        //监听连接事件
        $this->server->on('open', [$this, 'onOpen']);
        //监听接收消息事件
        $this->server->on('message', [$this, 'onMessage']);
        //监听关闭事件
        $this->server->on('close', [$this, 'onClose']);
        //设置
        $this->server->set([
            'reactor_num' => 2,     //使用核心数
//          'daemonize'     => 1,   //守护进程
            'max_conn'    => 5000,  //最大连接数
            'worker_num ' => 8,     //工作进程
//            'document_root' => '/grx/swoole/public',//这里传入静态文件的目录
//            'enable_static_handler' => true//允许访问静态文件
        ]);
//        //协程数据库客户端
//        $db = null;
//
//        go(function() use (&$db){
//            $db = new Swoole\Coroutine\MySQL();
//
//            $dbConfig = [
//                'host' => '127.0.0.1',
//                'port' => 3306,
//                'user' => 'sanchihui',
//                'password' => '2PFm886hearjCai6',
//                'database' => 'sanchihui',
//                'charset' => 'utf8mb4', //指定字符集
//                'timeout' => 2,  // 可选：连接超时时间（非查询超时时间），默认为SW_MYSQL_CONNECT_TIMEOUT（1.0）
//            ];
//
//            $db->connect($dbConfig);
//        });

//        $this->dbCollect = $db;

        //开启服务
        $this->server->start();

    }

    /**
     * 连接成功回调函数
     * @param $server
     * @param $request
     */
    public function onOpen($server, $request)
    {
        //通过 $request->get 得到 token值 与数据库查询出 user_id 得到后 存入数组

//        print_r($this->dbCollect->query("select now()"));
        echo $request->fd . '连接了' . PHP_EOL;//打印到我们终端
//        print_r($request);
//        print_r($this->dbCollect);
    }

    /**
     * 接收到信息的回调函数
     * @param $server
     * @param $frame
     */
    public function onMessage($server, $frame)
    {
        $server->connections;
        echo $frame->fd . '来了，说：' . $frame->data . PHP_EOL;//打印到我们终端
        foreach ($server->connections as $fd) {//遍历TCP连接迭代器，拿到每个在线的客户端id
            //将客户端发来的消息，推送给所有用户，也可以叫广播给所有在线客户端
//            $server->send($fd, json_encode(['no' => $frame->fd, 'msg' => $frame->data]));
            $server->push($fd, json_encode(['no' => $frame->fd, 'msg' => $frame->data]));
        }
    }

    /**
     * 断开连接回调函数
     * @param $server
     * @param $fd
     */
    public function onClose($server, $fd)
    {
        echo $fd . '走了' . PHP_EOL;//打印到我们终端
    }
}


$chat = new Chat();