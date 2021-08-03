<?php

ini_set('default_socket_timeout', -1);

include '../utils/dbUtils.php';
include '../utils/msg.php';
include "../utils/redisUtils.php";

$redis = $GLOBALS['redis'];
$redis->setOption(\Redis::OPT_READ_TIMEOUT, -1);
$redis->psubscribe(array('__keyevent@0__:expired'), 'keyCallback');

function keyCallback($redis, $pattern, $chan, $msg)
{
    $dbutils = new Core\dbUtils();
    $sql = 'update orders set status=3 where order_id=?';
    $res = $dbutils->my_exec($sql,[$msg]);
}
