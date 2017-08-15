<?php

namespace WebSocketSwoole\Controller;

use Think\Controller;

class RequestController extends Controller{
    /**
     * 添加好友请求
     */
    public function addFriendRequest($from_uid, $to_uid){
        $requestModel = M('Request');
        $where = array(
            'from_uid' => $from_uid,
            'type' => 1,
            'to_uid' => $to_uid,
            'status' => array('neq', 3),
        );
        $request = $requestModel->field('id')->where($where)->find();

        $res = false;
        if(!empty($request)){
            $update_data = array(
                'id' => $request['id'],
                'status' => 1,
                'create_date' => date('Y-m-d H:i:s'),
            );
            $res = $requestModel->save($update_data);
            if($res !== false){
                $res = $request['id'];
            }
        }else {
            $add_data = array(
                'from_uid' => $from_uid,
                'type' => 1,
                'to_uid' => $to_uid,
                'status' => 1,
                'create_date' => date('Y-m-d H:i:s'),
            );
            $res = $requestModel->add($add_data);
        }

        return $res;
    }

    /**
     * 更新请求
     */
    public function updateRequest($request_id, $status){
        $requestModel = M('Request');
        $where = array(
            'id' => $request_id,
        );
        $request = $requestModel->field('type, from_uid, to_uid')->where($where)->find();

        if(!empty($request)){
            if($request['type'] == 1){ //好友请求
                $update_data = array(
                    'status' => $status,
                    'update_date' => date('Y-m-d H:i:s'),
                );
                $res = $requestModel->where($where)->save($update_data);

                if($res !== false){
                    if($status == 2){ //如果同意
                        $friendModel = M('Friend');
                        $where = array(
                            'uid1' => $request['from_uid'],
                            'uid2' => $request['to_uid'],
                        );
                        $count = $friendModel->where($where)->count();

                        if($count <= 0){
                            $sum = $friendModel->count();
                            $talk_id = $sum + 1;
                            $create_date = date('Y-m-d H:i:s');
                            $add_data_list = [];
                            $add_data_list[] = array(
                                'talk_id' => $talk_id,
                                'uid1' => $request['from_uid'],
                                'uid2' => $request['to_uid'],
                                'create_date' => $create_date,
                            );
                            $add_data_list[] = array(
                                'talk_id' => $talk_id,
                                'uid1' => $request['to_uid'],
                                'uid2' => $request['from_uid'],
                                'create_date' => $create_date,
                            );
                            $add_res = $friendModel->addAll($add_data_list);

                            if($add_res){
                                $userModel = M('User');
                                $where = array(
                                    'u.id' => array('in', array($request['from_uid'], $request['to_uid'])),
                                );
                                $user = $userModel->field('u.id as `uid`, u.account, ui.nick_name')
                                                  ->where($where)
                                                  ->table('user u')
                                                  ->join('LEFT JOIN user_info ui ON u.id = ui.uid')
                                                  ->select();
                                if($user){
                                    $user[0]['talk_id'] = $talk_id;
                                    $user[1]['talk_id'] = $talk_id;
                                }
                            }
                        }
                    }
                }
            }
        }

        return ((isset($user) && !empty($user)) ? $user : true);
    }

    /**
     * 请求列表
     */
    public function requestList($uid){
        $requestModel = M('Request');
        $where = array(
            'r.to_uid' => $uid,
            'r.status' => 1,
        );
        $list = $requestModel->field('r.id as request_id, r.type, r.from_uid, u.account as from_account, r.to_uid, r.status')
                             ->where($where)
                             ->table('request r')
                             ->join('LEFT JOIN `user` u ON r.from_uid = u.id')
                             ->select();

        return $list;
    }
}