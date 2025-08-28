<?php
if (!defined('_HAU')) {
    die('Truy cập không hợp lệ');
}
$data = [
    'title' => 'Đăng ký tài khoản'
];
layout('header-auth', $data);


if (isPost()) {
    $filter = filterData();
    $errors = [];
    // Validate fullnamme
    if (empty(trim($filter['fullname']))) {
        $errors['fullname']['require'] = 'Họ tên bắt buộc phải nhập';
    } else {
        if (trim(strlen($filter['fullname']) < 5)) {
            $errors['fullname']['length'] = 'Họ tên phải lớn hơn 5 ký tự';
        }
    }

    // Validate email
    if (empty(trim($filter['email']))) {
        $errors['email']['require'] = 'Email bắt buộc phải nhập';
    } else {
        // Đúng định đạng email, email đã tồn tại trong xsdl chưa
        if (!validateEmail(trim($filter['email']))) {
            $errors['email']['isEmail'] = 'Email không đúng định dạng';
        } else {
            $email = $filter['email'];

            $checkEmail = getRows("SELECT * FROM users WHERE email = '$email' ");
            if ($checkEmail > 0) {
                $errors['email']['check'] = 'Email đã tồn tại';
            }
        }
    }

    // Validate phone
    if (empty($filter['phone'])) {
        $errors['phone']['require'] = 'Số điện thoại bắt buộc phải nhập';
    } else {
        if (!isPhone($filter['phone'])) {
            $errors['phone']['isPhone'] = 'Số điện thoại không đúng định dạng';
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

    // Validate comfirm password
    if (empty(trim($filter['comfirm_pass']))) {
        $errors['comfirm_pass']['require'] = 'Vui lòng nhập lại mật khẩu';
    } else {
        if (trim($filter['password']) !== trim($filter['comfirm_pass'])) {
            $errors['comfirm_pass']['like'] = 'Mật khẩu nhập lại không khớp';
        }
    }

    if (empty($errors)) {
        // table: users, data
        $activeToken = sha1(uniqid().time());
        $data = [
          'fullname' => $filter['fullname'],
          'address'  => $filter['address'] ?? '',
          'phone'    => $filter['phone'],
          'password' => password_hash($filter['password'], PASSWORD_DEFAULT),
          'email'    => $filter['email'],
          'active_token' => $activeToken,
          'group_id'    => 1,
          'created_at'  =>  date('Y:m:d H:i:s')
        ];

        $insertStatus = insert('users', $data);

        if ($insertStatus) {
            $emailTo = $filter['email'];
            $subject = 'Kích hoạt tài khoản hệ thống TrungHau!!';
            $content = 'Chúc mừng bạn đã đăng ký thành công tài khoản tại TrungHau. <br>';
            $content .= 'Để kích hoạt tài khoản, bạn hãy vui lòng click vào đường link bên dưới: <br>';
            $content .= _HOST_URL . '/?module=auth&action=active&token=' . $activeToken . '<br>';
            $content .= 'Cảm ơn các bạn đã ủng hộ TrungHau!!!';

            // Gửi Email
            sendMail($emailTo,$subject,$content);

            setSessionFlash('msg','Đăng ký thành công, vui lòng kích hoạt tài khoản.');
            setSessionFlash('msg_type','success');
        } else {
            setSessionFlash('msg','Đăng ký không thành công, vui lòng kích hoạt tài khoản.');
            setSessionFlash('msg_type','danger');
        }
    } else {
        setSessionFlash('msg','Vui lòng kiểm tra dữ liệu nhập vào.');
        setSessionFlash('msg_type','danger');
        setSessionFlash('oldData', $filter);
        setSessionFlash('errors', $errors);
    }

    $msg = getSessionFlash('msg');
    $msg_type = getSessionFlash('msg_type');
    $oldData = getSessionFlash('oldData');
    $errorsArr = getSessionFlash('errors');
}

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
                if(!empty($msg) && !empty($msg_type)){
                    getMsg($msg, $msg_type);
                }
                 ?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
                        <h2 class="fw-normal mb-5 me-3">Đăng nhập hệ thống</h2>
                    </div>
                    <!-- Họ tên -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="fullname" type="text" class="form-control form-control-lg" value="<?php
                            if(!empty($oldData)){
                                echo oldData($oldData, 'fullname');
                            }
                             ?>" placeholder="Họ tên" />
                        <?php
                        if(!empty($errors)){
                             echo formErorrs($errorsArr, 'fullname');
                        }
                        ?>
                    </div>
                    <!-- Email -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="email" type="text" class="form-control form-control-lg"
                            value="<?php 
                            if(!empty($oldData)){
                                echo oldData($oldData, 'email');
                            }
                            ?>" placeholder="Địa chỉ email" />
                        <?php 
                        if(!empty($errors)){
                            echo formErorrs($errorsArr, 'email');   
                        }
                        ?>
                    </div>
                    <!-- Phone -->
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input name="phone" type="text" class="form-control form-control-lg"
                            value="<?php 
                            if(!empty($oldData)){
                                 echo oldData($oldData ?? [], 'phone');
                            }
                            ?>" placeholder="Nhập số điện thoại" />
                        <?php 
                        if(!empty($errorsArr)){
                            echo formErorrs($errorsArr, 'phone'); 
                        }
                        ?>
                    </div>
                    <!-- Password -->
                    <div data-mdb-input-init class="form-outline mb-3">
                        <input name="password" type="password" id="form3Example4" class="form-control form-control-lg"
                            placeholder="Nhập mật khẩu">
                        <?php 
                        if(!empty($errors)){
                            echo formErorrs($errorsArr, 'password'); 
                        }
                        ?>
                    </div>
                    <!-- Nhập lại mật khẩu -->
                    <div data-mdb-input-init class="form-outline mb-3">
                        <input name="comfirm_pass" type="password" id="form3Example4"
                            class="form-control form-control-lg" placeholder="Nhập lại mật khẩu">
                        <?php 
                        if(!empty($errors)){
                             echo formErorrs($errorsArr ?? [], 'comfirm_pass'); 
                        }
                        ?>
                    </div>
                    <div class="text-center text-lg-start mt-4 pt-2">
                        <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-lg"
                            style="padding-left: 2.5rem; padding-right: 2.5rem;">Đăng
                            ký</button>
                        <p class="small fw-bold mt-2 pt-1 mb-0">Bạn đã có tài khoản? <a
                                href="<?php echo _HOST_URL;?>?module=auth&action=login" class="link-danger">Đăng
                                nhập ngay</a></p>
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