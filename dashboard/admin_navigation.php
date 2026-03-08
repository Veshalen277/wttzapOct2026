<?php
if (isset($_SESSION['u_data'])) {
    $data = $_SESSION['u_data'];
    $role = $data[4]; // Assuming role information is stored at index 4
    $currentPage = basename($_SERVER['PHP_SELF']); // Get the current page filename
    // $menuItems = [
    //         ['title' => 'Home', 'link' => '/dashboard/emp_profile.php', 'hide' => []],
    //         ['title' => 'Leave Application Form', 'link' => '/dashboard/leave_app.php', 'hide' => []],
    //         ['title' => 'Submit Cashup', 'link' => '/dashboard/report_form.php', 'hide' => []],
    //         ['title' => 'Submit TFM Cashup', 'link' => '/dashboard/tfm_report_form.php', 'hide' => []],
    //         ['title' => 'Edit Profile', 'link' => '/dashboard/my_profile.php', 'hide' => []],
    //       //['title' => 'Submit Suggestion', 'link' => '/dashboard/suggestion.php', 'hide' => []],
    //       //['title' => 'Send Message', 'link' => '/dashboard/message_form.php', 'hide' => []],
    //       //['title' => 'Messages', 'link' => '/dashboard/message_board.php', 'hide' => []],
    //      ['title' => 'Order Form', 'link' => '/dashboard/orderForm.php', 'hide' => []],
    //              ];
    $menuItems = [
    ['title' => 'Home', 'link' => '/dashboard/emp_profile.php', 'hide' => []],
    ['title' => 'Leave Application Form', 'link' => '/dashboard/leave_app.php', 'hide' => []],
    ['title' => 'Submit Cashup', 'link' => '/dashboard/report_form.php', 'hide' => []],
    [
        'title' => 'Submit TFM Cashup',
        'link' => '/dashboard/tfm_report_form.php',
        'hide' => [],
        'attributes' => [
            'data-bs-toggle' => 'tooltip',
            'title' => 'TFM Users only !'
        ]
    ],
    ['title' => 'Edit Profile', 'link' => '/dashboard/my_profile.php', 'hide' => []],
    //['title' => 'Submit Suggestion', 'link' => '/dashboard/suggestion.php', 'hide' => []],
    //['title' => 'Send Message', 'link' => '/dashboard/message_form.php', 'hide' => []],
    //['title' => 'Messages', 'link' => '/dashboard/message_board.php', 'hide' => []],
    ['title' => 'Order Form', 'link' => '/dashboard/orderForm.php', 'hide' => []],
];

    $adminMenuItems = [
            ['title' => 'Print Reports', 'link' => '/dashboard/admin_profile.php', 'hide' => []],
            ['title' => 'View Work Reports', 'link' => '/dashboard/assigned_work.php', 'hide' => []],
        //  ['title' => 'Dashboard', 'link' => '/dashboard/charts_admin.php', 'hide' => []],
        //  ['title' => 'Leave Application Form', 'link' => '/dashboard/leave_app.php', 'hide' => []],
        //  ['title' => 'Send Message', 'link' => '/dashboard/message_form.php', 'hide' => []],
        //  ['title' => 'Messages', 'link' => '/dashboard/message_board.php', 'hide' => []],
            ['title' => 'User List', 'link' => '/dashboard/users_list.php', 'hide' => []],
        //  ['title' => 'Submit Suggestion', 'link' => '/dashboard/suggestion.php', 'hide' => []],
          ['title' => 'Edit Profile', 'link' => '/dashboard/my_profile.php', 'hide' => []],
            ['title' => 'Leave Applications', 'link' => '/dashboard/leave-neg.php', 'hide' => []],
          ['title' => 'Employee Profile', 'link' => '/dashboard/emp_profile.php', 'hide' => []],
            ['title' => 'Reports', 'link' => '#', 'submenu' => [
            ['title' => 'General Cash Reports', 'link' => '/dashboard/view_reports.php'],
            ['title' => 'General TFM Cash Reports', 'link' => '/dashboard/view_tfm_reports.php'],
          //  ['title' => 'Archived Reports', 'link' => '/dashboard/archive_reports.php'],
             ['title' => 'Monthly Reports', 'link' => '/dashboard/monthly_reports.php'],
            ['title' => 'Archived TFM Reports', 'link' => '/dashboard/tfm_archive_reports.php'],
            //  ['title' => 'Leave Applications', 'link' => '/dashboard/leave-neg.php'],
            //  ['title' => ' Form: Smoking Coffee Day-End Report', 'link' => '/dashboard/smoking_coffee_daily_reports.php'],
            //  ['title' => 'View Smoking Coffee Day-End Reports', 'link' => '/dashboard/view_smoking_coffee_daily_reports.php'],
            //  ['title' => 'View Smoking Coffee Monthly Reports', 'link' => '/dashboard/sc_monthly_reports.php'],
            //  ['title' => 'View Smoking CoffeeDay-EndReports', 'link' => '/dashboard/view_smoking_coffee_daily_reports.php'],
            //  ['title' => ' Form: Laletsa Day-End Report', 'link' => '/dashboard/laletsa_report_form.php'],
            //  ['title' => 'View Laletsa Day-End Reports', 'link' => '/dashboard/laletsa_daily_reports.php'],
            //  ['title' => 'View Laletsa Monthly Reports', 'link' => '/dashboard/laletsa_monthly_reports.php'],
            ]],
            ['title' => 'Orders', 'link' => '#', 'submenu' => [
            ['title' => 'Order Form', 'link' => '/dashboard/orderForm.php'],
            ['title' => 'Orders', 'link' => '/dashboard/orderBoard.php'],
            ]],
         /*   ['title' => 'Stock Management (Smoking Coffee)', 'link' => '#', 'submenu' => [
            ['title' => 'Add SC Category Item', 'link' => '/dashboard/sc_add_category.php'],
            ['title' => 'Add SC Stock Item', 'link' => '/dashboard/sc_add_stock.php'],
            ['title' => 'Add SC Inventory', 'link' => '/dashboard/sc_stock_form.php'],
            ['title' => 'View SC Inventory', 'link' => '/dashboard/sc_stock_report.php'],
         // ['title' => 'S/C Stock Report Pie Chart', 'link' => '/dashboard/stock_report_pie.php'],
            ]],
         */    
         /*  ['title' => 'Stock Management (Laletsa)', 'link' => '#', 'submenu' => [
            ['title' => 'Add Laletsa Category Item', 'link' => '/dashboard/lal_add_category.php'],
            ['title' => 'Add Laletsa Stock Item', 'link' => '/dashboard/lal_add_stock.php'],
            ['title' => 'Add Laletsa Inventory', 'link' => '/dashboard/lal_stock_form.php'],
            ['title' => 'View Laletsa Inventory', 'link' => '/dashboard/lal_stock_report.php'],
            // ['title' => 'Laletsa Stock Report Pie Chart', 'link' => '/dashboard/lal_stock_report_pie.php'],
            ]],
            */    
            ];
    $superAdminMenuItems = [
           // ['title' => 'Print Work Reports', 'link' => '/dashboard/admin_profile.php', 'hide' => []],
            ['title' => 'Dashboard', 'link' => '/dashboard/charts_admin.php', 'hide' => []],
            ['title' => 'Orders', 'link' => '#', 'submenu' => [
            ['title' => 'Order Form', 'link' => '/dashboard/orderForm.php'],
            ['title' => 'Orders', 'link' => '/dashboard/orderBoard.php'],
                ]],
            ['title' => 'Submit Cashup', 'link' => '/dashboard/report_form.php', 'hide' => []],
            ['title' => 'Submit TFM Cashup', 'link' => '/dashboard/tfm_report_form.php', 'hide' => []],
            
         // ['title' => 'Order Form', 'link' => '/dashboard/orderForm.php', 'hide' => []],
        // ['title' => 'Orders', 'link' => '/dashboard/orderBoard.php', 'hide' => []],
         // ['title' => 'Leave Application Form', 'link' => '/dashboard/leave_app.php', 'hide' => []],
         // ['title' => 'Send Message', 'link' => '/dashboard/message_form.php', 'hide' => []],
         // ['title' => 'Messages', 'link' => '/dashboard/message_board.php', 'hide' => []],
         // ['title' => 'User List', 'link' => '/dashboard/users_list.php', 'hide' => []],
         // ['title' => 'Submit Suggestion', 'link' => '/dashboard/suggestion.php', 'hide' => []],
         // ['title' => 'Edit Profile', 'link' => '/dashboard/my_profile.php', 'hide' => []],
         //   ['title' => 'Leave Applications', 'link' => '/dashboard/leave-neg.php', 'hide' => []],
            ['title' => 'Employee Profile', 'link' => '/dashboard/emp_profile.php', 'hide' => []],

            ['title' => 'Manage Users', 'link' => '#', 'submenu' => [
            ['title' => 'User List', 'link' => '/dashboard/users_list.php'],
            ['title' => 'Leave Applications', 'link' => '/dashboard/leave-neg.php'],
            ['title' => 'Suggestions', 'link' => '/dashboard/suggestion_board.php'],
             ['title' => 'Print Work Reports', 'link' => '/dashboard/admin_profile.php'],
              ['title' => 'View Work Reports', 'link' => '/dashboard/assigned_work.php'],
            ]],
            ['title' => 'User Profile', 'link' => '#', 'submenu' => [
            ['title' => 'Msgs', 'link' => '/dashboard/message_board.php'],
             ['title' => 'Send', 'link' => '/dashboard/message_form.php'],
            ['title' => 'pvt message', 'link' => '/dashboard/private_messages.php'],
            ['title' => 'view pvt', 'link' => '/dashboard/view_private_messages.php'],
                                 ['title' => 'Send Group Message', 'link' => '/dashboard/send_group_message.php'],
                                           
                  ['title' => 'Create Group', 'link' => '/dashboard/create_group.php'],
      ['title' => 'Group Invite', 'link' => '/dashboard/group_invite.php'],
                        ['title' => 'Manage Group', 'link' => '/dashboard/group_management.php'],
                         ['title' => 'Manage Members', 'link' => '/dashboard/manage_group_members.php'],
            ['title' => 'view pvt', 'link' => '/dashboard/group_invite.php'],

            ['title' => 'Leave', 'link' => '/dashboard/leave_app.php'],
            ['title' => 'Suggest', 'link' => '/dashboard/suggestion.php'],
            ['title' => 'Profile', 'link' => '/dashboard/my_profile.php'],

            ]],
            ['title' => 'Income/Expense Reports', 'link' => '#', 'submenu' => [
            ['title' => 'General Income Reports (sandbox)', 'link' => '/dashboard/view_income_reports.php'],
            ['title' => 'Archived Reports (sandbox)', 'link' => '/dashboard/archive_reports.php'],
               ['title' => 'Monthly Reports', 'link' => '/dashboard/monthly_reports.php'],
            ['title' => 'General TFM Cash Reports', 'link' => '/dashboard/view_tfm_reports.php'],
            ['title' => 'Archived TFM Reports', 'link' => '/dashboard/tfm_archive_reports.php'],
            ]],
            ['title' => 'Smoking Coffee', 'link' => '#', 'submenu' => [
            ['title' => 'Add Category Item', 'link' => '/dashboard/sc_add_category.php'],
            ['title' => 'Add Stock Item', 'link' => '/dashboard/sc_add_stock.php'],
          //  ['title' => 'Add Inventory', 'link' => '/dashboard/sc_stock_form.php'],
            ['title' => 'View Inventory', 'link' => '/dashboard/sc_stock_report.php'],
         //   ['title' => 'Stock Report Pie Chart', 'link' => '/dashboard/stock_report_pie.php'],
            ['title' => ' Form: Day-End Report', 'link' => '/dashboard/smoking_coffee_daily_reports.php'],
            ['title' => 'View Day-End Reports', 'link' => '/dashboard/view_smoking_coffee_daily_reports.php'],
            ['title' => 'View Monthly Reports', 'link' => '/dashboard/sc_monthly_reports.php'],
            ]],
            ['title' => 'Laletsa', 'link' => '#', 'submenu' => [
            ['title' => 'Add Category Item', 'link' => '/dashboard/lal_add_category.php'],
            ['title' => 'Add Stock Item', 'link' => '/dashboard/lal_add_stock.php'],
            ['title' => 'Add Inventory', 'link' => '/dashboard/lal_stock_form.php'],
            ['title' => 'View Inventory', 'link' => '/dashboard/lal_stock_report.php'],
           ['title' => 'Stock Report Pie Chart', 'link' => '/dashboard/lal_stock_report_pie.php'],
            ['title' => ' Form: Day-End Report', 'link' => '/dashboard/laletsa_report_form.php'],
            ['title' => 'View Day-End Reports', 'link' => '/dashboard/laletsa_daily_reports.php'],
            ['title' => 'View Monthly Reports', 'link' => '/dashboard/laletsa_monthly_reports.php'],
            ]],     
            ['title' => 'Factory Shop', 'link' => '#', 'submenu' => [
            ['title' => 'Add Category Item', 'link' => '/dashboard/fs_add_category.php'],
            ['title' => 'Add Stock Item', 'link' => '/dashboard/fs_add_stock.php'],
            ['title' => 'Add Inventory', 'link' => '/dashboard/fs_stock_form.php'],
            ['title' => 'View Inventory', 'link' => '/dashboard/fs_stock_report.php'],
            ['title' => 'Stock Report Pie Chart', 'link' => '/dashboard/fs_stock_report_pie.php'],
        //    ['title' => ' Form: Day-End Report', 'link' => '/dashboard/laletsa_report_form.php'],
        //    ['title' => 'View Day-End Reports', 'link' => '/dashboard/laletsa_daily_reports.php'],
        //    ['title' => 'View Monthly Reports', 'link' => '/dashboard/laletsa_monthly_reports.php'],
                   ]],       

                ];











    $scAdminMenuItems = [
            ['title' => 'Print Reports', 'link' => '/dashboard/admin_profile.php', 'hide' => []],
            ['title' => 'Edit Profile', 'link' => '/dashboard/my_profile.php', 'hide' => []],
            //    ['title' => 'Leave Applications', 'link' => '/dashboard/leave-neg.php', 'hide' => []],
            ['title' => 'Employee Profile', 'link' => '/dashboard/emp_profile.php', 'hide' => []],
            ['title' => 'Submit Cashup', 'link' => '/dashboard/report_form.php', 'hide' => []],

         //   ['title' => 'pvt message', 'link' => '/dashboard/private_messages.php', 'hide' => []],
         //   ['title' => 'view pvt', 'link' => '/dashboard/view_private_messages.php', 'hide' => []],
            ['title' => 'Orders', 'link' => '#', 'submenu' => [
            ['title' => 'Order Form', 'link' => '/dashboard/orderForm.php'],
            //  ['title' => 'Orders', 'link' => '/dashboard/orderBoard.php'],

            ]],
            ['title' => 'Stock Management', 'link' => '#', 'submenu' => [
               
                ['title' => 'Inventory', 'link' => '/dashboard/sc_stock_report.php'],
                  ['title' => 'Pie Chart', 'link' => '/dashboard/sc_stock_report_pie.php'],
            ['title' => 'Add Category Item', 'link' => '/dashboard/sc_add_category.php'],
               ['title' => 'Manage Categories', 'link' => '/dashboard/sc_manage_categories.php'],
            ['title' => 'Add Stock Item', 'link' => '/dashboard/sc_add_stock.php'],
   ['title' => 'Daily Stock update', 'link' => '/dashboard/sc_update_stock.php'],
            //  ['title' => 'Add SC Inventory', 'link' => '/dashboard/sc_stock_form.php'],
           

            ]],
  
            ];









?>


<style>

    .nav-item a {
        font-size: 13.5px;
        font-weight:500px;
    }
</style>

<!-- <nav class="navbar navbar-expand-sm bg-light">
    <div class="container-fluid px-5">
        
        <ul class="navbar-nav">
            </?php
                if ($role == 2) {
                foreach ($superAdminMenuItems as $item) {
                    if (isset($item['submenu'])) {
                        echo '<li class="nav-item dropdown">';
                        echo '<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $item['title'] . '</a>';
                        echo '<div class="dropdown-menu" aria-labelledby="navbarDropdown">';
                        foreach ($item['submenu'] as $subitem) {
                            echo '<a class="dropdown-item" href="' . $subitem['link'] . '">' . $subitem['title'] . '</a>';
                        }
                        echo '</div>';
                        echo '</li>';
                    } else {
                        // Check if the current page should hide this menu item
                        if (!in_array($currentPage, $item['hide']) || ($currentPage == '' && in_array('index.php', $item['hide']))) {
                            echo '<li class="nav-item"><a class="nav-link" href="' . $item['link'] . '">' . $item['title'] . '</a></li>';
                        }
                    }
                }
                } elseif 
             ($role == 1) {
                foreach ($menuItems as $item) {
                    // Check if the current page should hide this menu item
                    if (!in_array($currentPage, $item['hide']) || ($currentPage == '' && in_array('index.php', $item['hide']))) {
                        echo '<li class="nav-item"><a class="nav-link" href="' . $item['link'] . '">' . $item['title'] . '</a></li>';
                    }
                }
            } elseif ($role == 0) {
                foreach ($adminMenuItems as $item) {
                    if (isset($item['submenu'])) {
                        echo '<li class="nav-item dropdown">';
                        echo '<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $item['title'] . '</a>';
                        echo '<div class="dropdown-menu" aria-labelledby="navbarDropdown">';
                        foreach ($item['submenu'] as $subitem) {
                            echo '<a class="dropdown-item" href="' . $subitem['link'] . '">' . $subitem['title'] . '</a>';
                        }
                        echo '</div>';
                        echo '</li>';
                    } else {
                        // Check if the current page should hide this menu item
                        if (!in_array($currentPage, $item['hide']) || ($currentPage == '' && in_array('index.php', $item['hide']))) {
                            echo '<li class="nav-item"><a class="nav-link" href="' . $item['link'] . '">' . $item['title'] . '</a></li>';
                        }
                    }
                }
            }


            //New Edition 
            elseif ($role == 3) {
                foreach ($scAdminMenuItems as $item) {
                    if (isset($item['submenu'])) {
                        echo '<li class="nav-item dropdown">';
                        echo '<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . $item['title'] . '</a>';
                        echo '<div class="dropdown-menu" aria-labelledby="navbarDropdown">';
                        foreach ($item['submenu'] as $subitem) {
                            echo '<a class="dropdown-item" href="' . $subitem['link'] . '">' . $subitem['title'] . '</a>';
                        }
                        echo '</div>';
                        echo '</li>';
                    } else {
                        // Check if the current page should hide this menu item
                        if (!in_array($currentPage, $item['hide']) || ($currentPage == '' && in_array('index.php', $item['hide']))) {
                            echo '<li class="nav-item"><a class="nav-link" href="' . $item['link'] . '">' . $item['title'] . '</a></li>';
                        }
                    }
                }
            }
            ?>
            <li class="nav-item justify-content-end">
                <form action="logout.php" method="POST">
                    <button name="emp_logout" class="btn btn-secondary btn-sm">Logout</button>
                </form>
            </li>
        </ul>
    </div>
</nav> -->

<?php
}  //End of session and role check
?>
