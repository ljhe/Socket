<?php
/**
 * socket 服务端的 demo
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

while (1) {
    // 接收套接字的资源信息，成功返回套接字的信息资源，失败为false
    $conn = socket_accept($server_socket);
    if ($conn) {
        // 获取连接过来的客户端ip地址和端口
        socket_getpeername($conn,$addr,$port);

        $data = socket_read($conn, 1024);  //从客户端读取数据
        if ($data === false) {
            echo "client close" . PHP_EOL;
            break;
        } else {
            echo 'read from client:' . $data . PHP_EOL;
            socket_write($conn, $data, strlen($data));  //回写给客户端
        }
        //客户端关闭
        socket_close($conn);
    }
}
// 关闭socket
socket_close($socket);


// 保存错误日志
function saveLog($res)
{
    $errcode = socket_last_error();
    // 读写方式打开文件，将文件指针指向文件末尾。如果文件不存在，则创建。
    $myFile = fopen('socket_log', "a+");
    fwrite($myFile,$res . socket_strerror($errcode) . PHP_EOL);
    // 关闭打开的文件
    fclose($myFile);
    return;
}

