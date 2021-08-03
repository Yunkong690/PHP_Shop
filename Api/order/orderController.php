<?php

include '../utils/dbUtils.php';
include '../utils/msg.php';
include "../utils/redisUtils.php";

session_start();
if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
        case 'makeOrder':
            $Item = $_REQUEST['item_id'];
            $data = array(
                $_REQUEST['order_id'],
                serialize($_REQUEST['item_id']),
                $_REQUEST['totalPrice'],
                $_REQUEST['totalNum'],
                $_REQUEST['postname'],
                $_REQUEST['province'] . $_REQUEST['city'] . $_REQUEST['county'] . $_REQUEST['address'],
                $_REQUEST['tel'],
                $_REQUEST['user_id'],
            );
            makeOrder($data, $Item);
            break;
        case 'makeOrderId':
            makeOrderId();
            break;
        case 'getAllOrderById':
            $user_id=$_REQUEST['user_id'];
            getAllOrderById($user_id);
            break;
        case 'payOrder':
            $Item = $_REQUEST['item_id'];
            $user_id=$_REQUEST['user_id'];
            $bill= $_REQUEST['totalPrice'];
            $order_id=$_REQUEST['order_id'];
            $num=$_REQUEST['totalNum'];
            payOrder($user_id,$order_id,$bill,$Item);
            break;
    }
}

function makeOrderId()
{
    $str = date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    $msg = new Core\msg(0, "订单号生成成功", $str);
    echo json_encode($msg->getJson());
}

function makeOrder($data, $Item)
{
    $redis = $GLOBALS['redis'];
    $dbutils = new Core\dbUtils();
    $sql = 'insert into orders set order_id=?,goodItem=?,bill=?,num=?,buyername=?,buyeraddress=?, buyertel=?,user_id=?';
    $res = $dbutils->my_exec($sql, $data);
    if ($res) {
        $msg = new Core\msg(0, "提交成功", $res);
        $redis->setex($data[0],5*60,$data[0]);
        echo json_encode($msg->getJson());
        $in = str_repeat('?,', count($Item) - 1) . '?';
        $sql = "update car set status=1 where id in ($in)";
        $res1 = $dbutils->my_exec($sql, $Item);
    } else {
        $msg = new Core\msg(500, "提交失败", $data);
        echo json_encode($msg->getJson());
    }

}


function setGoodsNum($arr){
    $redis = $GLOBALS['redis'];
    $in  = str_repeat('?,', count($arr) - 1) . '?';
    $sql="select good_id, buynum  from car where id in ($in)";
    $dbutils = new Core\dbUtils();
    $res = $dbutils->my_query($sql, $arr,false);
    $flag=false;
    foreach ($res as $value) {
        $sql1="select num  from goods where id =? for update ";
        $num = $dbutils->my_query($sql1, [$value['good_id']]);
        if($num >$value['buynum']){
            $sql="update goods set num=num-? where id =?";
            $res1 = $dbutils->my_exec($sql, [$value['buynum'],$value['good_id']]);
            $redis->hIncrBy("goods_".$value['good_id'], 'num', 0-$value['buynum']);
            if($res1){
                $flag=true;
            }else{
                $flag=false;
            }
        }
    }
    return $flag;
}

function getOrderGoods($arr){
    $in  = str_repeat('?,', count($arr) - 1) . '?';
    $sql="select *  from car where id in ($in)";
    $dbutils = new Core\dbUtils();
    $res = $dbutils->my_query($sql, $arr,false);
    $name =array();
    $name[]+=$res['name'];
    return $name;
}


function payOrder($user_id,$order_id,$bill,$Item){
    $redis = $GLOBALS['redis'];
    $money=0;
    if (isset($_COOKIE['user'])) {
        $user = unserialize($_COOKIE['user']);
        $money = $user['money'];
    }
    if($money>$bill) {
        $money = $money - $bill;
        $dbutils = new Core\dbUtils();
        $sql1 = 'update orders set status=1 where order_id=?';
        $res1 = $dbutils->my_exec($sql1, [$order_id]);
        $sql2 = 'update user set money=money-? where id=?';
        $res2 = $dbutils->my_exec($sql2, [$bill, $user_id]);
        $flag=setGoodsNum($Item);
        if ($res1 && $res2&&$flag) {
            $msg = new Core\msg(0, "下单成功");
            $redis->del($order_id);
            echo json_encode($msg->getJson());
            $user['money'] = $money;
            setcookie('user', serialize($user), time() + 7 * 24 * 3600, '/');

        } else {
                $msg = new Core\msg(500, "下单失败");
            echo json_encode($msg->getJson());
            $money = $money + $bill;
            $sql1 = 'update orders set status=0 where order_id=?';
            $res3 = $dbutils->my_exec($sql1, [$order_id]);
            $sql2 = 'update user set monery=? where id=?';
            $res4 = $dbutils->my_exec($sql2, [$money, $user_id]);
            $user['money'] = $money;
            setcookie('user', serialize($user), time() + 7 * 24 * 3600, '/');
        }
    }else{
        $msg = new Core\msg(501, "余额不足");
        echo json_encode($msg->getJson());
    }
}


function getAllOrderById($id){
    $dbutils = new Core\dbUtils();
    $sql = 'select * from orders where user_id=? ';
    $res=$dbutils->my_query($sql,[$id],false);
    if($res){
        $msg = new Core\msg(0, "获取订单成功",$res,count($res));
    }else{
        $msg = new Core\msg(400, "没有订单");
    }
    echo json_encode($msg->getJson());
}