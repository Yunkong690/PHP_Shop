<?php

include '../utils/msg.php';

session_start();
if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
        case 'checkLogin' :
            checkLogin();
            break;
        case 'logout':
            logout();
            break;
    }
}


function checkLogin()
{
    if (isset($_COOKIE['admin'])) {
        $_SESSION['isLogin'] = 1;
    }
    if (isset($_SESSION['isLogin'])) {
        $username = $_SESSION['admin']['username'];
        $msg = new Core\msg(200, "已经登录",$username);
        echo json_encode($msg->getJson());
    } else {
        $msg = new Core\msg(403, "未登录");
        echo json_encode($msg->getJson());
    }
}


function logout()
{
    unset ($_SESSION ['admin']);
    unset($_SESSION['isLogin']);
    if (!empty ($_COOKIE ['admin']) || !empty ($_COOKIE ['code'])) {
        setcookie("admin", null, time() - 1);
        setcookie("code", null, time() - 1);
    }
    $msg = new Core\msg(200, "退出成功");
    echo json_encode($msg->getJson());
}
