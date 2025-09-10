<?php
if (!defined('_HAU')) {
    die('Truy cập không hợp lệ');
}

// ?module=users&action=list&group=1&keyword=hau&page=10
// Phân trang: Trước ...,3,4,'5',6,7,... Sau
// '1' ,2,3,... Sau -> Trước 1,'2',3,4...Sau
//perPage: Mỗi page bao nhiêu dữ liệu
// maxPage: Số lượng page tối đa trong csdl 
// offset: Vị trí lấy 

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

// Xử lý phân trang
$maxData = getRows("SELECT id FROM users"); // tổng dữ liệu
$perPage = 5; // số dòng dữ liệu
$maxPage = ceil($maxData / $perPage); // tính max page
$offset = 0;
$page = 1;

// get page
if (isset($filter['page'])) {
    $page = $filter['page'];
}

if ($page > $maxPage || $page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $perPage;


$getDetaiUser = getAll("SELECT u.id, u.fullname, u.email, u.created_at, g.name
FROM users u INNER JOIN `groups` g 
ON u.group_id = g.id $chuoiWhere
ORDER BY u.created_at DESC
LIMIT $offset,$perPage
");

$getGroup = getAll("SELECT * FROM `groups`");

?>
<div class="container grid-user">
    <div class="container-fl">
        <a href="?module=users&action=add" class="btn btn-success mb-3"><i class="fa-solid fa-plus"></i>Thêm mới người
            dùng</a>
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
                        <option value="<?php echo $item['id']; ?>"
                            <?php echo ($group == $item['id']) ? 'selected' : false; ?>><?php echo $item['name']; ?>
                        </option>
                        <?php
                        endforeach;
                        ?>
                    </select>
                </div>
                <div class="col-7">
                    <input type="text" value="<?php echo (!empty($keyword)) ? $keyword : false; ?>" name="keyword"
                        class="form-control" placeholder="Nhập thông tin tìm kiếm...">
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
                    <td><a href="?module=users&action=permission&id=<?php echo $item['id']; ?>"
                            class="btn btn-primary">Phân quyền</a></td>
                    <td><a href="?module=users&action=edit&id=<?php echo $item['id']; ?>" class="btn btn-warning"><i
                                class="fa-solid fa-pen"></i></a></td>
                    <td><a href="?module=users&action=delete&id=<?php echo $item['id']; ?>"
                            onclick="return confirm('Bạn có chắc chắc muốn xóa không?')" class="btn btn-danger"><i
                                class="fa-solid fa-trash"></i></a></td>
                </tr>
                <?php
                endforeach
                ?>
            </tbody>
        </table>
        <nav aria-label="Page navigation example">
            <ul class="pagination">
                <!-- Xử lý nút "Trước" -->
                <?php
                 if($page > 1): 
                 ?>
                <li class="page-item"><a class="page-link"
                        href="?module=users&action=list&page=<?php echo $page - 1; ?>">Trước</a></li>
                <?php
                 endif;
                 ?>
                <!-- Tính vị trí bắt đầu  -->
                <?php
                 $start = $page - 1;
                 if($start < 1){
                    $start = 1;
                 }
                 ?>
                <?php
                  if($start > 1): 
                 ?>
                <li class="page-item"><a class="page-link"
                        href="?module=users&action=list&page=<?php echo $page - 1; ?>">...</a></li>
                <?php
                 endif;
                 $end = $page + 1;
                 if($end > $maxPage){
                    $end = $maxPage;
                 }
                 ?>
                 <?php
                    for($i = $start; $i <= $end; $i++):
                 ?>
                <li class="page-item <?php echo($page == $i)? "active": false; ?>"><a class="page-link" 
                  href="?module=users&action=list&page=<?php echo $i; ?>"><?php echo $i ?></a></li>
                <?php
                  endfor;
                  if($end < $maxPage): 
                 ?>
                <li class="page-item"><a class="page-link"
                        href="?module=users&action=list&page=<?php echo $page + 1; ?>">...</a></li>
                <?php
                 endif;
                 ?>
                <!-- Xưe lý nút "Sau"-->
                <?php
                 if($page < $maxPage): 
                 ?>
                <li class="page-item"><a class="page-link"
                        href="?module=users&action=list&page=<?php echo $page + 1; ?>">Sau</a></li>
                <?php
                 endif;
                 ?>

            </ul>
        </nav>
    </div>
</div>

<?php
layout('footer');