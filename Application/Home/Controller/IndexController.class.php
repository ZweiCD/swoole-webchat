<?php

namespace Home\Controller;

use Think\Controller;

class IndexController extends Controller {

    public function index(){
        $account = I('get.account', '');
        if($account == ''){
            echo '登录失败';
            exit(0);
        }
        V('account', $account);
        $session_array = session($account);
        $uid = $session_array['uid'];
        V('uid', $uid);
        $nick_name = $session_array['nick_name'];
        V('nick_name', $nick_name);

        $this->display();
    }
}