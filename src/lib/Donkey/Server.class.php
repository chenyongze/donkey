<?php

/**
 * Created by PhpStorm.
 * User: ClownFish
 * Date: 16-4-6
 * Time: 下午3:22
 */
namespace Donkey;

class Server
{
    private static $server;
    private $setting = array(
        "host"                     => "127.0.0.1",
        "port"                     => "9521",
        "reactor_num"              => 4,  //reactor线程数
        "worker_num"               => 4, //worker进程数
        'task_worker_num'          => 4, //task 数量
    );

    public function __construct($setting = array())
    {
        $this->formatSetting($setting);
        $this->run();
    }

    /**
     * 格式化配置
     * @param $setting
     */
    private function formatSetting($setting){
        foreach($setting as $key=>$value){
            if( isset($this->setting[$key])){
                $this->setting[$key] = $value;
            }
        }
    }

    /**
     * 启动swool_server服务器
     */
    private function run(){
        $host = $this->setting["host"];
        $port = $this->setting["port"];
        unset($this->setting["host"]);
        unset($this->setting["port"]);

        Server::$server = new \swoole_server($host, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        Server::$server->set($this->setting);
        Server::$server->on("Start", array($this, 'onStart'));
        Server::$server->on("Shutdown", array($this, "onShutdown"));
        Server::$server->on("WorkerStart", array($this, "onWorkerStart"));
        Server::$server->on("WorkerStop", array($this, "onWorkerStop"));
        Server::$server->on("Timer", array($this, "onTimer"));
        Server::$server->on("Connect", array($this, "onConnect"));
        Server::$server->on("Receive", array($this, "onReceive"));
        Server::$server->on("Packet", array($this, "onPacket"));
        Server::$server->on("Close", array($this, "onClose"));
        Server::$server->on("Task", array($this, "onTask"));
        Server::$server->on("Finish", array($this, "onFinish"));
        Server::$server->on("PipeMessage", array($this, "onPipeMessage"));
        Server::$server->on("WorkerError", array($this, "onWorkerError"));
        Server::$server->on("ManagerStart", array($this, "onManagerStart"));
        Server::$server->on("ManagerStop", array($this, "onManagerStop"));
        Server::$server->start();
    }

    /**
     * Server启动在主进程的主线程回调此函数
     * @param $server swoole_server server句柄
     */
    public function onStart($server)
    {
        $this->setName("master");
        echo "onStart\n";
    }

    /**
     * 此事件在Server结束时发生
     * @param $server swoole_server server句柄
     */
    public function onShutdown($server)
    {
        echo "onShutdown\n";
    }

    /**
     * 此事件在worker进程/task进程启动时发生。
     * 这里创建的对象可以在进程生命周期内使用
     * @param $server swoole_server server句柄
     * @param $worker_id int 是一个从0-$worker_num之间的数字，表示这个worker进程的ID
     */
    public function onWorkerStart($server, $worker_id)
    {
        //设置进程名称
        $this->setName($server->taskworker?"task":"worker",$worker_id);
        echo "onWorkerStart\n";
    }

    /**
     * 此事件在worker进程终止时发生。
     * 在此函数中可以回收worker进程申请的各类资源
     * @param $server swoole_server server句柄
     * @param $worker_id int 是一个从0-$worker_num之间的数字，表示这个worker进程的ID
     */
    public function onWorkerStop($server, $worker_id)
    {
        echo "onWorkerStop\n";
    }

    /**
     * 定时器触发
     * @param $server swoole_server server句柄
     * @param $interval int 定时器时间间隔，根据$interval的值来区分是哪个定时器触发的
     */
    public function onTimer($server, $interval)
    {
        echo "onTimer\n";
    }

    /**
     * 有新的连接进入时，在worker进程中回调
     * @param $server swoole_server server句柄
     * @param $fd int 是连接的文件描述符，发送数据/关闭连接时需要此参数
     * @param $from_id int 来自那个Reactor线程
     */
    public function onConnect($server, $fd, $from_id)
    {
        echo "onConnect\n";
    }

    /**
     * 接收到数据时回调此函数，发生在worker进程中
     * @param $server swoole_server server句柄
     * @param $fd int TCP客户端连接的文件描述符
     * @param $from_id int TCP连接所在的Reactor线程ID
     * @param $data string 收到的数据内容，可能是文本或者二进制内容
     */
    public function onReceive($server, $fd, $from_id, $data)
    {

    }

    /**
     * 接收到UDP数据包时回调此函数，发生在worker进程中
     * @param $server swoole_server server句柄
     * @param $data string 收到的数据内容，可能是文本或者二进制内容
     * @param $client_info array 客户端信息包括address/port/server_socket 3项数据
     */
    public function onPacket($server, $data, $client_info)
    {
        echo "onPacket\n";
    }

    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     * @param $server swoole_server server句柄
     * @param $fd int 连接的文件描述符
     * @param $from_id int 来自那个reactor线程
     */
    public function onClose($server, $fd, $from_id)
    {
        echo "onClose\n";
    }

    /**
     * 在task_worker进程内被调用。
     * worker进程可以使用swoole_server_task函数向task_worker进程投递新的任务
     * @param $server swoole_server server句柄
     * @param $task_id int 任务ID，由swoole扩展内自动生成，用于区分不同的任务。
     *                 $task_id和$from_id组合起来才是全局唯一的，
     *                 不同的worker进程投递的任务ID可能会有相同
     * @param $from_id int 来自于哪个worker进程
     * @param $data string 任务的内容
     */
    public function onTask($server, $task_id, $from_id, $data)
    {
    }

    /**
     * 当worker进程投递的任务在task_worker中完成时，
     * task进程会通过swoole_server->finish()方法将任务处理的结果发送给worker进程。
     * @param $server swoole_server server句柄
     * @param $task_id  int //任务的ID
     * @param $data string //任务处理的结果内容
     */
    public function onFinish($server, $task_id, $data)
    {
    }

    /**
     * 当工作进程收到由sendMessage发送的管道消息时会触发onPipeMessage事件。
     * worker/task进程都可能会触发onPipeMessage事件
     * @param $server swoole_server //server句柄
     * @param $from_worker_id int  //来自那个worker的id
     * @param $message  string //消息
     */
    public function onPipeMessage($server, $from_worker_id, $message)
    {
        echo "onPipeMessage\n";
    }

    /**
     * 当worker/task_worker进程发生异常后会在Manager进程内回调此函数
     * @param $server swoole_server //server句柄
     * @param $worker_id int 异常进程的编号
     * @param $worker_pid int 异常进程的PID
     * @param $exit_code int 退出的状态码，范围是 1 ～255
     */
    public function onWorkerError($server, $worker_id, $worker_pid, $exit_code)
    {
        echo "onWorkerError\n";
    }

    /**
     * 当管理进程启动时调用它
     * @param $server swoole_server //server句柄
     */
    public function onManagerStart($server)
    {
        $this->setName("manger");
    }

    /**
     * 当管理进程结束时调用它
     * @param $server swoole_server //server句柄
     */
    public function onManagerStop($server)
    {
        echo "onManagerStop\n";
    }


    /**
     * 设置进程名称
     * @param $type
     * @param int $id
     */
    public function setName($type,$id = -1){
        $process_name = "donkey_".$type;
        if($id > -1){
            $process_name .= "_".$id;
        }
        swoole_set_process_name($process_name);
    }


}