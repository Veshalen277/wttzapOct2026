<?php
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $item_name = htmlspecialchars(trim($_POST['item_name']));
    $category_id = intval($_POST['category_id']); // Ensures category_id is an integer

    // Check if fields are not empty
    if (!empty($item_name) && $category_id > 0) {
        // Insert into stock_items table
        $sql = "INSERT INTO fs_stock_items (item_name, category_id) VALUES (?, ?)";
        
        // Prepare and bind parameters
        if ($stmt = mysqli_prepare($con, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $item_name, $category_id);
            
            // Execute the query
            if (mysqli_stmt_execute($stmt)) {
                // Success message and image
                echo '<div id="message-container" style="text-align: center; margin: 20px;">';
                echo '<p>Item added successfully.</p>';
                echo '<img src="https://www.indiewire.com/wp-content/uploads/2016/08/20140216-131646.jpg" alt="Success" style="width: 500px; height: auto;">';
                echo '</div>';
                
                // JavaScript to handle redirection after 2 seconds
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "fs_add_stock.php";
                    }, 2000); // 2000 milliseconds = 2 seconds
                </script>';
                
            } else {
                error_log('fs/fs_process_stock.php: Error: ' . mysqli_error($con));
                echo 'Something went wrong. Please try again later.';
            }

            // Close statement
            mysqli_stmt_close($stmt);
        } else {
            error_log('fs/fs_process_stock.php: Error preparing statement: ' . mysqli_error($con));
            echo 'Something went wrong. Please try again later.';
        }
    } else {
        echo "Please fill in all fields.";
    }
}

// Close the database connection
mysqli_close($con);

include 'footer.php';
?>
