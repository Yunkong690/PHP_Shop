<?php
include '../utils/dbUtils.php';
include '../utils/msg.php';
include "../utils/redisUtils.php";

if (isset($_REQUEST['action']) && !empty($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
        case 'getGoodbyId':
            $id = $_REQUEST['id'];
            getGoodbyId($id);
            break;
        case 'getAllGoods':
            getAllGoods();
            break;
        case 'updateGoodbyId':
            $data = array(
                $_REQUEST['good_title'],
                $_REQUEST['type'],
                $_REQUEST['good_num'],
                $_REQUEST['price'],
                $_REQUEST['cover'],
                $_REQUEST['good_detail'],
                $_REQUEST['good_specs'],
                $_REQUEST['type_id'],
                $_REQUEST['id']);
            $goods['id'] = $_REQUEST['id'];
            $goods['name'] = $_REQUEST['good_title'];
            $goods['type'] = $_REQUEST['type'];
            $goods['type_id'] = $_REQUEST['type_id'];
            $goods['num'] = $_REQUEST['good_num'];
            $goods['price'] = $_REQUEST['price'];
            $goods['specs'] = $_REQUEST['good_specs'];
            $goods['cover'] = $_REQUEST['cover'];
            $goods['detail'] = $_REQUEST['good_detail'];
            $old_type = $_REQUEST['old_type'];
            updateGoodbyId($data, $old_type, $goods);
            break;
        case 'addGoods':
            $data = array(
                $_REQUEST['good_title'],
                $_REQUEST['type'],
                $_REQUEST['good_num'],
                $_REQUEST['price'],
                $_REQUEST['cover'],
                $_REQUEST['good_detail'],
                $_REQUEST['good_specs'],
                $_REQUEST['type_id']);

            $goods['name'] = $_REQUEST['good_title'];
            $goods['type'] = $_REQUEST['type'];
            $goods['type_id'] = $_REQUEST['type_id'];
            $goods['num'] = $_REQUEST['good_num'];
            $goods['price'] = $_REQUEST['price'];
            $goods['specs'] = $_REQUEST['good_specs'];
            $goods['cover'] = $_REQUEST['cover'];
            $goods['detail'] = $_REQUEST['good_detail'];
            addGoods($data, $goods);
            break;
        case 'deleteGoodsById':
            $arr = array();
            $type_id = array();
            //$arr=$_REQUEST['good_id'];
            array_push($arr, $_REQUEST['good_id']);
            //$type_id=$_REQUEST['type_id'];
            array_push($type_id, $_REQUEST['type_id']);
            deleteGoodsById($arr, $type_id);
            unset($arr);
            break;
        case 'getGoodsByType':
            $type_id = $_REQUEST['type_id'];
            getGoodsByType($type_id);
            break;
        case 'GoodsLimit':
            $limit = $_REQUEST['limit'];
            $page = $_REQUEST['page'];
            GoodsLimit($limit,$page);
            break;
    }
}


function getGoodbyId($id)
{
    $redis = $GLOBALS['redis'];
    $res = $redis->hgetall("goods_" . $id);
//    $dbutils = new Core\dbUtils();
//    $res = $dbutils->my_query('select *  from goods where id=?', array($id));
    if ($res) {
        $msg = new Core\msg(200, "????????????????????????", $res);
        echo json_encode($msg->getJson());
    } else {
        $msg = new Core\msg(200, "????????????????????????");
        echo json_encode($msg->getJson());
    }

}

function updateGoodbyId($data, $old_type, $goods)
{
    $dbutils = new Core\dbUtils();
    $sql = 'update goods set name=?,type=?,num=?,price=?,cover=?,detail=? ,specs=?,type_id=? where id=?';
    $res = $dbutils->my_exec($sql, $data);
    if ($res) {
        $redis = $GLOBALS['redis'];
        $redis->lrem("type_" . $old_type, $goods['id'], 0);//???????????????
        $redis->hmset("goods_" . $goods['id'], $goods);
        $redis->lpush("type_" . $goods['type_id'], $goods['id']);
        $msg = new Core\msg(0, "????????????????????????");
        echo json_encode($msg->getJson());
    } else {
        $msg = new Core\msg(404, "????????????????????????");
        echo json_encode($msg->getJson());
    }
}


