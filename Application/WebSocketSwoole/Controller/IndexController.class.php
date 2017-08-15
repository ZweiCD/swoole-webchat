<?php

namespace WebSocketSwoole\Controller;

use Think\Controller;

class IndexController extends Controller {
    public function index(){
        $server = new \swoole_websocket_server('192.168.197.129', 9501);
        $server->set(array(
            'task_worker_num' => 8,
        ));

        $server->users = [];

        $server->on('WorkerStart', function($ser, $worker_id){
            echo "worker start!worker_id: $worker_id\n";
            if($worker_id == 0){
                $ser->addtimer(1000);
            }
        });

        $server->on('timer', function($ser, $interval){
            switch($interval){
                case 1000: {
                    foreach($ser->users as $k => $v){
                        foreach($v['msg_list'] as $kk => $vv){
                            $ser->task(array($v['fd'], $vv));
                        }
                    }
                    break;
                }
            }
        });

        $server->on('open', function(\swoole_websocket_server $server, $request){
            echo "server: handshake success with fd{$request->fd}\n";
        });

        $server->on('message', function(\swoole_websocket_server $server, $frame){
            echo "receive frome {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            $data = json_decode($frame->data, true);
//>=====================================================================================================================
            /*
             * 客户端连接socket
             */
            if($data['cmd'] == 'socket_connect'){
                $res = A('WebSocketSwoole/SocketConnect')->index($data, $frame->fd);
                if(!$res){
                    $res_data = array(
                        'cmd' => 'socket_connect_result',
                        'code' => 1,
                        'msg' => 'login fail',
                    );
                }else{
                    $res_data = array(
                        'cmd' => 'socket_connect_result',
                        'code' => 0,
                        'msg' => 'login success',
                    );

                    //如果用户已经在线
                    if(array_key_exists($data['account'], $server->users)){
                        $fd = $server->users[$data['account']]['fd'];
                        $push_data = array(
                            'cmd' => 'server_logout',
                        );
                        $server->push($fd, json_encode($push_data));
                    }

                    $server->users[$data['account']] = array(
                        'account' => $data['account'],
                        'uid' => $data['uid'],
                        'fd' => $frame->fd,
                        'status' => 1,
                    );
                }
                $server->push($frame->fd, json_encode($res_data));

                //通知该账号在线的好友
                $list = A('WebSocketSwoole/Friend')->friendList($data['uid']);
                if(!empty($list)) {
                    $push_data = array(
                        'cmd' => 'server_friend_online',
                        'code' => 0,
                        'uid' => $data['uid'],
                        'status' => $server->users[$data['account']]['status'],
                    );
                    foreach ($list as $k => $v) {
                        if (array_key_exists($v['account'], $server->users) && $server->users[$v['account']]['status'] > 0) {
                            $server->push($server->users[$v['account']]['fd'], json_encode($push_data));
                        }
                    }
                }
            }
//<=====================================================================================================================
//>=====================================================================================================================
            /*
             * 客户端请求好友列表
             */
            if($data['cmd'] == 'client_friend_list'){
                if($frame->fd == $server->users[$data['account']]['fd']) {
                    $uid = $server->users[$data['account']]['uid'];
                    $account = $data['account'];
                    $list = A('WebSocketSwoole/Friend')->friendList($uid);
                    $unreadMsgList = A('WebSocketSwoole/Message')->unreadMsgList($uid); //账号未读消息
                    if(!empty($list)) {
                        foreach ($list as $k => $v) {
                            $list[$k]['status'] = 0;
                            $list[$k]['unreadMsgNum'] = 0;
                            if (array_key_exists($v['account'], $server->users)) {
                                $list[$k]['status'] = $server->users[$v['account']]['status'];
                            }

                            foreach($unreadMsgList as $kk  => $vv){
                                if($vv['from'] == $v['uid']){
                                    $list[$k]['unreadMsgNum'] += 1;
                                    $vv['to'] = $uid;
                                    $vv['to_account'] = $account;
                                    $list[$k]['unreadMsg'][] = $vv;
                                }
                            }
                        }
                    }

                    $push_data = array(
                        'cmd' => 'server_friend_list',
                        'code' => 0,
                        'list' => $list,
                    );
                    $server->push($frame->fd, json_encode($push_data));
                }
            }
//<=====================================================================================================================
//>=====================================================================================================================
            /*
             * 客户端请求消息请求列表
             */
            if($data['cmd'] == 'client_request_list'){
                if($frame->fd == $server->users[$data['account']]['fd']) {
                    $uid = $server->users[$data['account']]['uid'];
                    $account = $data['account'];
                    $list = A('WebSocketSwoole/Request')->requestList($uid);
                    foreach($list as $k => $v){
                        $list[$k]['to_account'] = $account;
                    }

                    $push_data = array(
                        'cmd' => 'server_request_list',
                        'list' => $list,
                    );
                    $server->push($frame->fd, json_encode($push_data));
                }
            }
//<=====================================================================================================================
//>=====================================================================================================================
            /*
             * 客户端发送消息
             */
            if($data['cmd'] == "client_send_msg"){
                $is_friend = A('WebSocketSwoole/Friend')->isFriend($data['from'], $data['to']);
                if($is_friend){
                    $push_data = array( //通知发送者服务器受到消息
                        'cmd' => 'send_msg_result',
                        'code' => 0,
                        'msg' => 'ok',
                    );
                    $server->push($frame->fd, json_encode($push_data));

                    //保存聊天消息
                    $msg_id = A('WebSocketSwoole/Message')->savePersonalMsg($data);

                    //查询接受者是否在线，在线则转发消息
                    if(array_key_exists($data['to_account'], $server->users) && $server->users[$data['to_account']]['uid'] == $data['to']){
                        $push_data = array(
                            'cmd' => 'server_send_msg',
                            'code' => 0,
                            'from' => $data['from'],
                            'to' => $data['to'],
                            'from_account' => $data['from_account'],
                            'to_account' => $data['to_account'],
                            'type' => $data['type'],
                            'msg' => $data['msg'],
                            'send_time' => $data['send_time'],
                            'msg_id' => $msg_id,
                        );
                        $server->task(array($server->users[$data['to_account']]['fd'], json_encode($push_data)));
                        $server->users[$data['to_account']]['msg_list'][] = json_encode($push_data);
                    }
                }else{
                    $push_data = array(
                        'cmd' => 'send_msg_result',
                        'code' => 1,
                        'msg' => 'is  not friend',
                    );
                    $server->push($frame->fd, json_encode($push_data));
                }
            }
//<=====================================================================================================================
//>=====================================================================================================================
            /*
             * 客户端通知服务器消息已收到
             */
            if($data['cmd'] == 'client_get_msg'){
                foreach($server->users[$data['account']]['msg_list'] as $k => $v){
                    $msg = json_decode($v, true);
                    if($msg['msg_id'] == $data['msg_id']){
                        unset($server->users[$data['account']]['msg_list'][$k]);
                        break;
                    }
                }
            }
//<=====================================================================================================================
//>=====================================================================================================================
            /*
             * 客户端通知服务器消息已读
             */
            if($data['cmd'] == 'client_read_msg'){
                A('WebSocketSwoole/Message')->setMsgIsRead($data['msg_id']);
            }
//<=====================================================================================================================
//>=====================================================================================================================
            /*
             * 客户端发送添加好友请求
             */
            if($data['cmd'] == 'client_friend_request'){
                $search_uid = A('WebSocketSwoole/User')->isAccountExist($data['search_account']);
                if(!$search_uid){ //账号是否存在
                    $push_data = array(
                        'cmd' => 'friend_request_result',
                        'code' => 1,
                        'msg' => 'account not exist',
                    );
                }else{
                    $is_friend = A('WebSocketSwoole/Friend')->isFriend($data['uid'], $search_uid);
                    if($is_friend){ //是否已经是好友
                        $push_data = array(
                            'cmd' => 'friend_request_result',
                            'code' => 2,
                            'search_account' => $data['search_account'],
                            'msg' => 'Is already friend',
                        );
                    }else{
                        $res = A('WebSocketSwoole/Request')->addFriendRequest($data['uid'], $search_uid);
                        if(!$res){
                            $push_data = array(
                                'cmd' => 'friend_request_result',
                                'code' => 3,
                                'msg' => 'add to DB fail',
                            );
                        }else{
                            $push_data = array(
                                'cmd' => 'friend_request_result',
                                'code' => 0,
                                'msg' => 'success',
                            );

                            //查询接受者是否在线，在线则转发好友请求
                            if(array_key_exists($data['search_account'], $server->users) && $server->users[$data['search_account']]['uid'] == $search_uid) {
                                $push_data2 = array(
                                    'cmd' => 'server_friend_request',
                                    'code' => 0,
                                    'request_id' => $res,
                                    'from_uid' => $data['uid'],
                                    'from_account' => $data['account'],
                                    'to_uid' => $search_uid,
                                    'to_account' => $data['search_account'],
                                    'status' => 1,
                                );
                                $server->push($server->users[$data['search_account']]['fd'], json_encode($push_data2));
                            }
                        }
                    }
                }
                $server->push($frame->fd, json_encode($push_data));
            }
//<=====================================================================================================================
//>=====================================================================================================================
            /*
             * 客户端发送添加好友请求处理结果
             */
            if($data['cmd'] == 'client_request_result'){
                $res = A('WebSocketSwoole/Request')->updateRequest($data['request_id'], $data['status']);
                if(is_array($res)){
                    if(array_key_exists($res[0]['account'], $server->users) && $server->users[$res[0]['account']]['uid'] == $res[0]['uid']){
                        $status = 0;
                        if(array_key_exists($res[1]['account'], $server->users) && $server->users[$res[1]['account']]['uid'] == $res[1]['uid']){
                            $status = $server->users[$res[1]['account']]['status'];
                        }
                        $push_data = array(
                            'cmd' => 'server_add_friend',
                            'account' => $res[1]['account'],
                            'uid' => $res[1]['uid'],
                            'nick_name' => $res[1]['nick_name'],
                            'status' => $status,
                        );
                        $server->push($server->users[$res[0]['account']]['fd'], json_encode($push_data));
                    }

                    if(array_key_exists($res[1]['account'], $server->users) && $server->users[$res[1]['account']]['uid'] == $res[1]['uid']){
                        $status = 0;
                        if(array_key_exists($res[0]['account'], $server->users) && $server->users[$res[0]['account']]['uid'] == $res[0]['uid']){
                            $status = $server->users[$res[0]['account']]['status'];
                        }
                        $push_data = array(
                            'cmd' => 'server_add_friend',
                            'account' => $res[0]['account'],
                            'uid' => $res[0]['uid'],
                            'nick_name' => $res[0]['nick_name'],
                            'status' => $status,
                        );
                        $server->push($server->users[$res[1]['account']]['fd'], json_encode($push_data));
                    }
                }
            }
//<=====================================================================================================================
        });

        $server->on('close', function($ser, $fd){
            echo "client {$fd} closed\n";

            foreach($ser->users as $k => $v){
                if($v['fd'] == $fd){
                    $uid = $v['uid'];
                    unset($ser->users[$k]);
                    break;
                }
            }

            A('WebSocketSwoole/SocketClose')->index();

            //通知该账号在线的好友
            $list = A('WebSocketSwoole/Friend')->friendList($uid);
            if(!empty($list)) {
                $push_data = array(
                    'cmd' => 'server_friend_offline',
                    'code' => 0,
                    'uid' => $uid,
                    'status' => 0,
                );
                foreach ($list as $k => $v) {
                    if (array_key_exists($v['account'], $ser->users) && $ser->users[$v['account']]['status'] > 0) {
                        $ser->push($ser->users[$v['account']]['fd'], json_encode($push_data));
                    }
                }
            }
        });

        $server->on('task', function($ser, $task_id, $from_id, $data){
            $ser->push($data[0], $data[1]);
            echo "data: $data[1]\n";
            echo "task_id: $task_id\n";
            echo "from_id: $from_id\n";
            return "$task_id result\n";
        });

        $server->on('finish', function($ser, $task_id, $data){
            echo "Task $task_id finish\n";
            echo "Result: $data\n";
        });

        $server->start();
    }
}