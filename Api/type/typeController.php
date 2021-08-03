<?php
include '../utils/dbUtils.php';
include '../utils/msg.php';


if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
        case 'getAllType':
            getAllType();
            break;
    }
}

function getAllType(){
    $dbutils = new Core\dbUtils();
    $res = $dbutils->my_query('select *  from type', "", false);
    if ($res) {
        $msg = new Core\msg(0, "获取商品类别", $res, count($res));
    } else {
        $msg = new Core\msg(404, "获取商品类别");
    }
    echo json_encode($msg->getJson());
}