function getAllGoods()
{
    $redis = $GLOBALS['redis'];
    $goodlist = $redis->lrange("all_goods", 0, -1);
    $res = (array)null;
    foreach ($goodlist as $id) {
        $res[] = $redis->hgetall("goods_" . $id);
    }
    if ($res) {
        $msg = new Core\msg(0, "????????????????????????", $res, count($res));
    } else {
        $dbutils = new Core\dbUtils();
        $res1 = $dbutils->my_query('select *  from goods', "", false);
        if ($res1) {
            $redis = $GLOBALS['redis'];
            foreach ($res1 as $value) {
                $redis->hmset("goods_" . $value['id'], $value);
                $redis->lpush("type_" . $value['type_id'], $value['id']);
                $redis->lpush("all_goods", $value['id']);
            }
            $msg = new Core\msg(0, "????????????????????????", $res1, count($res));
        } else {
            $msg = new Core\msg(404, "????????????????????????");
        }
    }
    echo json_encode($msg->getJson());
}

function addGoods($data, &$goods)
{
    $redis = $GLOBALS['redis'];
    $dbutils = new Core\dbUtils();
    $sql = 'insert into goods set name=?,type=?,num=?,price=?,cover=?,detail=? ,specs=?,type_id=?';
    $res = $dbutils->my_exec($sql, $data);
    $id = $dbutils->my_last_insert_id();
    $goods['id'] = $id;
    if ($res) {
        $redis->hmset("goods_" . $goods['id'], $goods);
        $redis->lpush("type_" . $goods['type_id'], $goods['id']);
        $redis->lpush("all_goods", $goods['id']);
        $msg = new Core\msg(0, "??????????????????" . $id, $res);
        echo json_encode($msg->getJson());
    } else {
        $msg = new Core\msg(404, "??????????????????");
        echo json_encode($msg->getJson());
    }
}


function deleteGoodsById($arr, $type_id)
{
    $redis = $GLOBALS['redis'];
    $dbutils = new Core\dbUtils();
    $in = str_repeat('?,', count($arr) - 1) . '?';
    $sql = "delete from goods where id in ($in)";
    $res = $dbutils->my_exec($sql, $arr);

    if ($res) {
        $msg = new Core\msg(0, "??????????????????", $res, count($res));
        foreach ($arr as $key => $value) {
            $redis->del("goods_" . $value);
            $redis->lrem('all_goods', $value, 0);
            $redis->lrem("type_" . $type_id[$key], $value, 0);
        }
    } else {
        $msg = new Core\msg(404, "??????????????????", $type_id);
    }
    echo json_encode($msg->getJson());
}

function getGoodsByType($type_id)
{
    $redis = $GLOBALS['redis'];
    $goodlist = $redis->lrange("type_" . $type_id, 0, -1);
    $dbutils = new Core\dbUtils();
    $res = (array)null;
    foreach ($goodlist as $id) {
        $res[] = $redis->hgetall("goods_" . $id);
    }
    if ($res) {
        $msg = new Core\msg(0, "????????????????????????", $res, count($res));
    } else {
        $sql = 'select * from goods where type_id =?';
        $res1 = $dbutils->my_query($sql, [$type_id], false);
        if ($res1) {
            $msg = new Core\msg(0, "????????????????????????", $res1, count($res));
        }
    }
    echo json_encode($msg->getJson());
}

function GoodsLimit($limit,$page){
# ?????????????????????$rows???????????????
    $dbutils = new Core\dbUtils();
    $rows = $dbutils->my_query('select *  from goods', "", false);
    $sort_num = array_column($rows,'type_id');
    array_multisort($sort_num,SORT_DESC,$rows, SORT_DESC);
    $datas = array();
    $datas = showpage($rows,$limit,$page);
    $items = array();
    $msg = new Core\msg(0, "????????????????????????", $datas['rows'], $datas['tot']);
    echo json_encode($msg->getJson());
}



function showpage($rows,$count,$page){
    $tot = count($rows); // ???????????????

    // $count = $count; # ??????????????????

    $countpage = ceil($tot/$count); # ??????????????????

    $start = ($page-1)*$count; # ????????????????????????

    $datas = array_slice($rows, $start, $count); # ?????????????????????

    # ???????????????????????????
    if ($page > 1) {
        $uppage = $page-1;
    }else{
        $uppage = 1;
    }

    if ($page < $countpage) {
        $nextpage = $page+1;
    }else{
        $nextpage = $countpage;
    }

    $pages['countpage'] = $countpage;
    $pages['page'] = $page;
    $pages['uppage'] = $uppage;
    $pages['nextpage'] = $nextpage;
    $pages['tot'] = $tot;

    //?????????????????? , ????????????$i?????????????????????
    $n = 1;
    foreach ($datas as &$data) {
        $data['n'] = $n;
        $n++;
    }

    $pages['rows'] = $datas;

    return $pages;
}