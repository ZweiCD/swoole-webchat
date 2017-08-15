<?php

namespace Home\Controller;

use Think\Controller;

class RegisterController extends Controller{

    /**
     * 注册
     */
    public function index(){
        $this->display();
    }

    /**
     * 注册处理
     */
    public function register(){
        if(IS_AJAX){
            $account = I('post.account', '');
            $password = I('post.password', '');
            if($account == '' || $password == ''){
                $data = array('code' => 1, 'msg' => 'params error');
                $this->ajaxReturn($data);
            }

            $userModel = M('User');
            $where = array(
                'account' => $account,
            );
            $count = $userModel->where($where)->count();
            if($count > 0){
                $data = array('code' => 2, 'msg' => 'account already exists');
                $this->ajaxReturn($data);
            }

            $userModel->startTrans();
            $add_data = array(
                'account' => $account,
                'password' => md5($password),
                'create_date' => date('Y-m-d H:i:s'),
            );
            $user_res = $userModel->add($add_data);

            if(!$user_res){
                $userModel->rollback();
                $data = array('code' => 3, 'msg' => 'register fail');
                $this->ajaxReturn($data);
            }

            $userInfoModel = M('UserInfo');
            $add_data = array(
                'uid' => $user_res,
                'nick_name' => $account,
                'create_date' => date('Y-m-d H:i:s'),
            );
            $user_info_res = $userInfoModel->add($add_data);
            if(!$user_info_res){
                $userModel->rollback();
                $data = array('code' => 4, 'msg' => 'register fail');
                $this->ajaxReturn($data);
            }

            $userModel->commit();
            $data = array('code' => 0, 'msg' => 'success');
            $this->ajaxReturn($data);
        }
    }
}