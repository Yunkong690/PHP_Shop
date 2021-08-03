<?php

include '../utils/dbUtils.php';
include '../utils/msg.php';
include '../Model/Admin.php';

session_start();
$dbutils = new Core\dbUtils();
$username = $_REQUEST['username'];
$password = $_REQUEST['password'];
$autologin = $_REQUEST['auto_Login'];
$res = $dbutils->my_query('select *  from admin where username=? and password=?', array($username, $password));

//登录信息返回
if ($res) {
    $admin['id']=$res['id'];
    $admin['username']=$res['username'];
    $admin['name']=$res['name'];
    $admin['tel']=$res['tel'];
    $admin['note']=$res['note'];
    $_SESSION['admin']=$admin;
    $_SESSION['autologin']=$autologin;
    if ($autologin == true) {// 如果记住登陆，则记录登录状态，把用户名和加密的密码放到cookie里面
        setcookie("admin",serialize($admin),time()+7*24*3600);
        setcookie("code",md5($admin),time()+7*24*3600);

    }else{
        setcookie("admin","",time()-1);
        setcookie("code","",time()-1);
    }
    $msg = new Core\msg(200, "登录成功");
    echo json_encode($msg->getJson());

} else {
    $msg = new Core\msg(403, "用户名或密码错误");
    echo json_encode($msg->getJson());
}
die();



