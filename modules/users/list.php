<?php
if (!defined('_HAU')) {
    die('Truy cập không hợp lệ');
}

// ?module=users&action=list&group=1&keyword=hau
$data = [
    'title' => 'Danh sách người dùng'
];
layout('header', $data);
layout('sidebar');

$filter = filterData();
$chuoiWhere = '';
$group = '0';
$keyword = '';

if (isGet()) {
    if (isset($filter['keyword'])) {
        $keyword = $filter['keyword'];
    }
    if (isset($filter['group'])) {
        $group = $filter['group'];
    }

    if (!empty($keyword)) {
        if (strpos($chuoiWhere, 'WHERE') == false) {
            $chuoiWhere .= ' WHERE ';
        } else {
            $chuoiWhere .= ' AND ';
        }
        $chuoiWhere .= "fullname LIKE '%$keyword%' OR email LIKE '%$keyword%' ";
    }

    if (!empty($group)) {
        if (strpos($chuoiWhere, 'WHERE') == false) {
            $chuoiWhere .= ' WHERE ';
        } else {
            $chuoiWhere .= ' AND ';
        }
        $chuoiWhere .= " group_id = $group ";
    }
}

$getDetaiUser = getAll("SELECT u.id, u.fullname, u.email, u.created_at, g.name
FROM users u INNER JOIN `groups` g 
ON u.group_id = g.id $chuoiWhere
ORDER BY u.created_at DESC
");

$getGroup = getAll("SELECT * FROM `groups`");

?>
<div class="container grid-user">
    <div class="container-fl">
        <a href="?module=users&action=add" class="btn btn-success mb-3"><i class="fa-solid fa-plus"></i>Thêm mới người dùng</a>
        <form action="" method="GET" class="mb-3">
            <input type="hidden" name="module" value="users">
            <input type="hidden" name="action" value="list">
            <div class="row">
                <div class="col-3">
                    <select name="group" id="" class="form-select form-control">
                        <option value="">Nhóm người dùng</option>
                        <?php
                        foreach ($getGroup as $item):
                        ?>
                            <option value="<?php echo $item['id']; ?>" <?php echo ($group == $item['id']) ? 'selected' : false; ?>><?php echo $item['name']; ?></option>
                        <?php
                        endforeach;
                        ?>
                    </select>
                </div>
                <div class="col-7">
                    <input type="text" value="<?php echo (!empty($keyword)) ? $keyword : false; ?>" name="keyword" class="form-control" placeholder="Nhập thông tin tìm kiếm...">
                </div>
                <div class="col-2">
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                </div>
            </div>
        </form>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th scope="col">STT</th>
                    <th scope="col">Họ tên</th>
                    <th scope="col">Email</th>
                    <th scope="col">Ngày đăng ký</th>
                    <th scope="col">Nhóm</th>
                    <th scope="col">Phân quyền</th>
                    <th scope="col">Sửa</th>
                    <th scope="col">Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($getDetaiUser as $key => $item):
                ?>
                    <tr>
                        <th scope="row"><?php echo $key + 1; ?></th>
                        <td><?php echo $item['fullname']; ?></td>
                        <td><?php echo $item['email']; ?></td>
                        <td><?php echo $item['created_at']; ?></td>
                        <td><?php echo $item['name']; ?></td>
                        <td><a href="?module=users&action=permission&id=<?php echo $item['id']; ?>" class="btn btn-primary">Phân quyền</a></td>
                        <td><a href="?module=users&action=edit&id=<?php echo $item['id']; ?>" class="btn btn-warning"><i class="fa-solid fa-pen"></i></a></td>
                        <td><a href="?module=users&action=delete&id=<?php echo $item['id']; ?>" onclick="return confirm('Bạn có chắc chắc muốn xóa không?')" class="btn btn-danger"><i class="fa-solid fa-trash"></i></a></td>
                    </tr>
                <?php
                endforeach
                ?>
            </tbody>
        </table>
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                <li class="page-item"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div>
</div>

<?php
layout('footer');
