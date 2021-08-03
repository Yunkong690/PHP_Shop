<?php
/**
 * Redis连接参数
 */

$host = '127.0.0.1';
$port = 6379;
$auth = 'Sjx1457';

//连接Redis
 global $redis;
 $redis= new Redis();
$redis->connect($host, $port);
$redis->auth($auth);
