<!DOCTYPE html>
<script src="https://cdn.tiny.cloud/1/2c646ifr40hywrvj32dwwml8e5qmxxr52qvzmjjq7ixbrjby/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
require_once('config/doj_db.php');
session_start();
if (!isset($_SESSION['admin_login'])) {
    echo "<script>alert('Please Login')</script>";
    echo "<meta http-equiv='refresh' content='0;url=login'>";
}

if (isset($_GET['project_id'])) {
    $project_id = $_GET['project_id'];
    $project_data = $conn->prepare("SELECT * FROM project WHERE project_id = :project_id");
    $project_data->bindParam(":project_id", $project_id);
    $project_data->execute();
    $row_project = $project_data->fetch(PDO::FETCH_ASSOC);

    $project_img = $conn->prepare("SELECT * FROM project_img WHERE project_id = :project_id");
    $project_img->bindParam(":project_id", $project_id);
    $project_img->execute();
    $row_img = $project_img->fetchAll();
}


if (isset($_POST['del-img'])) {
    $img_id = $_POST['del-img'];

    $delete_img = $conn->prepare("DELETE FROM project_img WHERE id = :id");
    $delete_img->bindParam(":id", $img_id);
    $delete_img->execute();

    if ($delete_img) {
        echo "<meta http-equiv='refresh' content='0;url=project_ref_edit?project_id=$project_id'>";
    }
}

if (isset($_POST['edit-project'])) {
    $project_name = $_POST['project_name'];
    $customer = $_POST['customer'];
    $location = $_POST['location'];
    $project_start = $_POST['project_start'];
    $project_finish = $_POST['project_finish'];
    $product_list = $_POST['product_list'];

    $project = $conn->prepare("UPDATE project SET project_name = :project_name, customer = :customer, location = :location, project_start = :project_start,
                                 project_finish = :project_finish, product_list = :product_list WHERE project_id = :project_id");
    $project->bindParam(":project_name", $project_name);
    $project->bindParam(":customer", $customer);
    $project->bindParam(":location", $location);
    $project->bindParam(":project_start", $project_start);
    $project->bindParam(":project_finish", $project_finish);
    $project->bindParam(":product_list", $product_list);
    $project->bindParam(":project_id", $project_id);
    $project->execute();


    foreach ($_FILES['img']['tmp_name'] as $key => $value) {
        $file_names = $_FILES['img']['name'];

        $extension = strtolower(pathinfo($file_names[$key], PATHINFO_EXTENSION));
        $supported = array('jpg', 'jpeg', 'png', 'webp');
        if (in_array($extension, $supported)) {
            $new_name = rand() . '.' . "webp";
            if (move_uploaded_file($_FILES['img']['tmp_name'][$key], "upload/upload_project/" . $new_name)) {
                $sql = "INSERT INTO project_img (image, project_id) VALUES(:image, :project_id)";
                $upload_img = $conn->prepare($sql);
                $params = array(
                    'image' => $new_name,
                    'project_id' => $project_id
                );
                $upload_img->execute($params);
            }
        }
    }
    if ($project) {
        echo "<script>
                $(document).ready(function() {
                    Swal.fire({
                        text: 'Edit Project has been completed.',
                        icon: 'success',
                        timer: 10000,
                        showConfirmButton: false
                    });
                })
                </script>";
        echo "<meta http-equiv='refresh' content='2;url=project_ref'>";
    } else {
        echo "<script>
                $(document).ready(function() {
                    Swal.fire({
                        text: 'Something Went Wrong!!!',
                        icon: 'error',
                        timer: 10000,
                        showConfirmButton: false
                    });
                })
                </script>";
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
    <link rel="stylesheet" href="css/project.css?v=<?php echo time(); ?>">
    <!-- <link rel="shortcut icon" href="assets/images/logo/favicon.svg" type="image/x-icon"> -->
    <link rel="shortcut icon" href="image/logodoj.png" type="image/png">

    <link rel="stylesheet" href="assets/css/shared/iconly.css">

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
                <h3>Project References Edit</h3>
            </div>
            <section class="section">
                <form method="post" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Project References</h4>
                            <button type="submit" name="edit-project" class="btn btn-edit">Save</button>
                        </div>
                        <div class="card-body">


                            <div class="content">
                                <div class="project-name">
                                    <h6>Project Name</h6>
                                    <input type="text" name="project_name" value="<?php echo $row_project['project_name'] ?>" class="form-control">
                                    <h6>Customer</h6>
                                    <input type="text" name="customer" value="<?php echo $row_project['customer'] ?>" class="form-control">
                                    <h6>Location</h6>
                                    <input type="text" name="location" value="<?php echo $row_project['location'] ?>" class="form-control">
                                    <h6>Project Start</h6>
                                    <input type="text" name="project_start" value="<?php echo $row_project['project_start'] ?>" class="form-control">
                                    <h6>Project Finish</h6>
                                    <input type="text" name="project_finish" value="<?php echo $row_project['project_finish'] ?>" class="form-control">
                                    <h6>Product List</h6>
                                    <input type="text" name="product_list" value="<?php echo $row_project['product_list'] ?>" class="form-control">
                                </div>
                                <div class="content-img">
                                    <span id="upload-img">Content Image</span>
                                    <div class="group-pos">
                                        <input type="file" name="img[]" id="imgInput" onchange="preview_image();" class="form-control" multiple>
                                        <button type="button" class="btn reset" id="reset2">Reset</button>
                                    </div>
                                    <span class="file-support">Only file are support ('jpg', 'jpeg', 'png', 'webp').</span>
                                    <div id="gallery">
                                        <?php
                                        foreach ($row_img as $row_img) { ?>
                                            <div class="box-edit-img">
                                                <span class="del-edit-img"><button type="submit" onclick="return confirm('Do you want to delete this image?')" name="del-img" value="<?php echo $row_img['id'] ?>" class="btn-edit-del-img"><i class="bi bi-x-lg"></i></button></span>
                                                <img class='previewImg' id='edit-img' src="upload/upload_project/<?php echo $row_img['image'] ?>" alt="">
                                            </div>
                                        <?php  }
                                        ?>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </form>
            </section>
            <?php include('footer.php'); ?>
        </div>
    </div>
    <script language="javascript" src="https://code.jquery.com/jquery-3.6.1.js" integrity="sha256-3zlB5s2uwoUzrXK3BT7AX3FyvojsraNFxCc2vC/7pNI=" crossorigin="anonymous"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/app.js"></script>

    <script>
        function preview_image() {
            var total_file = document.getElementById("imgInput").files.length;
            for (var i = 0; i < total_file; i++) {
                $('#gallery').append("<div class='box-edit-img'>  <span class='del-edit-img'> <button type='submit' onclick='return confirm('Do you want to delete this image?')' name='del-img' class='btn-edit-del-img'><i class='bi bi-x-lg'></i></button></span>  <img class='previewImg' id='edit-img' src='" + URL.createObjectURL(event.target.files[i]) + "'> </div>");
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#reset2').click(function() {
                $('#imgInput').val(null);
                $('.previewImg').attr("src", "");
                $('.previewImg').addClass('none');
                $('.box-edit-img').addClass('none');
            });
            $('#imgout').click(function() {
                $('#imgInput').val(null);
            });

        });
    </script>

</body>

</html>