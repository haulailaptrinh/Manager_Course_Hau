<?php
if (!defined('_HAU')) {
    die('Truy cập không hợp lệ');
}
$data = [
    'title' => 'Đặt lại mật khẩu'
];
layout('header-auth', $data);

$filterGet = filterData('get');
if (!empty($filterGet['token'])) {
    $tokenReset = $filterGet['token'];
}

if (!empty($tokenReset)) {
    // Check token có chính xác không
    $checkToken = getOne("SELECT * FROM users WHERE forget_token = '$tokenReset'");
    if (!empty($checkToken)) {
        if (isPost()) {
            $filter = filterData();
            $errors = [];

            // Validate password  MK > 6
            if (empty(trim($filter['password']))) {
                $errors['password']['require'] = 'Mật khẩu bắt buộc phải nhập';
            } else {
                if (strlen(trim($filter['password'])) < 6) {
                    $errors['password']['length'] = 'Mật khẩu phải lớn hơn 6 ký từ';
                }
            }

            // Validate comfirm password
            if (empty(trim($filter['comfirm_pass']))) {
                $errors['comfirm_pass']['require'] = 'Vui lòng nhập lại mật khẩu';
            } else {
                if (trim($filter['password']) !== trim($filter['comfirm_pass'])) {
                    $errors['comfirm_pass']['like'] = 'Mật khẩu nhập lại không khớp';
                }
            }

            if (empty($errors)) {
                $password = password_hash($filter['password'], PASSWORD_DEFAULT);
                $data = [
                    'password' => $password,
                    'forget_token' => null,
                    'updated_at' => date('Y:m:d H:i:s')
                ];
                $condition = 'id=' . $checkToken['id'];
                $updataStatus = update('users', $data, $condition);

                if ($updataStatus) {
                    // gửi email
                    $emailTo = $checkToken['email'];
                    $subject = 'Đổi mật khẩu thành công!!';
                    $content = 'Chúc mừng bạn đã đổi mật khẩu thành công trên TrungHau. <br>';
                    $content .= 'Nếu không phải bạn thao tác thì hãy liên hệ với admin. <br>';
                    $content .= 'Cảm ơn các bạn đã ủng hộ TrungHau!!!';

                    // Gửi Email
                    sendMail($emailTo, $subject, $content);

                    setSessionFlash('msg', 'Gửi đổi mật khẩu thành công');
                    setSessionFlash('msg_type', 'success');
                } else {
                    setSessionFlash('msg', 'Đã có lỗi xảy ra vui lòng thử lại.');
                    setSessionFlash('msg_type', 'danger');
                }
            } else {
                setSessionFlash('msg', 'Vui lòng kiểm tra dữ liệu nhập vào.');
                setSessionFlash('msg_type', 'danger');
                setSessionFlash('oldData', $filter);
                setSessionFlash('errors', $errors);
            }
        }
    } else {
        setSessionFlash('msg', 'Liên kết hết hạn hoặc không tồn tại.');
        setSessionFlash('msg_type', 'danger');
    }
} else {
    setSessionFlash('msg', 'Liên kết hết hạn hoặc không tồn tại.');
    setSessionFlash('msg_type', 'danger');
}


$msg = getSessionFlash('msg');
$msg_type = getSessionFlash('msg_type');
$oldData = getSessionFlash('oldData');
$errorsArr = getSessionFlash('errors');
?>

<section class="vh-100">
    <div class="container-fluid h-custom">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-md-9 col-lg-6 col-xl-5">
                <img src="<?php echo _HOST_URL_TEMPLATES; ?>/assets/image/draw2.webp" class="img-fluid"
                    alt="Sample image">
            </div>
            <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                <?php
                if (!empty($msg) && !empty($msg_type)) {
                    getMsg($msg, $msg_type);
                }
                ?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
                        <h2 class="fw-normal mb-5 me-3">Đặt lại mật khẩu</h2>
                    </div>
                    <!-- Pass mới-->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="password" name="password" class="form-control form-control-lg"
                            placeholder="Nhập mật khẩu mới" />
                        <?php
                        if (!empty($errors)) {
                            echo formErorrs($errorsArr, 'password');
                        }
                        ?>
                    </div>
                    <!-- Nhập lại pass mới -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="password" name="comfirm_pass" class="form-control form-control-lg"
                            placeholder="Nhập lại mật khẩu mới" />
                        <?php
                        if (!empty($errors)) {
                            echo formErorrs($errorsArr, 'comfirm_pass');
                        }
                        ?>
                    </div>
                    <div class="text-center text-lg-start mt-4 pt-2">
                        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                            style="padding-left: 2.5rem; padding-right: 2.5rem;">Gửi</button>
                    </div>
                    <p style="    margin-top: 15px;">
                        <a href="<?php echo _HOST_URL; ?>?module=auth&action=login" class="link-danger">Đăng
                            nhập</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>
</div>

<?php layout('footer'); ?>