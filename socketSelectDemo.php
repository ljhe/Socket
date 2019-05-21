<?php
/**
 * socket_select() 非阻塞
 */

// 创建一个socket
$server_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
if (false === $server_socket) {
    saveLog("socket create fail: ");
}

// 绑定IP地址以及端口
if (!socket_bind($server_socket,'127.0.0.1',8888)) {
    saveLog("socket bind fail: ");
}

// 最大监听套接字个数
if (socket_listen($server_socket,128)) {
    saveLog("socket listen fail: ");
}

$e = null;
$t = 100;
// 非阻塞
socket_set_nonblock($server_socket);
$rfds[] = $server_socket;
$wfds = array();

while (1) {
    $read = $rfds;
    $write = $wfds;
    @socket_select($read,$write,$e,$t);
    foreach ($read as $k) {
        if ($k == $server_socket) {
            $nonBlock = socket_accept($server_socket);
            // 非阻塞
            socket_set_nonblock($nonBlock);
            $rfds[] = $nonBlock;
            echo "new client coming, $nonBlock" . PHP_EOL;
        }else{
            echo "accept data" . PHP_EOL;
            // 从客户端读取数据
            $data = socket_read($k, 1024);
            if ($data === false) {
                echo "client close" . PHP_EOL;
                break;
            } else {
                echo 'read from client:' . $data . PHP_EOL;
                // 回写给客户端
                socket_write($k, $data, strlen($data));
                // 删除rfds数组中的需要关闭的套接字
                $key = array_search($k, $rfds);
                unset($rfds[$key]);
                // 客户端关闭
                socket_close($k);

            }
        }
    }
    sleep(3);
}


// 保存错误日志
function saveLog($res)
{
    $errcode = socket_last_error();
    // 读写方式打开文件，将文件指针指向文件末尾。如果文件不存在，则创建。
    $myFile = fopen('socket_log.txt', "a+");
    fwrite($myFile,$res . socket_strerror($errcode) . PHP_EOL);
    // 关闭打开的文件
    fclose($myFile);
    return;
}

