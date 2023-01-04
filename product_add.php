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

if (isset($_POST['add-product'])) {
    $product_name = $_POST['product_name'];
    $intro = $_POST['intro'];
    $content = $_POST['content'];
    $conclude = $_POST['conclude'];
    $link = $_POST['link'];
    $img_cover = $_FILES['img-cover'];
    $status = "on";

    $allow = array('jpg', 'jpeg', 'png', 'webp');
    $extention1 = explode(".", $img_cover['name']); //เเยกชื่อกับนามสกุลไฟล์
    $fileActExt1 = strtolower(end($extention1)); //แปลงนามสกุลไฟล์เป็นพิมพ์เล็ก
    $fileNew1 = rand() . "." . "webp";
    $filePath1 = "upload/upload_product/" . $fileNew1;

    if (empty($product_name)) {
        echo "<script>alert('Please Enter Product Name')</script>";
    } else if (empty($intro)) {
        echo "<script>alert('Please Enter Introduction')</script>";
    } else if (empty($content)) {
        echo "<script>alert('Please Enter Content')</script>";
    } else if (empty($conclude)) {
        echo "<script>alert('Please Enter Conclusion')</script>";
    } else {
        try {

            if (in_array($fileActExt1, $allow)) {
                if ($img_cover['size'] > 0 && $img_cover['error'] == 0) {
                    if (move_uploaded_file($img_cover['tmp_name'], $filePath1)) {
                        $product = $conn->prepare("INSERT INTO product(product_name, intro, content, conclude, link_video, cover_img ,status)
                                        VALUES(:product_name, :intro, :content, :conclude, :link_video, :cover_img,:status)");
                        $product->bindParam(":product_name", $product_name);
                        $product->bindParam(":intro", $intro);
                        $product->bindParam(":content", $content);
                        $product->bindParam(":conclude", $conclude);
                        $product->bindParam(":link_video", $link);
                        $product->bindParam(":cover_img", $fileNew1);
                        $product->bindParam(":status", $status);
                        $product->execute();

                        $id_product = $conn->lastInsertId();
                    }
                }
            }



            foreach ($_FILES['img']['tmp_name'] as $key => $value) {
                $file_names = $_FILES['img']['name'];

                $extension = strtolower(pathinfo($file_names[$key], PATHINFO_EXTENSION));
                $supported = array('jpg', 'jpeg', 'png', 'webp', 'mp4');
                if (in_array($extension, $supported)) {
                    $new_name = rand() . '.' . "webp";
                    if (move_uploaded_file($_FILES['img']['tmp_name'][$key], "upload/upload_product/" . $new_name)) {
                        $sql = "INSERT INTO product_img (image, product_id) VALUES(:image, :product_id)";
                        $upload_img = $conn->prepare($sql);
                        $params = array(
                            'image' => $new_name,
                            'product_id' => $id_product
                        );
                        $upload_img->execute($params);
                    }
                } else {
                    echo "<script>alert('ไม่รองรับนามสกุลไฟล์นี้')</script>";
                }
            }
            if ($product) {
                echo "<script>
                $(document).ready(function() {
                    Swal.fire({
                        text: 'Add Product has been completed.',
                        icon: 'success',
                        timer: 10000,
                        showConfirmButton: false
                    });
                })
                </script>";
                echo "<meta http-equiv='refresh' content='2;url=product'>";
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
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
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
    <link rel="stylesheet" href="css/product.css?v=<?php echo time(); ?>">
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
                <h3>Product Add</h3>
            </div>
            <section class="section">
                <form method="post" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Product</h4>
                            <button type="submit" name="add-product" class="btn btn-edit">Save</button>
                        </div>
                        <div class="card-body">

                            <div class="product-name">
                                <h6>Product Name</h6>
                                <input type="text" name="product_name" class="form-control">
                                <h6>Introduction</h6>
                                <input type="text" name="intro" class="form-control">
                            </div>
                            <div class="title">
                                <div class="title-img">
                                    <span id="upload-img">Cover Image</span>
                                    <div class="group-pos">
                                        <input type="file" name="img-cover" id="imgInput-cover" class="form-control">
                                        <button type="button" class="btn reset" id="reset1">Reset</button>
                                    </div>
                                    <span class="file-support">Only file are support ('jpg', 'jpeg', 'png', 'webp').</span>
                                    <div id="gallery-cover">
                                        <div class='box-edit-img-cover'>
                                            <span class='del-edit-img'></span>
                                            <img class='edit-img-cover' id='previewImg-cover' src=''>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="content">
                                <div class="content-img">
                                    <span id="upload-img">Content Image</span>
                                    <div class="group-pos">
                                        <input type="file" name="img[]" id="imgInput" onchange="preview_image();" class="form-control" multiple>
                                        <button type="button" class="btn reset" id="reset2">Reset</button>
                                    </div>
                                    <span class="file-support">Only file are support ('jpg', 'jpeg', 'png', 'webp').</span>
                                    <div id="gallery"></div>
                                </div>
                                <div class="content-text">
                                    <textarea name="content"></textarea>
                                    <script>
                                        tinymce.init({
                                            selector: 'textarea',
                                            height: "400",
                                            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                                            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
                                            tinycomments_mode: 'embedded',
                                            tinycomments_author: 'Author name',
                                            mergetags_list: [{
                                                    value: 'First.Name',
                                                    title: 'First Name'
                                                },
                                                {
                                                    value: 'Email',
                                                    title: 'Email'
                                                },
                                            ]
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>

                        <div class="conclude">
                            <h6>Conclusion</h6>
                            <textarea name="conclude"></textarea>
                            <script>
                                tinymce.init({
                                    selector: 'textarea',
                                    height: "300",
                                    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                                    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
                                    tinycomments_mode: 'embedded',
                                    tinycomments_author: 'Author name',
                                    mergetags_list: [{
                                            value: 'First.Name',
                                            title: 'First Name'
                                        },
                                        {
                                            value: 'Email',
                                            title: 'Email'
                                        },
                                    ]
                                });
                            </script>
                        </div>

                        <div class="clip">
                            <div class="box-clip">
                                <h6>Link Video</h6>
                                <input type="text" name="link" class="form-control">
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
                $('#gallery').append("<div class='box-edit-img'>  <span class='del-edit-img'></span>  <img class='previewImg' id='edit-img' src='" + URL.createObjectURL(event.target.files[i]) + "'> </div>");
            }
        }
    </script>
    <script>
        let imgInput = document.getElementById('imgInput-cover');
        let previewImg = document.getElementById('previewImg-cover');

        imgInput.onchange = evt => {
            const [file] = imgInput.files;
            if (file) {
                previewImg.src = URL.createObjectURL(file);
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
            $('#reset1').click(function() {
                $('#imgInput-cover').val(null);
                $('#previewImg-cover').attr("src", "");
                // $('.previewImg').addClass('none');
                // $('.box-edit-img').addClass('none');
            });
            $('#imgout').click(function() {
                $('#imgInput').val(null);
            });

        });
    </script>

</body>

</html>