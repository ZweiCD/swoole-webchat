<?php

namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller{

    /**
     * 登录
     */
    public function index(){
        $this->display();
    }

    /**
     * 登录处理
     */
    public function login(){
        if(IS_AJAX){
            $account = I('account', '');
            $password = I('password', '');
            if($account == '' || $password == ''){
                $data = array('code' => 1, 'msg' => 'params error');
                $this->ajaxReturn($data);
            }

            $userModel = M('User');
            $where = array(
                'account' => $account,
            );
            $user = $userModel->field('id, account, password')->where($where)->find();
            if(empty($user) || $user['password'] != md5($password)){
                $data = array('code' => 2, 'msg' => 'account or password error');
                $this->ajaxReturn($data);
            }

            $userInfoModel = M('UserInfo');
            $where = array(
                'uid' => $user['id'],
            );
            $user_info = $userInfoModel->field('nick_name')->where($where)->find();

            $session_array = array(
                'account' => $user['account'],
                'uid' => $user['id'],
                'password' => $user['password'],
                'nick_name' => $user_info['nick_name'],
            );
            session($user['account'], $session_array);

            $data = array('code' => 0, 'msg' => 'success', 'account' => $user['account']);
            $this->ajaxReturn($data);
        }
    }
}