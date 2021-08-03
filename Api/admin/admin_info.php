<?php

include '../utils/msg.php';
include '../utils/dbUtils.php';

session_start();


if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
        case 'getAdmin_Info' :
            getAdmin_Info();
            break;
        case 'updateAdmin_Info':
            updateAdmin_Info();
            break;
    }
}
function getAdmin_Info()
{
    if (isset($_SESSION['isLogin'])) {
        $admin = $_SESSION['admin'];
        $msg = new Core\msg(200, "Success", $admin);
        echo json_encode($msg->getJson());
    } else {

    }
}

function updateAdmin_Info()
{
    $dbutils = new Core\dbUtils();
    $admin = $_SESSION['admin'];
    $id = $admin['id'];
    $username = $_REQUEST['username'];
    $name = $_REQUEST['name'];
    $tel = $_REQUEST['tel'];
    $note = $_REQUEST['note'];
    $res = $dbutils->my_exec('update admin set username=? , name =? , tel =? , note=? where id=?', array($username, $name, $tel, $note, $id));
    if ($res) {
        //更新成功
        $msg = new Core\msg(200,"保存成功");
        echo json_encode($msg->getJson());
        //更新session数据
        $admin['id'] = $id;
        $admin['username'] = $username;
        $admin['name'] = $name;
        $admin['tel'] = $tel;
        $admin['note'] = $note;
        $_SESSION['admin'] = $admin;
        if ($_SESSION['autologin'] == true) {
            setcookie("admin", serialize($admin), time() + 7 * 24 * 3600);
            setcookie("code", md5($admin), time() + 7 * 24 * 3600);
        }
    } else {
        $msg = new Core\msg(500, "信息有误或未修改，保存失败");
        echo json_encode($msg->getJson());

    }
}
