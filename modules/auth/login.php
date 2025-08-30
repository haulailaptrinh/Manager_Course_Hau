<?php
/*
- Validate dữ liệu nhập vào
- Checl dữ liệu với csdl (email và pass)
- Dữ liệu khớp tokenlogin -> insert vào bảng token_login (Kiểm tra đăng nhập)

- Kiểm tra đăng nhập:
    + Gán token_login lên session
    + Trong header -> lấy token từ session về và so khớp với token trang bảng token_login
    + Nếu khớp thì điều hướng về trang đích (ko khớp điều hướng về trang login)
- Điều  hướng đến trang dashboard
- Đăng nhập tài khoản ở 1 nơi tại 1 thời điểm
*/
if (!defined('_HAU')) {
    die('Truy cập không hợp lệ');
}

$data = [
    'title' => 'Đăng nhập hệ thống'
];
layout('header-auth', $data);

if (isPost()) {
    $filter = filterData();
    $errors = [];

    // Validate email
    if (empty(trim($filter['email']))) {
        $errors['email']['require'] = 'Email bắt buộc phải nhập';
    } else {
        // Đúng định đạng email, email đã tồn tại trong xsdl chưa
        if (!validateEmail(trim($filter['email']))) {
            $errors['email']['isEmail'] = 'Email không đúng định dạng';
        }
    }

    // Validate password  MK > 6
    if (empty(trim($filter['password']))) {
        $errors['password']['require'] = 'Mật khẩu bắt buộc phải nhập';
    } else {
        if (strlen(trim($filter['password'])) < 6) {
            $errors['password']['length'] = 'Mật khẩu phải lớn hơn 6 ký từ';
        }
    }

    if (empty($errors)) {
        // Kiểm tra dữ liệu
        $email = $filter['email'];
        $password = $filter['password'];

        // Kiểm tra email
        $checkEmail = getOne("SELECT * FROM users WHERE email = '$email'");

        if (!empty($checkEmail)) {
            if (!empty($password)) {
                $checkStatus = password_verify($password, $checkEmail['password']);
                if ($checkStatus) {
                    // TK chỉ login 1 nơi
                    $user_id = $checkEmail['id'];
                    $checkAlready = getRows("SELECT * FROM token_login WHERE user_id = '$user_id'");
                    if ($checkAlready > 0) {
                        setSessionFlash('msg', 'Tài khoản đang được đăng nhập ở 1 nơi khác, vui lòng thử lại sau.');
                        setSessionFlash('msg_type', 'danger');
                        redirect('?module=auth&action=login');
                    } else {
                        // Tạo token và insert và bảng token_login
                        $token = sha1(uniqid() . time());

                        // Gán token lên sesion
                        setSessionFlash('token_login', $token);
                        $data = [
                            'user_id' => $checkEmail['id'],
                            'token' => $token,
                            'created_at' => date('Y:m:d H:i:s')
                        ];
                        $insertToken = insert('token_login', $data);
                        if ($insertToken) {
                            setSessionFlash('msg', 'Đăng nhập thành công.');
                            setSessionFlash('msg_type', 'success');

                            redirect('/');
                        } else {
                            setSessionFlash('msg', 'Đăng nhập không thành công.');
                            setSessionFlash('msg_type', 'danger');
                        }
                    }
                } else {
                    setSessionFlash('msg', 'Vui lòng kiểm tra dữ liệu nhập vào.');
                    setSessionFlash('msg_type', 'danger');
                }
            }
        }
    } else {
        setSessionFlash('msg', 'Vui lòng kiểm tra dữ liệu nhập vào.');
        setSessionFlash('msg_type', 'danger');
        setSessionFlash('oldData', $filter);
        setSessionFlash('errors', $errors);
    }
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
                        <h2 class="fw-normal mb-5 me-3">Đăng nhập hệ thống</h2>
                    </div>
                    <!-- Email input -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="text" name="email" value="<?php
                                                                if (!empty($oldData)) {
                                                                    echo oldData($oldData, 'email');
                                                                }
                                                                ?>" class="form-control form-control-lg" placeholder="Địa chỉ email" />
                        <?php
                        if (!empty($errors)) {
                            echo formErorrs($errorsArr, 'email');
                        }
                        ?>
                    </div>
                    <!-- Password input -->
                    <div data-mdb-input-init class="form-outline mb-3">
                        <input type="password" name="password" id="form3Example4" class="form-control form-control-lg"
                            placeholder="Nhập mật khẩu">
                        <?php
                        if (!empty($errors)) {
                            echo formErorrs($errorsArr, 'password');
                        }
                        ?>
                    </div>
                    <a href="<?php echo _HOST_URL; ?>?module=auth&action=forgot" class="text-body">Quên mật khẩu?</a>
                    <div class="text-center text-lg-start mt-4 pt-2">
                        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                            style="padding-left: 2.5rem; padding-right: 2.5rem;">Đăng
                            nhập</button>
                        <p class="small fw-bold mt-2 pt-1 mb-0">Bạn chưa có tài khoản? <a
                                href="<?php echo _HOST_URL; ?>?module=auth&action=register" class="link-danger">Đăng
                                ký ngay</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
</div>
<?php
layout('footer');
?>