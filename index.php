<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_start();
ob_start(); // header, cookie

require_once 'config.php';

require_once './includes/connect.php';
require_once './includes/database.php';
require_once './includes/session.php';

// Email 
require_once './includes/mailer/Exception.php';
require_once './includes/mailer/PHPMailer.php';
require_once './includes/mailer/SMTP.php';

require_once './includes/functions.php';

// $pass = '123456';
// $rel = password_hash($pass,PASSWORD_DEFAULT);
// echo $rel;

// $pass_user_inpuut = '1234656';
// $rel2 = password_verify($pass_user_inpuut,$rel);
// echo "</br>" . $rel2;
$module = _MODULES;
$action = _ACTION;

if (!empty($_GET['module'])) {
    $module = $_GET['module'];
}

if (!empty($_GET['action'])) {
    $action = $_GET['action'];
}

$path = 'modules/' . $module . '/' . $action . '.php';
if (!empty($path)) {
    if (file_exists($path)) {
        require_once $path;
    } else {
        require_once './modules/errors/404.php';
    }
} else {
    require_once './modules/errors/404.php';
}
