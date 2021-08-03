<?php

include '../utils/dbUtils.php';
include '../utils/msg.php';
include "../utils/redisUtils.php";


session_start();

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
        case 'Login' :
            Login();
            break;
        case 'Logout' :
            $user_id = $_REQUEST['user_id'];
            Logout($user_id);
            break;
        case 'getAddress' :
            $user_id = $_REQUEST['user_id'];
            getAddress($user_id);
            break;
        case 'checkLogin':
            $user_id = $_REQUEST['user_id'];
            $token = $_REQUEST['token'];
            checkLogin($user_id, $token);
            break;
    }
}
function Login()
{
    $dbutils = new Core\dbUtils();
    $username = $_REQUEST['username'];
    $password = $_REQUEST['password'];
    $autologin = $_REQUEST['auto_Login'];
    $res = $dbutils->my_query('select *  from user where username=? and password=?', [$username, $password]);
    if ($res) {
        $uniqid = md5(uniqid(microtime(true), true));
        $token = md5($username . $password . $uniqid);
        $user['id'] = $res['id'];
        $user['username'] = $res['username'];
        $user['nickname'] = $res['nickname'];
        $user['sex'] = $res['sex'];
        $user['address'] = $res['address'];
        $user['tel'] = $res['tel'];
        $user['name'] = $res['name'];
        $user['money'] = $res['money'];
        $user['token'] = $token;
        $_SESSION['user'] = $user;
        $redis = $GLOBALS['redis'];
        $redis->set($user['id'] . '_token', $token);
        if ($autologin === 'true') {// 如果记住登陆，则记录登录状态，把用户名和token放到cookie里面
            setcookie('user', serialize($user), time() + 7 * 24 * 3600, '/');
            setcookie('token', $token, time() + 7 * 24 * 3600, '/');
            setcookie('user_id', $user['id'], time() + 7 * 24 * 3600, '/');
            setcookie('username', $username,time() + 7 * 24 * 3600,'/');

        } else {
            setcookie('username', $username,"",'/');
            setcookie('user_id', $user['id'], "", '/');
            setcookie('user', serialize($user), time() - 1);
            setcookie('token', $token, "",'/');

        }
        $msg = new Core\msg(200, "登录成功");
        echo json_encode($msg->getJson());
    } else {
        $msg = new Core\msg(403, '用户名或密码错误');
        echo json_encode($msg->getJson());
    }
}

function checkLogin($user_id, $token)
{
    $redis = $GLOBALS['redis'];
    $redis_token = $redis->get($user_id . '_token');
    if ($redis_token == $token) {
        $_SESSION['isLogin'] = 1;
    }
    if (isset($_SESSION['isLogin'])) {
        $user = unserialize($_COOKIE['user']);
        $username = $user['username'];
        $msg = new Core\msg(200, "已经登录", $username);
        echo json_encode($msg->getJson());
    } else {
        $msg = new Core\msg(403, "未登录");
        echo json_encode($msg->getJson());
    }
}

function getAddress($user_id)
{
    $dbutils = new Core\dbUtils();
    $res = $dbutils->my_query('select name,tel,address from user where id=?', [$user_id]);
    if($res){
        $msg = new Core\msg(0, "获取收货地址成功", $res);
        echo json_encode($msg->getJson());
    }else{
        $msg = new Core\msg(404, "获取收货地址失败");
        echo json_encode($msg->getJson());
    }
}

function Logout($user_id){
    setcookie('username', '',time() - 1,'/');
    setcookie('user_id','', time() - 1,'/');
    setcookie('user', '', time() - 1,'/');
    setcookie('token', '', time() - 1,'/');
    $redis = $GLOBALS['redis'];
    $redis->del($user_id . '_token');
   Header("Location:   ../../Web/pay/index.html");
}