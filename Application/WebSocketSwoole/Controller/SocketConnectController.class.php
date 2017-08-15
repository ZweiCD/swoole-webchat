<?php

namespace WebSocketSwoole\Controller;

use Think\Controller;

class SocketConnectController extends Controller{

    public function index($data, $fd){
        $status = false;
        $account = $data['account'];
        $uid = $data['uid'];

        $user = M('User');
        $where = array(
            'id' => $uid,
            'account' => $account,
        );
        $count = $user->where($where)->count();
        if($count > 0){
            $status = true;
            $update_data = array(
                'last_login_time' => date('Y-m-d H:i:d'),
            );
            $user->where($where)->save($update_data);
        }

        return $status;
    }
}