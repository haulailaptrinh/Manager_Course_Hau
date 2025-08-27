<?php
if (!defined('_HAU')) {
    die('Truy cập không hợp lệ');
}

try {
    if(class_exists('PDO')){
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );
        $dns = _DRIVER . ':host='._HOST.':3366;dbname='._DB;
        $conn = new PDO($dns,_USER,_PASS,$options);
    }
} catch (Exception $ex) {
     die("Kết nối thất bại: " . $ex->getMessage());
}
