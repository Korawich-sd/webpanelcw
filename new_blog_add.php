<!DOCTYPE html>
<script src="https://cdn.tiny.cloud/1/2c646ifr40hywrvj32dwwml8e5qmxxr52qvzmjjq7ixbrjby/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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

if (isset($_POST['add-news'])) {
    $news_name = $_POST['news_name'];
    $text_content = $_POST['text-content'];
    $img_cover = $_FILES['img-cover'];
    $status = "on";


    $allow = array('jpg', 'jpeg', 'png', 'webp');
    $extention1 = explode(".", $img_cover['name']); //เเยกชื่อกับนามสกุลไฟล์
    $fileActExt1 = strtolower(end($extention1)); //แปลงนามสกุลไฟล์เป็นพิมพ์เล็ก
    $fileNew1 = rand() . "." . "webp";
    $filePath1 = "upload/upload_news/" . $fileNew1;

    if (empty($news_name)) {
        echo "<script>alert('Please Enter News or Blog Name')</script>";
    } else if (empty($text_content)) {
        echo "<script>alert('Please Enter content')</script>";
    } else {
        try {

            if (in_array($fileActExt1, $allow)) {
                if ($img_cover['size'] > 0 && $img_cover['error'] == 0) {
                    if (move_uploaded_file($img_cover['tmp_name'], $filePath1)) {
                        $news = $conn->prepare("INSERT INTO news(news_name,content, status, cover_img)
                                        VALUES(:news_name, :content, :status, :cover_img)");
                        $news->bindParam(":news_name", $news_name);
                        $news->bindParam(":content", $text_content);
                        $news->bindParam(":status", $status);
                        $news->bindParam(":cover_img", $fileNew1);
                        $news->execute();

                        $id_news = $conn->lastInsertId();
                    }
                }
            }

            foreach ($_FILES['img']['tmp_name'] as $key => $value) {
                $file_names = $_FILES['img']['name'];

                $extension = strtolower(pathinfo($file_names[$key], PATHINFO_EXTENSION));
                $supported = array('jpg', 'jpeg', 'png', 'webp', 'mp4');
                if (in_array($extension, $supported)) {
                    $new_name = rand() . '.' . "webp";
                    if (move_uploaded_file($_FILES['img']['tmp_name'][$key], "upload/upload_news/" . $new_name)) {
                        $sql = "INSERT INTO news_img (image, news_id) VALUES(:image, :news_id)";
                        $upload_img = $conn->prepare($sql);
                        $params = array(
                            'image' => $new_name,
                            'news_id' => $id_news
                        );
                        $upload_img->execute($params);
                    }
                } else {
                    echo "<script>alert('ไม่รองรับนามสกุลไฟล์นี้')</script>";
                }
            }
            if ($news) {
                echo "<script>
                $(document).ready(function() {
                    Swal.fire({
                        text: 'Add News & Blog has been completed.',
                        icon: 'success',
                        timer: 10000,
                        showConfirmButton: false
                    });
                })
                </script>";
                echo "<meta http-equiv='refresh' content='2;url=new_blog'>";
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
    <!-- <link rel="stylesheet" href="css/product.css?v=<?php echo time(); ?>"> -->
    <!-- <link rel="shortcut icon" href="assets/images/logo/favicon.svg" type="image/x-icon"> -->
    <link rel="shortcut icon" href="image/logodoj.png" type="image/png">
    <link rel="stylesheet" href="css/news.css?v=<?php echo time(); ?>">
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
                <h3>News & Blog Add</h3>
            </div>
            <section class="section">
                <form method="post" enctype="multipart/form-data">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">News & Blog</h4>
                            <button type="submit" name="add-news" class="btn btn-edit">Save</button>
                        </div>
                        <div class="title">
                            <!-- <h6 class="txt-cover">Cover Image</h6> -->
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
                        <div class="card-body">
                            <div class="product-name">
                                <h6>News or Blog Name</h6>
                                <input type="text" name="news_name" class="form-control">
                            </div>

                            <div class="content-text">
                                <h6>Content</h6>
                                <textarea name="text-content"></textarea>
                                <script>
                                    tinymce.init({
                                        selector: 'textarea',
                                        height: "350",
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