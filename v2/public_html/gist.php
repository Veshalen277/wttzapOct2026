<?php
if (isset($_POST["login_btn"])) {

$user_id = mysqli_real_escape_string($con, strtolower(trim($_POST["user_id"])));
    $pass = mysqli_real_escape_string($con, sha1($_POST["user_pass"]));

    $sql = 
            // "SELECT * FROM users_tbl 
            // WHERE user_id='{$user_id}' 
            // AND user_pass='{$pass}'";
"SELECT *
FROM users_tbl
WHERE user_id='{$user_id}'
AND user_pass='{$pass}'
AND is_active = 1";
    $query = mysqli_query($con, $sql);
    $rows = mysqli_num_rows($query);

    if ($rows) {
        $record = mysqli_fetch_assoc($query);

        $u_data = array(
            $record["fullname"],
            $record["user_des"],
            $record["user_scale"],
            $record["user_res"],
            $record["user_role"],
            $record["id"]
        );

        $_SESSION["u_data"] = $u_data;

        $today = date("Y-m-d");

        $check_stmt = $con->prepare(
            "SELECT COUNT(*) 
             FROM user_logins 
             WHERE user_id = ? 
             AND DATE(login_time) = ?"
        );

        $check_stmt->bind_param("is", $record["id"], $today);
        $check_stmt->execute();
        $check_stmt->bind_result($login_count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($login_count == 0) {
            $insert_stmt = $con->prepare(
                "INSERT INTO user_logins (user_id) VALUES (?)"
            );

            $insert_stmt->bind_param("i", $record["id"]);
            $insert_stmt->execute();
            $insert_stmt->close();
        }

        $role = $record["user_role"];

        if ($role == 0) {
            header("Location: dashboard/admin/admin_profile.php");
            die;
        } else {
            header("Location: dashboard/employee/emp_profile.php");
            die;
        }

    } else {
        $_SESSION["error"] = "Invalid User ID / Password";
        header("Location: index.php");
        die;
    }
}
?>