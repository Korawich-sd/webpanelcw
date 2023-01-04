<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
require_once('config/doj_db.php');
session_start();
error_reporting(0);
if (!isset($_SESSION['admin_login'])) {
    echo "<script>alert('Please Login')</script>";
    echo "<meta http-equiv='refresh' content='0;url=login'>";
}


$data_service = $conn->prepare("SELECT * FROM service ORDER BY service_id DESC");
$data_service->execute();
$row_service = $data_service->fetchAll();

if (isset($_POST['change-status'])) {
    $check = $_POST['check'];
    $service_id = $_POST['service_id'];
   
    $stmt = $conn->prepare("UPDATE service SET status = :status WHERE service_id =  :service_id");
    $stmt->bindParam(":status", $check);
    $stmt->bindParam(":service_id", $service_id);
    $stmt->execute();

    if ($stmt) {
        echo "<script>
        $(document).ready(function() {
            Swal.fire({
                text: 'Change Status Success',
                icon: 'success',
                timer: 10000,
                showConfirmButton: false
            });
        })
        </script>";
        echo "<meta http-equiv='refresh' content='2;url=service'>";
    } else {
        echo "<script>alert('Something Went Wrong!!!')</script>";
        echo "<meta http-equiv='refresh' content='2;url=service'>";
    }
}


if (isset($_POST['delete_all'])) {
    if (count((array)$_POST['ids']) > 0) {
        $all = implode(",", $_POST['ids']);
        $del_service = $conn->prepare("DELETE FROM service WHERE service_id in ($all)");
        $del_service->execute();

        if ($del_service) {
            $del_img = $conn->prepare("DELETE FROM service_img WHERE service_id in ($all)");
            $del_img->execute();

            echo "<script>
            $(document).ready(function() {
                Swal.fire({
                    text: 'Delete Service Success',
                    icon: 'success',
                    timer: 10000,
                    showConfirmButton: false
                });
            })
            </script>";
            echo "<meta http-equiv='refresh' content='2;url=service'>";
        } else {
            echo "<script>alert('Something Went Wrong!!!')</script>";
            echo "<meta http-equiv='refresh' content='2;url=service'>";
        }
    }else{
        echo "<script>
        $(document).ready(function() {
            Swal.fire({
                text: 'You must select an item before delete',
                icon: 'warning',
                timer: 10000,
                showConfirmButton: false
            });
        })
        </script>";
        echo "<meta http-equiv='refresh' content='2;url=service'>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mazer Admin Dashboard</title>


    <link rel="stylesheet" href="assets/css/main/app.css">
    <link rel="stylesheet" href="assets/css/main/app-dark.css">
    <!-- <link rel="shortcut icon" href="assets/images/logo/favicon.svg" type="image/x-icon"> -->
    <link rel="shortcut icon" href="image/logodoj.png" type="image/png">

    <link rel="stylesheet" href="assets/css/shared/iconly.css">
    <link rel="stylesheet" href="css/service.css">
</head>

<body>
<div id="app">
        <?php include('sidebar.php'); ?>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <h3>Service</h3>
            </div>
            <section class="section">
                <form method="post">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Service</h4>
                            <div class="flex-end">

                                <a href="service_add"><button type="button" class="btn btn-edit">Add</button></a>
                                <button type="submit" onclick="return confirm('Do you want to delete all?');" name="delete_all" class="btn btn-del">Delete</button>

                            </div>

                        </div>


                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead align="center">
                                        <tr>
                                            <th scope="col">
                                                <input type="checkbox" class="form-check-input checkbox-select" id="select_all">
                                            </th>
                                            <th scope="col">Cover Image</th>
                                            <th scope="col">Service Name</th>
                                            <th scope="col">Creation Date</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Manage</th>
                                        </tr>
                                    </thead>
                                    <?php
                                    for ($i = 0; $i < count($row_service); $i++) {
                                        $p_id = $row_service[$i]["service_id"];
                                        $img =  $conn->prepare("SELECT * FROM service_img WHERE service_id = :service_id");
                                        $img->bindParam(":service_id", $p_id);
                                        $img->execute();
                                        $row_img = $img->fetchAll();

                                    ?>
                                        <tbody>
                                            <tr>
                                                <td align="center">
                                                    <input type="checkbox" class="form-check-input checkbox checkbox-select" name="ids[]" value=<?php echo $row_service[$i]['service_id'] ?>>
                                                </td>
                                                <td align="center" width="20%">
                                                    <img width="50%" src="upload/upload_service/<?php echo $row_service[$i]['cover_img']; ?>" alt="">
                                                </td>
                                                <td align="left"><?php echo $row_service[$i]['service_name']; ?></td>
                                                <td align="center"><?php echo $row_service[$i]['created_at']; ?></td>
                                                <td align="center">
                                                    <a type="input" class="btn" <?php if ($row_service[$i]['status'] == "on") {
                                                                                    echo " style='background-color: #06c258'";
                                                                                } else {
                                                                                    echo " style='background-color: #ff4122'";
                                                                                } ?> data-bs-toggle="modal" href="#status<?php echo $row_service[$i]['service_id'] ?>" id="setting"><i class="bi bi-gear"></i></a>

                                                </td>
                                                <td align="center">
                                                    <div class="manage">
                                                      <a href="service_edit?service_id=<?php echo $row_service[$i]['service_id']; ?>"><button type="button" class="btn" style="background-color:#ffc107; color: #FFFFFF;"><i class="bi bi-pencil-square"></i></button></a>  
                                                        <button class="btn" onclick="return confirm('Do you want to delete?');" name="delete_all" style="background-color:#ff4122; color: #FFFFFF;"><i class="bi bi-trash"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <div class="modal fade" id="status<?php echo $row_service[$i]['service_id'] ?>" data-bs-backdrop="static" aria-hidden="true">
                                            <div class="modal-dialog  modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Status Setting</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-check form-switch">
                                                            <form method="post">
                                                                <div class="switch-box">
                                                                    <span>OFF</span>
                                                                    <input type="hidden" name="service_id" value="<?php echo $row_service[$i]['service_id']; ?>">
                                                                    <input class="form-check-input" id="switch-check" name="check" type="checkbox" <?php if ($row_service[$i]['status'] == "on") {
                                                                                                                                                        echo "checked";
                                                                                                                                                    } else {
                                                                                                                                                        echo "";
                                                                                                                                                    } ?>>
                                                                    <span>ON</span>
                                                                </div>
                                                                <div class="box-btn">
                                                                    <button name="change-status" class="btn btn-status" type="submit">Save</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                    ?>

                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
            <?php include('footer.php'); ?>
        </div>
    </div>

    <script>
        //for checkbox
        $(document).ready(function() {
            $('#select_all').on('click', function() {
                if (this.checked) {
                    $('.checkbox').each(function() {
                        this.checked = true;
                    })
                } else {
                    $('.checkbox').each(function() {
                        this.checked = false;
                    })
                }
            })
            $('.checkbox').on('click', function() {
                if ($('.checkbox:checked').length == $('.checkbox').length) {
                    $('#select_all').prop('checked', true);
                } else {
                    $('#select_all').prop('checked', false);
                }
            })
        });
    </script>

    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/app.js"></script>
</body>

</html>