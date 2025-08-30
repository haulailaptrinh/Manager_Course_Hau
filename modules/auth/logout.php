<?php
if (!defined('_HAU')) {
    die('Truy cập không hợp lệ');
}

$data = [
    'title' => 'Đăng nhập hệ thống'
];
layout('header-auth', $data);

if (isLogin()) {
    $token = getSession('token_login');
    $condition = "token = '" . $token . "'";
    $removeToken = delete('token_login', $condition);

    if ($removeToken) {
        removeSession('token_login');
        redirect('?module=auth&action=login');
    } else {
        setSessionFlash('msg', 'Lỗi hệ thống thống xin vui lòng thử lại.');
        setSessionFlash('msg_type', 'danger');
    }
} else {
    setSessionFlash('msg', 'Vui lòng kiểm tra dữ liệu nhập vào.');
    setSessionFlash('msg_type', 'danger');
}
