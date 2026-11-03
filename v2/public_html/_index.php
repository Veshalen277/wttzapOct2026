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

<style>
body {
    margin: 0;
    background: #f3f5f7;
    font-family: Arial, Helvetica, sans-serif;
    color: #222;
    min-height: 100vh;
}

.portal-shell {
    max-width: 1400px;
    margin: 30px auto;
    padding: 0 20px;
}

.navbar {
    box-shadow: 0 2px 8px rgba(0,0,0,.12);
}

.navbar-brand {
    font-size: 24px;
    font-weight: 700;
}

.navbar-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.navbar-form input {
    min-width: 140px;
}

.forgot-link {
    color: #fff;
    font-size: 14px;
    text-decoration: none;
}

.forgot-link:hover {
    text-decoration: underline;
}

.portal-panel {
    background: #fff;
    border: 1px solid #d7dce1;
    padding: 28px;
    margin-bottom: 24px;
}

.portal-hero {
    text-align: center;
    padding: 38px 28px;
}

.portal-hero h1 {
    margin: 0 0 10px;
    font-size: 36px;
    font-weight: 700;
}

.portal-hero p {
    margin: 0;
    color: #666;
    font-size: 17px;
}

.section-title {
    font-size: 15px;
    font-weight: 700;
    color: #444;
    text-transform: uppercase;
    letter-spacing: .8px;
    margin-bottom: 18px;
}

.gallery-container {
    display: grid;
    grid-template-columns: repeat(9, 1fr);
    gap: 14px;
}

.gallery-img {
    width: 100%;
    display: block;
    border: 1px solid #d7dce1;
    background: #fff;
    padding: 8px;
    transition: .2s ease;
}

.gallery-img:hover {
    transform: scale(1.04);
    border-color: #0d6efd;
}

.modules-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.module-card {
    border: 1px solid #d7dce1;
    background: #fafafa;
    padding: 20px;
    min-height: 120px;
}

.module-card h3 {
    font-size: 18px;
    margin: 0 0 8px;
    font-weight: 700;
}

.module-card p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.notice-list {
    margin: 0;
    padding-left: 20px;
    color: #444;
}

.notice-list li {
    margin-bottom: 8px;
}

.image-section {
    opacity: .85;
}

.image-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
    margin-bottom: 18px;
}

.side-image {
    width: 100%;
    height: 190px;
    object-fit: contain;
    background: #fff;
    border: 1px solid #d7dce1;
    padding: 8px;
}

.portal-footer {
    background: #20252b;
    color: #d9dde1;
    font-size: 13px;
    padding: 18px 0;
    margin-top: 30px;
}

.footer-inner {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
}

.status-dot {
    display: inline-block;
    width: 9px;
    height: 9px;
    background: #28a745;
    margin-right: 6px;
}

@media (max-width: 1200px) {
    .gallery-container {
        grid-template-columns: repeat(6, 1fr);
    }

    .modules-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 992px) {
    .navbar .container {
        flex-direction: column;
        gap: 12px;
    }

    .navbar-form {
        flex-wrap: wrap;
        justify-content: center;
    }

    .gallery-container {
        grid-template-columns: repeat(4, 1fr);
    }

    .modules-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .image-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .portal-shell {
        margin: 18px auto;
        padding: 0 12px;
    }

    .navbar-form {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }

    .navbar-form input,
    .navbar-form button {
        width: 100%;
    }

    .forgot-link {
        text-align: center;
    }

    .portal-panel {
        padding: 18px;
    }

    .portal-hero h1 {
        font-size: 28px;
    }

    .gallery-container {
        grid-template-columns: repeat(2, 1fr);
    }

    .modules-grid {
        grid-template-columns: 1fr;
    }

    .side-image {
        height: auto;
    }

    .footer-inner {
        flex-direction: column;
        text-align: center;
    }
}
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container justify-content-between">
        <a class="navbar-brand" href="#">WTT Employee Portal</a>

        <form action="" method="POST" class="navbar-form">
         <input
    type="text"
    name="user_id"
    id="user_id"
    class="form-control"
    placeholder="User ID"
    autocomplete="username"
    autocapitalize="off"
    spellcheck="false"
    required>
            <input type="password" name="user_pass" class="form-control" placeholder="Password" required>
            <button name="login_btn" class="btn btn-primary">Login</button>
            <a class="forgot-link" href="forgot_password.php">Forgot Password</a>
        </form>
    </div>
</nav>

<?php if (isset($_SESSION["error"])): ?>
    <div class="alert alert-danger text-center m-3" role="alert">
        <?= $_SESSION["error"]; unset($_SESSION["error"]); ?>
    </div>
<?php endif; ?>

<main class="portal-shell">

    <section class="portal-panel portal-hero">
        <h1>Welcome to the Employee Portal</h1>
        <p>Company resources, employee services and internal systems in one place.</p>
    </section>

    <section class="portal-panel">
        <div class="section-title">Company Divisions</div>

        <div class="gallery-container">
            <?php foreach ($images as $image): ?>
                <div>
                    <img src="<?= $image ?>" class="gallery-img" alt="Company Division">
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="portal-panel">
        <div class="section-title">Available Modules</div>

        <div class="modules-grid">
            <div class="module-card">
                <h3>Employee Dashboard</h3>
                <p>Staff profiles, notices and daily employee tools.</p>
            </div>

            <div class="module-card">
   <h3 class="text-muted"><del>Claims Manager</del></h3>
<p class="text-muted">
    <del>Insurance claims, internal invoices and claim tracking.</del>
</p>
            </div>

            <div class="module-card">
                <h3>Maintenance</h3>
                <p>Repairs, work orders, contractors and site maintenance.</p>
            </div>

            <div class="module-card">
                <h3>Reports</h3>
                <p>Operational summaries, admin reports and business records.</p>
            </div>
        </div>
    </section>

    <section class="portal-panel">
        <div class="section-title">Company Notices</div>

        <ul class="notice-list">
            <li>Use your assigned employee login details to access internal systems.</li>
            <li>Report system issues to the IT department.</li>
            <li>Keep passwords private and do not share login access.</li>
        </ul>
    </section>

    <section class="portal-panel image-section">
        <div class="section-title">Company Information</div>

        <div class="image-row">
            <img src="d.png" class="side-image" alt="Company Image 1">
            <img src="c.png" class="side-image" alt="Company Image 2">
        </div>

        <div class="image-row">
            <img src="b.png" class="side-image" alt="Company Image 3">
            <img src="a.png" class="side-image" alt="Company Image 4">
        </div>
    </section>

</main>

<footer class="portal-footer">
    <div class="footer-inner">
        <div>
            <span class="status-dot"></span> Database Online
        </div>
        <div>Employee Portal Version 3.0.0</div>
        <div>© <?= date("Y"); ?> WTT Group</div>
        <div>Powered by Timeflies IT Division</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('user_id').addEventListener('input', function () {
    this.value = this.value.toLowerCase();
});
</script>
</body>
</html>