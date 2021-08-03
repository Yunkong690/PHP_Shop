<?php

include '../utils/dbUtils.php';
include '../utils/msg.php';


if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
        case 'getCar':
            $user_id=$_REQUEST['user_id'];
            getCar($user_id);
            break;
        case 'addCar':
            $user_id=$_REQUEST['user_id'];
            $good_id=$_REQUEST['good_id'];
            $buy_num=$_REQUEST['buynum'];
            addCar($user_id,$good_id,$buy_num);
            break;

    }
}

function getCar($user_id){
        $dbutils = new Core\dbUtils();
        $res = $dbutils->my_query('select *  from car where user_id=? and status=0', array($user_id), false);
        if ($res) {
            $msg = new Core\msg(0, "获取购物车信息成功", $res, count($res));
        } else {
            $msg = new Core\msg(0, "","");
        }
    echo json_encode($msg->getJson());
}

function addCar($user_id,$good_id,$buy_num){
    $dbutils = new Core\dbUtils();
    $res = $dbutils->my_query('select *  from goods where id=?', array($good_id));
    if($res){
        $cover=$res['cover'];
        $name=$res['name'];
        $price=$res['price'];
        $specs=$res['specs'];
        $sql='insert into car set cover=?,name=?,buynum=?,price=?,specs=? ,good_id=?,user_id=?';
        $res1=$dbutils->my_exec($sql,array($cover,$name,$buy_num,$price,$specs,$good_id,$user_id));
        $msg = new Core\msg(0, "添加购物车成功", $res1);
        echo json_encode($msg->getJson());
    }
}