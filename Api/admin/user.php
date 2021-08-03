<?php

session_start();
include '../utils/msg.php';
include '../utils/dbUtils.php';

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
        case 'getAllUser' :
            getAllUser();
            break;
    }
}

function getAllUser(){
    if(isset($_SESSION['isLogin'])){
        $dbutils = new Core\dbUtils();
        $sql="select id,username,nickname,sex,address,tel,name,money,status  from user ";
        $res=$dbutils->my_query($sql,"",false);
        $msg = new Core\msg(0, "Success",$res,count($res));
        echo json_encode($msg->getJson());
    }
}