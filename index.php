<?php
ob_start();
session_start();
include "dashboard/config.php";
include "gist.php";

$images = [
    "assets/img1.png",
    "assets/img2.png",
    "assets/img3.png",
    "assets/img4.png",
    "assets/img5.png",
    "assets/img6.png",
    "assets/img7.png",
    "assets/wtt.png",
    "assets/img9.png"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/style.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .navbar-form {
            display: flex;
            gap: 10px;
        }

        .navbar-form input {
            height: 38px;
        }

        .image {
            padding:15px;
            max-width: 100%;
            height: auto;
            margin: 0 auto;
            animation: spin 158s linear infinite;
        }

        .image-row {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .side-image {
            max-width: 600px;
            height: auto;
        }

        .gallery-ribbon {
            background: white;
            padding: 20px 0;
            margin-top: auto;
        }

        .gallery-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }

        .gallery-item {
            flex: 0 0 calc(11.11% - 15px);
            max-width: calc(11.11% - 15px);
        }

        .gallery-img {
            width: 100%;
            height: auto;
            transition: transform 0.3s;
        }

        .gallery-img:hover {
            transform: scale(1.05);
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 1200px) {
            .gallery-item {
                flex: 0 0 calc(16.66% - 15px);
                max-width: calc(16.66% - 15px);
            }
        }

        @media (max-width: 992px) {
            .gallery-item {
                flex: 0 0 calc(25% - 15px);
                max-width: calc(25% - 15px);
            }
        }

        @media (max-width: 768px) {
            .image {
                max-width: 80%;
            }

            .side-image {
                max-width: 350px;
            }

            .gallery-item {
                flex: 0 0 calc(33.33% - 15px);
                max-width: calc(33.33% - 15px);
            }
        }

        @media (max-width: 576px) {
            .image {
                max-width: 60%;
            }

            .image-row {
                flex-direction: column;
                align-items: center;
            }

            .side-image {
                max-width: 80%;
            }

            .gallery-item {
                flex: 0 0 calc(50% - 15px);
                max-width: calc(50% - 15px);
            }

            .navbar-form {
                flex-direction: column;
                align-items: stretch;
            }

            .navbar-form input, .navbar-form button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<!-- Navbar with Login -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container justify-content-between">
        <a class="navbar-brand" href="#">Employee Portal</a>
        <form action="" method="POST" class="navbar-form d-flex">
            <input type="text" name="user_id" class="form-control me-2" placeholder="User ID" required>
            <input type="password" name="user_pass" class="form-control me-2" placeholder="Password" required>
            <button name="login_btn" class="btn btn-primary">Login</button>
             <a class="text-white" href="forgot_password.php">Forgot Password</a> 
        </form>
    </div>
</nav>

<!-- Display login error if any -->
<?php if (isset($_SESSION["error"])): ?>
    <div class="alert alert-danger text-center m-3" role="alert">
        <?= $_SESSION["error"]; unset($_SESSION["error"]); ?>
    </div>
<?php endif; ?>

<!-- Main Full-width Content -->
<div class="container-fluid mt-4">
    <div class="image-row">
        <img src="d.png" class="p-1 side-image" alt="Side Image 1">
        <img src="c.png" class="p-1 side-image" alt="Side Image 2">
    </div>
    <div class="image-row">
        <img src="b.png" class="p-1 side-image" alt="Side Image 3">
        <img src="a.png" class="p-1 side-image" alt="Side Image 4">
    </div>
<hr>
    <!-- Gallery Ribbon -->
<div class="container">
<div class="gallery-ribbon">
        <div class="gallery-container">
            <?php foreach ($images as $image): ?>
                <div class="gallery-item">
                    <img src="<?= $image ?>" class="gallery-img img-thumbnail" alt="Gallery Image">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
