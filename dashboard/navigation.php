<?php
if (isset($_SESSION['u_data'])) {
    $data = $_SESSION['u_data'];
    $role = $data[4]; 
    $fullName = htmlspecialchars($data[0]); // Assuming full name is stored at index 0
    $currentPage = basename($_SERVER['PHP_SELF']); // Get the current page filename

    include 'menu_config.php';

    $menuItems = getMenuItems($role);

    // Function to render menu items
function renderMenuItems($items, $currentPage, $inDropdown = false) {
    foreach ($items as $item) {

        // Dropdown parent
        if (isset($item['submenu'])) {

            // If we're already in a dropdown, Bootstrap doesn't support multi-level by default
            // so we render as a header + items (simple & clean)
            if ($inDropdown) {
                echo '<li><h6 class="dropdown-header">' . htmlspecialchars($item['title']) . '</h6></li>';
                renderMenuItems($item['submenu'], $currentPage, true);
                echo '<li><hr class="dropdown-divider"></li>';
                continue;
            }

            echo '<li class="nav-item dropdown">';
            echo '  <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">'
                . htmlspecialchars($item['title']) . '</a>';
            echo '  <ul class="dropdown-menu">';
            renderMenuItems($item['submenu'], $currentPage, true);
            echo '  </ul>';
            echo '</li>';

        } else {

            $activeClass = ($item['link'] === $currentPage) ? 'active' : '';

            if ($inDropdown) {
                echo '<li><a class="dropdown-item ' . $activeClass . '" href="' . htmlspecialchars($item['link']) . '">'
                    . htmlspecialchars($item['title']) . '</a></li>';
            } else {
                echo '<li class="nav-item"><a class="nav-link ' . $activeClass . '" href="' . htmlspecialchars($item['link']) . '">'
                    . htmlspecialchars($item['title']) . '</a></li>';
            }
        }
    }
}


 // Start Navbar HTML (Modern)
echo '<nav class="navbar navbar-expand-lg navbar-dark wtt-nav shadow-sm fixed-top">';
echo '  <div class="container-fluid px-3 px-lg-4">';

echo '    <a class="navbar-brand d-flex align-items-center gap-2" href="/dashboard/employee/emp_profile.php">';
echo '      <img src="https://wttzap.co.za/dashboard/images/FrontWebPageWheel.png" alt="Logo" class="wtt-logo">';
echo '      <span class="wtt-brand-text">WTTZap</span>';
echo '      <span class="wtt-brand-sub d-none d-md-inline">Employee Portal</span>';
echo '    </a>';

echo '    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">';
echo '      <span class="navbar-toggler-icon"></span>';
echo '    </button>';

echo '    <div class="collapse navbar-collapse" id="navbarNav">';

// LEFT / CENTER NAV
echo '      <ul class="navbar-nav mx-lg-auto gap-lg-1 wtt-navlinks">';
renderMenuItems($menuItems, $currentPage);
echo '      </ul>';

// RIGHT PROFILE
echo '      <div class="d-flex align-items-center ms-lg-3 mt-3 mt-lg-0">';
echo '        <div class="dropdown">';
echo '          <a class="btn wtt-profile dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">';
echo '            <span class="wtt-avatar">' . strtoupper(substr($fullName, 0, 1)) . '</span>';
echo '            <span class="d-none d-sm-inline">' . $fullName . '</span>';
echo '          </a>';
echo '          <ul class="dropdown-menu dropdown-menu-end wtt-dd">';
echo '            <li><a class="dropdown-item" href="/dashboard/employee/emp_profile.php"><i class="bi bi-person me-2"></i>My profile</a></li>';
echo '            <li><hr class="dropdown-divider"></li>';
echo '            <li><a class="dropdown-item text-danger" href="/dashboard/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>';
echo '          </ul>';
echo '        </div>';
echo '      </div>';

echo '    </div>'; // collapse
echo '  </div>';
echo '</nav>';
}
?>

<style>


/* Modern portal navbar */
.wtt-nav{
  background: #151a1f;              /* modern charcoal */
  border-bottom: 1px solid rgba(255,255,255,.06);
}

.wtt-logo{
  height: 54px;
  width: 54px;
  object-fit: contain;
}

.wtt-brand-text{
  font-weight: 700;
  letter-spacing: .2px;
  font-size: 1.05rem;
  line-height: 1;
}

.wtt-brand-sub{
  font-size: .9rem;
  opacity: .75;
  margin-left: .35rem;
}

.wtt-nav .navbar-nav .nav-link{
  padding: .55rem .75rem;
  border-radius: .6rem;
  color: rgba(255,255,255,.82);
  font-weight: 500;
}

.wtt-nav .navbar-nav .nav-link:hover{
  background: rgba(255,255,255,.06);
  color: #fff;
}

.wtt-nav .navbar-nav .nav-link.active{
  background: rgba(220,53,69,.18);  /* matches Bootstrap danger */
  color: #fff;
}

/* Dropdown menu styling */
.wtt-nav .dropdown-menu{
  border: 1px solid rgba(255,255,255,.08);
  background: #11161b;
}

.wtt-nav .dropdown-item{
  color: rgba(255,255,255,.86);
}

.wtt-nav .dropdown-item:hover{
  background: rgba(255,255,255,.06);
  color: #fff;
}

.wtt-profile{
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.08);
  color: #fff;
  border-radius: 999px;
  padding: .45rem .65rem;
}

.wtt-profile:hover{
  background: rgba(255,255,255,.09);
  color: #fff;
}

.wtt-avatar{
  width: 28px;
  height: 28px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(220,53,69,.25);
  border: 1px solid rgba(220,53,69,.35);
  font-weight: 700;
  line-height: 1;
}

/* Make dropdowns feel modern */
.navbar-nav .dropdown-menu{
  border-radius: .8rem;
  padding: .4rem;
}

.navbar-nav .dropdown-item{
  border-radius: .6rem;
  padding: .55rem .65rem;
}

/* Remove your old truncation hack */
@media (max-width: 1366px){
  .navbar-nav .nav-item .nav-link{
    max-width: none;
    text-overflow: initial;
    white-space: normal;
    font-size: .95rem;
  }
}



/* For thefixed top  */
body {
  padding-top: 72px; /* adjust to match navbar height */
}

</style>
