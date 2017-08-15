<?php

namespace WebSocketSwoole\Controller;

use Think\Controller;

class UserController extends Controller{
    /**
     * 查询账号是否存在
     */
    public function isAccountExist($account){
        $userModel = M('User');
        $where = array(
            'account' => $account,
        );
        $uid = $userModel->where($where)->getField('id');

        return empty($uid) ? false : $uid;
    }
}