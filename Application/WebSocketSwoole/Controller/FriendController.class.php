<?php

namespace WebSocketSwoole\Controller;

use Think\Controller;

class FriendController extends Controller{

    /**
     * 好友列表
     */
    public function friendList($uid){
        $list = [];
        if(!empty($uid)){
            $friendModel = M('Friend');
            $where = array(
                'f.uid1' => $uid,
            );

            $list = $friendModel->field('f.talk_id, ui.uid, ui.nick_name, u.account')
                                ->where($where)
                                ->table('friend f')
                                ->join('LEFT JOIN user_info ui ON f.uid2 = ui.uid')
                                ->join('LEFT JOIN user u ON f.uid2 = u.id')
                                ->select();
        }

        return $list;
    }

    /**
     * 判断是否好友
     */
    public function isFriend($uid1, $uid2){
        $friendModel = M('Friend');
        $where = array(
            'uid1' => $uid1,
            'uid2' => $uid2,
        );
        $count = $friendModel->where($where)->count();

        return ($count > 0 ? true : false);
    }
}