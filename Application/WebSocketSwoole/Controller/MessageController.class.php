<?php

namespace WebSocketSwoole\Controller;

use Think\Controller;

class MessageController extends Controller{

    /**
     * 保存聊天消息
     */
    public function savePersonalMsg($data){
        $friendModel = M('Friend');
        $where = array(
            'uid1' => $data['from'],
            'uid2' => $data['to'],
        );
        $talk_id = $friendModel->where($where)->getField('talk_id');

        if($talk_id != ""){
            $messageModel = M('Message');
            $add_data = array(
                'talk_type' => 1,
                'talk_id' => $talk_id,
                'uid' => $data['from'],
                'msg_type' => $data['type'],
                'msg' => $data['msg'],
                'is_read' => 1,
                'create_date' => $data['send_time'],
            );
            $res = $messageModel->add($add_data);
            return $res;
        }
    }

    /**
     * 设置消息状态已读
     */
    public function setMsgIsRead($msg_id){
        $messageModel = M('Message');
        if(is_array($msg_id)){
            $where = array(
                'id' => array('in', $msg_id),
            );
            $update_data = array(
                'is_read' => 2,
                'update_date' => date('Y-m-d H:i:s'),
            );
            $messageModel->where($where)->save($update_data);
        }else{
            $update_data = array(
                'id' => $msg_id,
                'is_read' => 2,
                'update_date' => date('Y-m-d H:i:s'),
            );
            $messageModel->save($update_data);
        }
    }

    /**
     * 查询未读消息
     */
    public function unreadMsgList($uid){
        $friendModel = M('Friend');
        $where = array(
            'uid1' => $uid,
        );
        $friend_list = $friendModel->field('talk_id')->where($where)->select();
        $talk_ids = [];
        foreach($friend_list as $k => $v){
            $talk_ids[] = $v['talk_id'];
        }

        $unreadMsgList = [];
        if(!empty($talk_ids)) {
            $messageModel = M('Message');
            $where = array(
                'm.talk_id' => array('in', $talk_ids),
                'm.is_read' => 1,
            );
            $unreadMsgList = $messageModel->field('m.id as `msg_id`, m.uid as `from`, u.account as `from_account`, m.msg_type as `type`, m.msg, m.create_date as `send_time`')
                                 ->where($where)
                                 ->table('message m')
                                 ->join('LEFT JOIN `user` u ON m.uid = u.id')
                                 ->select();
        }

        return $unreadMsgList;
    }
}