<?php

use Swoole\Websocket\Server;
use Swoole\WebSocket\Frame;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Table;

$host = '127.0.0.1';
$hostname = "127.0.0.1";
$port = 8080;

// Table is a shared memory table that can be used across connections
$messages = new Table(1024);
// we need to set the types that the table columns support - just like a RDB
$messages->column('id', Table::TYPE_INT, 11);
$messages->column('client', Table::TYPE_INT, 4);
$messages->column('username', Table::TYPE_STRING, 64);
$messages->column('message', Table::TYPE_STRING, 255);
$messages->column('is_admin', Table::TYPE_INT, 1);
$messages->create();

$connections = new Table(1024);
$connections->column('client', Table::TYPE_INT, 4);
$connections->create();

$server = new Server($host, $port);
$server->set([
    'worker_num' => 1,
    'task_worker_num' => 2,
]);

$server->on('start', function (Server $server) use ($hostname, $port) {
    echo sprintf('Swoole HTTP server is started at http://%s:%s' . PHP_EOL, $hostname, $port);
});

$server->on('task', function (Server $server) use ($hostname, $port) {
    echo sprintf('Swoole HTTP server is started at http://%s:%s' . PHP_EOL, $hostname, $port);
});


$server->on('open', function (Server $server, Request $request) use ($messages, $connections) {
    echo "connection open: {$request->fd}\n";
    // store the client on our memory table
    $connections->set($request->fd, ['client' => $request->fd]);

    // update all the client with the existing messages
    foreach ($messages as $row) {
        $server->push($request->fd, json_encode($row));
    }
});

// we can also run a regular HTTP server at the same time!
$server->on('request', function (Request $request, Response $response) use ($server) {
    $response->header('Content-Type', 'text/html');
    switch ($request->server['request_uri']) {
        case '/user':
            $content = handleUserRequest($request);
            break;
        case '/admin':
            $content = handleAdminRequest($request);
            break;
        case '/admin-msg':
            $msg = $request->get['msg'] ?: null;
            if (!$msg) {
                $content = "Msg must not be empty";
                echo "$content\n";
            } else {
                $data = [
                    'id' => time(),
                    'client_id' => 0,
                    'is_admin' => 1,
                    'username' => 'admin',
                    'message' => $msg,
                ];
                $server->task($data);
                $content = json_encode($data);
            }
            break;
        default:
            $content = handleLoginRequest($request);
    }
    $response->end($content);
});

$server->on('message', function (Server $server, Frame $frame) use ($messages, $connections) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";

    // frame data comes in as a string
    $output = json_decode($frame->data, true);

    // assign a "unique" id for this message
    $output['id'] = time();
    $output['client'] = $frame->fd;
    $output['is_admin'] = strpos($output['username'], 'admin') === 0 ? 1 : 0;

    // now we can store the message in the Table
    $messages->set($output['username'] . time(), $output);

    // now we notify any of the connected clients
    foreach ($connections as $client) {
        $server->push($client['client'], json_encode($output));
    }
});

$server->on('task', function (Server $server, $task_id, $reactorId, $data) use ($messages, $connections) {
    $str = json_encode($data);
    echo "receive admin task {$task_id}, data: $str\n";
    foreach ($connections as $client) {
        $server->push($client['client'], $str);
    }
});

$server->on('close', function (Server $server, int $client) use ($connections) {
    echo "client {$client} closed\n";
    // remove the client from the memory table
    $connections->del($client);
});

$server->start();


function handleLoginRequest(Request $request)
{
    return render('login');
}

function handleAdminRequest(Request $request)
{
    $username = $request->get['username'] ?: null;
    if (!$username) {
        die('???????????????');
    }
    return render('admin');
}

function handleUserRequest(Request $request)
{
    $username = $request->get['username'] ?: null;
    if (!$username) {
        die('???????????????');
    }
    return render('user', ['username' => $username]);
}

function render($filename, $data = [])
{
    $path = __DIR__ . '/' . $filename . '.php';
    ob_start();
    extract($data);
    if (file_exists($path)) {
        include($path);
    } else throw new \Exception("File not exist: " . $path);
    return ob_get_clean();
}