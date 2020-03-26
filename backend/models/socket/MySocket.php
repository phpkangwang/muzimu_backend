<?php
namespace backend\models\socket;
use Yii;

class MySocket{
    private $host = "127.0.0.1";
    private $port = "";
    private $socket = null;
    private $connection = null;

    public function __construct()
    {
       $this->port       = Yii::$app->params['socket'];
       $this->socket     = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("socket create error");
       $this->connection = socket_connect($this->socket, $this->host, $this->port) or die("socket connection error");
    }

    public function send($msg)
    {
        socket_write($this->socket, $msg);
    }

    public function __destruct()
    {
        socket_close($this->socket);
        // TODO: Implement __destruct() method.
    }
}